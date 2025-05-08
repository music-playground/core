<?php

namespace App\Core\Domain\Repository\Playlist;

use App\Core\Domain\Entity\Playlist;
use App\Core\Domain\Entity\PlaylistCast;
use App\Core\Domain\ValueObject\IdSource;
use App\Shared\Domain\Repository\LockMode;
use App\Shared\Domain\ValueObject\Pagination;

interface PlaylistRepositoryInterface
{
    public function save(Playlist $playlist): void;

    public function saveTrack(string $trackId, string $id): void;

    public function findById(string $id, LockMode $lock = LockMode::NONE): ?Playlist;

    public function findBySource(IdSource $source): ?Playlist;

    /** @return PlaylistCast[] */
    public function getCastAll(Pagination $pagination, ?SearchParams $params = null): array;

    public function count(?SearchParams $params = null): int;
}