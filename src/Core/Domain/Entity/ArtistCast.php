<?php

namespace App\Core\Domain\Entity;

use App\Core\Domain\ValueObject\ArtistAvatar;

readonly class ArtistCast
{
    public function __construct(
        public string $id,
        public string $name,
        public string $source,
        public array $genres,
        public ?ArtistAvatar $avatar = null
    ) {
    }
}