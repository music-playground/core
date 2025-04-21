<?php

namespace App\Core\Domain\Entity;

use App\Core\Domain\ValueObject\ArtistAvatar;

readonly class ArtistShortCast
{
    public function __construct(
        public string $name,
        public ?string $id,
        public ?ArtistAvatar $avatar
    ) {
    }
}