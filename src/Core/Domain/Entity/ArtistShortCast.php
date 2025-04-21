<?php

namespace App\Core\Domain\Entity;

readonly class ArtistShortCast
{
    public function __construct(
        public string $name,
        public ?string $id,
        public ?string $avatarId
    ) {
    }
}