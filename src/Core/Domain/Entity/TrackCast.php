<?php

namespace App\Core\Domain\Entity;

use App\Core\Domain\ValueObject\Audio;

readonly class TrackCast
{
    public function __construct(
        public string $id,
        public string $name,
        public Audio $file,
        public string $source,
        /** @var array<ArtistShortCast> */
        public array $artists,
        public AlbumShortCast $album
    ) {
    }
}