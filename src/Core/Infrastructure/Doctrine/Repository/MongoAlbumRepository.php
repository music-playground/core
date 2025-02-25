<?php

namespace App\Core\Infrastructure\Doctrine\Repository;

use App\Core\Domain\Repository\AlbumRepositoryInterface;

class MongoAlbumRepository implements AlbumRepositoryInterface
{
    public function findIdsByAuthor(string $authorId): array
    {
        return [];
    }
}