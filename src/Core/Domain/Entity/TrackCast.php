<?php

namespace App\Core\Domain\Entity;

readonly class TrackCast
{
    public function __construct(
        public string $id,
        public string $name,
        public string $fileId,
        public string $source,
        /** @var array<ArtistShortCast> */
        public array $artists,
        public AlbumShortCast $album
    ) {
    }
}