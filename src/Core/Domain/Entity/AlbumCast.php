<?php

namespace App\Core\Domain\Entity;

use App\Core\Domain\Enum\SourceCast;
use DateTimeImmutable;

readonly class AlbumCast
{
    public function __construct(
        public string $id,
        public string $name,
        public string $cover,
        public array $genres,
        public SourceCast $source,
        public string $releaseDate,
        /** @var ArtistShortCast[] */
        public array $artists
    ) {
    }
}