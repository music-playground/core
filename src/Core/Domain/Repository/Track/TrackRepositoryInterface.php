<?php

namespace App\Core\Domain\Repository\Track;

use App\Core\Domain\Entity\Track;
use App\Core\Domain\Entity\TrackCast;
use App\Core\Domain\Exception\TrackNotFoundException;
use App\Core\Domain\ValueObject\IdSource;
use App\Shared\Domain\Repository\LockMode;
use App\Shared\Domain\ValueObject\Pagination;

interface TrackRepositoryInterface
{
    public function save(Track $track): void;

    public function findBySource(IdSource $source, LockMode $lock = LockMode::NONE): ?Track;

    /**
     * @throws TrackNotFoundException
     */
    public function getById(string $id, LockMode $lock = LockMode::NONE): Track;

    /** @return TrackCast[] */
    public function getCastAll(Pagination $pagination, ?SearchParams $params = null): array;

    /** @return string[] */
    public function getAllIdsByAlbum(string $albumId): array;

    /** @return string[] */
    public function getAllNamesByAlbumId(string $albumId): array;

    public function count(?SearchParams $searchParams = null): int;

    public function findIdWithSource(IdSource $source): ?string;

    /**
     * @throws TrackNotFoundException
     */
    public function getCastById(string $id): TrackCast;

    public function delete(string $id): void;
}