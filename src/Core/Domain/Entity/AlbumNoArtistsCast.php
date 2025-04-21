<?php

namespace App\Core\Domain\Entity;

readonly class AlbumNoArtistsCast
{
    public function __construct(
        public string $id,
        public string $name,
        public string $cover,
        public array $genres,
        public string $source,
        public string $releaseDate
    ) {
    }
}