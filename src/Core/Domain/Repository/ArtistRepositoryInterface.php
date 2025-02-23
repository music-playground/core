<?php

namespace App\Core\Domain\Repository;

use App\Core\Domain\Entity\Artist;
use App\Core\Domain\Entity\ArtistCast;
use App\Core\Domain\Exception\ArtistNotFoundException;
use App\Shared\Domain\Repository\LockMode;
use App\Shared\Domain\ValueObject\Pagination;

interface ArtistRepositoryInterface
{
    public function save(Artist $artist): void;

    /** @throws ArtistNotFoundException */
    public function getById(string $id, LockMode $lock = LockMode::NONE): Artist;

    public function getCastById(string $id): ArtistCast;

    /** @return ArtistCast[] */
    public function getCastAll(Pagination $pagination): array;

    public function count(): int;

    /** @throws ArtistNotFoundException */
    public function delete(string $id): void;
}