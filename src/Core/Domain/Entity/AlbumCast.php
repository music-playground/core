<?php

namespace App\Core\Domain\Entity;

use App\Core\Domain\Enum\SourceCast;
use App\Core\Domain\ValueObject\AlbumCover;
use DateTimeImmutable;

readonly class AlbumCast
{
    public function __construct(
        public string $id,
        public string $name,
        public AlbumCover $cover,
        public array $genres,
        public string $source,
        public string $releaseDate,
        /** @var ArtistShortCast[] */
        public array $artists
    ) {
    }
}