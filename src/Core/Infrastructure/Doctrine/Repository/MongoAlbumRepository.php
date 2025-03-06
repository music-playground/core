<?php

namespace App\Core\Infrastructure\Doctrine\Repository;

use App\Core\Domain\Entity\Album;
use App\Core\Domain\Entity\AlbumCast;
use App\Core\Domain\Entity\ArtistShortCast;
use App\Core\Domain\Enum\SourceCast;
use App\Core\Domain\Exception\AlbumNotFoundException;
use App\Core\Domain\Repository\AlbumRepositoryInterface;
use App\Core\Domain\Repository\SearchParams;
use App\Core\Domain\ValueObject\IdSource;
use App\Shared\Domain\Repository\LockMode;
use App\Shared\Domain\ValueObject\Pagination;
use DateTimeImmutable;
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

    public function __construct(private DocumentManager $dm)
    {
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
     */
    public function count(?SearchParams $params = null): int
    {
        $query = $this->repository->createQueryBuilder();

        $this->pushSearchParams($query, $params);

        return $query->count()->getQuery()->execute();
    }

    public function findBySource(IdSource $source, LockMode $lock = LockMode::NONE): ?Album
    {
        return $this->repository->findOneBy([
            'source.id' => $source->getId(),
            'source.name' => $source->getName()
        ]);
    }

    public function findIdsByAuthor(string $authorId): array
    {
        $result = $this->repository->createAggregationBuilder()
            ->match()
            ->field('artistsIds')
            ->equals($authorId)
            ->getAggregation()
            ->getIterator()
            ->toArray();

        return array_map(fn (array $album) => $album['_id'], $result);
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

    private function pushSearchParams(
        MatchStage|\Doctrine\ODM\MongoDB\Query\Builder $match, ?SearchParams $searchParams
    ): void {
        if ($searchParams?->ids !== null) {
            $match->field('_id')->in($searchParams->ids);
        }

        if ($searchParams?->artistId !== null) {
            $match->field('artistsIds')->equals($searchParams->artistId);
        }
    }

    private function pushArtistsLookup(Builder $builder): void
    {
        $builder
            ->set()
                ->field('artistsIds')
                ->map('$artistsIds', 'id', ['$toObjectId' => '$$id'])
            ->lookup('artists')
                ->localField('artistsIds')
                ->foreignField('_id')
                ->pipeline([
                    [ '$project' => [ '_id' => 1, 'name' => 1, 'avatarId' => 1 ] ]
                ])
                ->alias('artists');
    }

    private function castFromArray(array $params): AlbumCast
    {
        return new AlbumCast(
            (string)$params['_id'],
            $params['name'],
            $params['coverId'],
            $params['genres'],
            new SourceCast($params['source']['name'], $params['source']['id']),
            $params['releaseDate']->toDateTime()->format('Y-m-d'),
            array_map(fn ($artist) => new ArtistShortCast(
                (string)$artist['_id'], $artist['name'], $artist['avatarId']),
                $params['artists']
            )
        );
    }
}