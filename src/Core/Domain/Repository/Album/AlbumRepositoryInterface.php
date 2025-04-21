<?php

namespace App\Core\Domain\Repository\Album;

use App\Core\Domain\Entity\Album;
use App\Core\Domain\Entity\AlbumCast;
use App\Core\Domain\Exception\AlbumNotFoundException;
use App\Core\Domain\ValueObject\IdSource;
use App\Shared\Domain\Repository\LockMode;
use App\Shared\Domain\ValueObject\Pagination;

interface AlbumRepositoryInterface
{
    public function save(Album $album): void;

    public function getById(string $id, LockMode $lock = LockMode::NONE): Album;

    /** @throws AlbumNotFoundException */
    public function getCastById(string $id): AlbumCast;

    /** @return AlbumCast[] */
    public function getCastAll(Pagination $pagination, ?SearchParams $params = null): array;

    public function count(?SearchParams $params = null): int;

    public function findBySource(IdSource $source, LockMode $lock = LockMode::NONE): ?Album;

    /** @return array<string> */
    public function findIdsByAuthor(string $authorId): array;

    public function delete(string $id): void;
}