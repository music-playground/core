<?php

namespace App\Core\Domain\Entity;

readonly class ArtistShortCast
{
    public function __construct(
        public string $id,
        public string $name,
        public string $avatarId
    ) {
    }
}