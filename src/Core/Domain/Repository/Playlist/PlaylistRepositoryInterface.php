<?php

namespace App\Core\Domain\Repository\Playlist;

use App\Core\Domain\Entity\Playlist;
use App\Core\Domain\ValueObject\IdSource;

interface PlaylistRepositoryInterface
{
    public function save(Playlist $playlist): void;

    public function findCreationOperationId(IdSource $source): ?string;
}