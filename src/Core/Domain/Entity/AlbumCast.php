<?php

namespace App\Core\Domain\Entity;

use App\Core\Domain\Enum\SourceCast;
use DateTimeImmutable;

readonly class AlbumCast
{
    public function __construct(
        public string $id,
        public string $name,
        public string $coverId,
        public SourceCast $source,
        public array $genres,
        public DateTimeImmutable $releaseDate,
        /** @var ArtistShortCast[] */
        public array $artists
    ) {
    }
}