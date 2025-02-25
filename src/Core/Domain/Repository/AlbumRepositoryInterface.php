<?php

namespace App\Core\Domain\Repository;

interface AlbumRepositoryInterface
{
    /** @return array<string> */
    public function findIdsByAuthor(string $authorId): array;
}