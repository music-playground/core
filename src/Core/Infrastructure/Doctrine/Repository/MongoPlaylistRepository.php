<?php

namespace App\Core\Infrastructure\Doctrine\Repository;

use App\Core\Domain\Entity\Playlist;
use App\Core\Domain\Repository\Playlist\PlaylistRepositoryInterface;
use App\Core\Domain\ValueObject\IdSource;
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
}