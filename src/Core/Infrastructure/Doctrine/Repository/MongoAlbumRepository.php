<?php

namespace App\Core\Infrastructure\Doctrine\Repository;

use App\Core\Domain\Entity\Album;
use App\Core\Domain\Entity\AlbumCast;
use App\Core\Domain\Exception\AlbumNotFoundException;
use App\Core\Domain\Exception\ArtistNotFoundException;
use App\Core\Domain\Repository\Album\AlbumRepositoryInterface;
use App\Core\Domain\Repository\Album\SearchParams;
use App\Core\Domain\Repository\ArtistRepositoryInterface;
use App\Core\Domain\ValueObject\AlbumCover;
use App\Core\Domain\ValueObject\IdSource;
use App\Core\Infrastructure\Util\ShortArtistsFactory;
use App\Shared\Domain\Repository\LockMode;
use App\Shared\Domain\ValueObject\Pagination;
use Doctrine\ODM\MongoDB\Aggregation\Builder;
use Doctrine\ODM\MongoDB\Aggregation\Stage\MatchStage;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\LockException;
use Doctrine\ODM\MongoDB\Mapping\MappingException;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

final readonly class MongoAlbumRepository implements AlbumRepositoryInterface
{
    private DocumentRepository $repository;

    public function __construct(
        private DocumentManager $dm,
        private ArtistRepositoryInterface $artistRepository,
        private ShortArtistsFactory $shortArtistsFactory
    ) {
        $this->repository = $this->dm->getRepository(Album::class);
    }

    public function save(Album $album): void
    {
        $this->dm->persist($album);
    }

    /**
     * @throws MappingException
     * @throws LockException
     * @throws AlbumNotFoundException
     */
    public function getById(string $id, LockMode $lock = LockMode::NONE): Album
    {
        $album = $this->repository->find($id);

        if ($album === null) {
            throw new AlbumNotFoundException();
        }

        return $album;
    }

    public function getCastById(string $id): AlbumCast
    {
        $aggregation = $this->repository->createAggregationBuilder();

        $aggregation->match()
            ->field('_id')
            ->equals($id);
        $this->pushArtistsLookup($aggregation);

        $album = $aggregation->getAggregation()->getSingleResult();

        if ($album === null) {
            throw new AlbumNotFoundException();
        }

        return $this->castFromArray($album);
    }

    /**
     * @throws ArtistNotFoundException
     */
    public function getCastAll(Pagination $pagination, ?SearchParams $params = null): array
    {
        if ($pagination->getCount() === 0) {
            return [];
        }

        $aggregation = $this->repository->createAggregationBuilder();

        $this->pushSearchParams($aggregation->match(), $params);

        $aggregation->sort('_id', -1)
        ->skip($pagination->getFrom())
        ->limit($pagination->getCount());

        $this->pushArtistsLookup($aggregation);

        $results = $aggregation->getAggregation()->execute()->toArray();

        return array_map(fn (array $album) => $this->castFromArray($album), $results);
    }

    /**
     * @throws MongoDBException
     * @throws ArtistNotFoundException
     */
    public function count(?SearchParams $params = null): int
    {
        $query = $this->repository->createQueryBuilder();

        $this->pushSearchParams($query, $params);

        return $query->count()->getQuery()->execute();
    }

    public function findBySource(IdSource $source, LockMode $lock = LockMode::NONE): ?Album
    {
        return $this->repository->findOneBy(['source' => $source]);
    }

    /**
     * @throws ArtistNotFoundException
     */
    public function findIdsByAuthor(string $authorId): array
    {
        $artist = $this->artistRepository->getById($authorId);
        $result = $this->repository->createAggregationBuilder()
            ->match()
            ->field('artists.source')
            ->equals((string)$artist->getSource())
            ->getAggregation()
            ->getIterator()
            ->toArray();

        return array_map(fn (array $album) => (string)$album['_id'], $result);
    }

    /**
     * @throws AlbumNotFoundException
     * @throws MappingException
     * @throws LockException
     */
    public function delete(string $id): void
    {
        $album = $this->getById($id, LockMode::PESSIMISTIC_EXCLUSIVE);

        $this->dm->remove($album);
    }

    /**
     * @throws ArtistNotFoundException
     */
    private function pushSearchParams(
        MatchStage|\Doctrine\ODM\MongoDB\Query\Builder $match, ?SearchParams $searchParams
    ): void {
        if ($searchParams?->ids !== null) {
            $match->field('_id')->in($searchParams->ids);
        }

        if ($searchParams?->artistId !== null) {
            $artist = $this->artistRepository->getById($searchParams->artistId);

            $match->field('artists.source')->equals((string)$artist->getSource());
        }
    }

    private function pushArtistsLookup(Builder $builder): void
    {
        $builder
            ->lookup('artists')
                ->localField('artists.source')
                ->foreignField('source')
                ->alias('existingArtists');
    }

    private function castFromArray(array $params): AlbumCast
    {
        return new AlbumCast(
            (string)$params['_id'],
            $params['name'],
            new AlbumCover($params['coverId']),
            $params['genres'],
            $params['source'],
            $params['releaseDate']->toDateTime()->format('Y-m-d'),
            $this->shortArtistsFactory->create($params['artists'], $params['existingArtists'])
        );
    }
}