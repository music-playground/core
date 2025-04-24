<?php

namespace App\Core\Infrastructure\Doctrine\Repository;

use App\Core\Domain\Entity\AlbumShortCast;
use App\Core\Domain\Entity\ArtistShortCast;
use App\Core\Domain\Entity\Track;
use App\Core\Domain\Entity\TrackCast;
use App\Core\Domain\Exception\TrackNotFoundException;
use App\Core\Domain\Repository\Track\SearchParams;
use App\Core\Domain\Repository\Track\TrackRepositoryInterface;
use App\Core\Domain\ValueObject\AlbumCover;
use App\Core\Domain\ValueObject\ArtistAvatar;
use App\Core\Domain\ValueObject\Audio;
use App\Core\Domain\ValueObject\IdSource;
use App\Shared\Domain\Repository\LockMode;
use App\Shared\Domain\ValueObject\Pagination;
use Doctrine\ODM\MongoDB\Aggregation\Builder;
use Doctrine\ODM\MongoDB\Aggregation\Stage\MatchStage;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\LockException;
use Doctrine\ODM\MongoDB\Mapping\MappingException;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

final readonly class MongoTrackRepository implements TrackRepositoryInterface
{
    private DocumentRepository $repository;

    public function __construct(private DocumentManager $dm)
    {
        $this->repository = $this->dm->getRepository(Track::class);
    }

    public function save(Track $track): void
    {
        $this->dm->persist($track);
    }

    public function findBySource(IdSource $source, LockMode $lock = LockMode::NONE): ?Track
    {
        return $this->repository->findOneBy(['source' => $source]);
    }

    /**
     * @throws MappingException
     * @throws LockException
     * @throws TrackNotFoundException
     */
    public function getById(string $id, LockMode $lock = LockMode::NONE): Track
    {
        $track = $this->repository->find($id);

        if ($track === null) {
            throw new TrackNotFoundException();
        }

        return $track;
    }

    public function getCastById(string $id): TrackCast
    {
        $aggregation = $this->repository->createAggregationBuilder();

        $aggregation->match()
            ->field('_id')
            ->equals($id);

        $this->pushAlbumLookup($aggregation);
        $this->pushArtistLookup($aggregation);

        $albums = $aggregation->getAggregation()
            ->execute()
            ->toArray();

        if (count($albums) === 0) {
            throw new TrackNotFoundException();
        }

        return $this->createCastByArray($albums[0]);
    }

    /** @return TrackCast[] */
    public function getCastAll(Pagination $pagination, ?SearchParams $params = null): array
    {
        if ($pagination->getCount() === 0) return [];

        $aggregation = $this->repository->createAggregationBuilder();
        $isValid = $this->pushSearchParams($aggregation->match(), $params);

        if ($isValid === false) {
            return [];
        }

        $aggregation->sort('_id', -1)
            ->skip($pagination->getFrom())
            ->limit($pagination->getCount());

        $this->pushAlbumLookup($aggregation);
        $this->pushArtistLookup($aggregation);

        $tracks = $aggregation->getAggregation()->execute()->toArray();

        return array_map(fn (array $track) => $this->createCastByArray($track), $tracks);
    }

    /**
     * @throws MongoDBException
     */
    public function getAllNamesByAlbumId(string $albumId): array
    {
        $builder = $this->repository->createQueryBuilder();

        return $builder->find(Track::class)
            ->field('albumId')
                ->equals($albumId)
            ->select('name')
            ->getQuery()
            ->execute()
            ->toArray();
    }

    /**
     * @throws MongoDBException
     */
    public function count(?SearchParams $searchParams = null): int
    {
        $query = $this->repository->createQueryBuilder();
        $isValid = $this->pushSearchParams($query, $searchParams);

        if ($isValid === false) {
            return 0;
        }

        return $query->count()->getQuery()->execute();
    }

    /**
     * @throws MappingException
     * @throws TrackNotFoundException
     * @throws LockException
     */
    public function delete(string $id): void
    {
        $track = $this->getById($id, LockMode::PESSIMISTIC_EXCLUSIVE);

        $this->dm->remove($track);
    }

    private function pushSearchParams(
        MatchStage|\Doctrine\ODM\MongoDB\Query\Builder $match, ?SearchParams $searchParams
    ): bool {
        if ($searchParams->albumId !== null) {
            if (preg_match(MONGO_OBJECT_ID_REGEX, $searchParams->albumId) !== 1) {
                return false;
            }

            $match->field('albumId')->equals($searchParams->albumId);
        }

        return true;
    }

    private function createCastByArray(array $params): TrackCast
    {
        return new TrackCast(
            $params['_id'],
            $params['name'],
            new Audio($params['fileId']),
            $params['source'],
            array_map(
                fn (array $artist) => new ArtistShortCast(
                    $artist['_id'],
                    $artist['name'],
                    $artist['avatarId'] ? new ArtistAvatar($artist['avatarId']) : null
                ),
                $params['artists']
            ),
            $this->createAlbumShortCastByArray($params['album'][0])
        );
    }

    private function pushArtistLookup(Builder $builder): void
    {
        $builder->lookup('artists')
            ->localField('artists.source')
            ->foreignField('source')
            ->alias('artists');
    }

    private function pushAlbumLookup(Builder $builder): void
    {
        $builder->lookup('albums')
        ->localField('albumId')
        ->foreignField('_id')
        ->alias('album');
    }

    private function createAlbumShortCastByArray(array $params): AlbumShortCast
    {
        return new AlbumShortCast($params['_id'], $params['name'], new AlbumCover($params['coverId']));
    }
}