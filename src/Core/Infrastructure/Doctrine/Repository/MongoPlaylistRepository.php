<?php

namespace App\Core\Infrastructure\Doctrine\Repository;

use App\Core\Domain\Entity\Playlist;
use App\Core\Domain\Repository\Playlist\PlaylistRepositoryInterface;
use App\Core\Domain\ValueObject\IdSource;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

final readonly class MongoPlaylistRepository implements PlaylistRepositoryInterface
{
    private DocumentRepository $repository;

    public function __construct(private DocumentManager $dm)
    {
        $this->repository = $dm->getRepository(Playlist::class);
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
}