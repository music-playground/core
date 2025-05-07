<?php

namespace App\Core\Infrastructure\Doctrine\Repository;

use App\Core\Domain\Entity\Playlist;
use App\Core\Domain\Entity\PlaylistCast;
use App\Core\Domain\Entity\PlaylistTrackCast;
use App\Core\Domain\Repository\Playlist\PlaylistRepositoryInterface;
use App\Core\Domain\Repository\Playlist\SearchParams;
use App\Core\Domain\ValueObject\Audio;
use App\Core\Domain\ValueObject\IdSource;
use App\Core\Domain\ValueObject\PlaylistCover;
use App\Shared\Domain\ValueObject\Pagination;
use Doctrine\ODM\MongoDB\Aggregation\Builder;
use Doctrine\ODM\MongoDB\Aggregation\Stage\MatchStage;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\LockException;
use Doctrine\ODM\MongoDB\Mapping\MappingException;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

final readonly class MongoPlaylistRepository implements PlaylistRepositoryInterface
{
    private DocumentRepository $repository;

    public function __construct(private DocumentManager $dm)
    {
        $this->repository = $dm->getRepository(Playlist::class);
    }

    /**
     * @throws MappingException
     * @throws LockException
     */
    public function findById(string $id): ?Playlist
    {
        return $this->repository->find($id);
    }

    public function save(Playlist $playlist): void
    {
        $this->dm->persist($playlist);
    }

    /**
     * @throws MongoDBException
     */
    public function findCreationOperationId(IdSource $source): ?string
    {
        $response = $this->repository->createQueryBuilder()
                ->field('source')
                    ->equals($source)
                ->select('creationOperationId')
                ->hydrate(false)
                ->getQuery()
                ->execute()
                ->toArray();

        return $response[0]['creationOperationId'] ?? null;
    }

    /**
     * @throws MongoDBException
     */
    public function saveTrack(string $trackId, string $id): void
    {
        $this->repository->createQueryBuilder()
            ->updateOne()
            ->field('_id')
                ->equals($id)
            ->field('tracks')
                ->addToSet($trackId)
            ->getQuery()
            ->execute();
    }

    public function getCastAll(Pagination $pagination, ?SearchParams $params = null): array
    {
        if ($pagination->getCount() === 0) {
            return [];
        }

        $aggregation = $this->repository->createAggregationBuilder();

        $this->pushSearchParams($aggregation->match(), $params);

        $aggregation
            ->limit($pagination->getCount())
            ->skip($pagination->getFrom());
        $this->pushTracksLookup($aggregation);

        $result = $aggregation->getAggregation()
            ->execute()
            ->toArray();

        return array_map(fn (array $playlist) => $this->castFromArray($playlist), $result);
    }

    /**
     * @throws MongoDBException
     */
    public function count(?SearchParams $params = null): int
    {
        $query = $this->repository->createQueryBuilder();

        $this->pushSearchParams($query, $params);

        return $query->count()
            ->getQuery()
            ->execute();
    }

    private function pushSearchParams(
        MatchStage|\Doctrine\ODM\MongoDB\Query\Builder $match, ?SearchParams $params
    ): void {
        if ($params?->ids !== null) {
            $match->field('_id')->in($params->ids);
        }
    }

    private function pushTracksLookup(Builder $builder): void
    {
        $builder
            ->addFields()
                ->field('tracks')
                ->map('$tracks', 'tracks', ['$toObjectId' => '$$tracks'])
                ->lookup('tracks')
                ->localField('tracks')
                ->foreignField('_id')
                ->alias('tracks');
    }

    private function castFromArray(array $params): PlaylistCast
    {
        return new PlaylistCast(
            (string)$params['_id'],
            $params['name'],
            isset($params['coverId']) ? new PlaylistCover($params['coverId']) : null,
            $params['description'] ?? null,
            array_map(fn (array $track) => new PlaylistTrackCast(
                $track['name'],
                new Audio($track['fileId']),
                array_map(fn (array $artist) => $artist['name'], $track['artists'])
            ), $params['tracks'])
        );
    }
}