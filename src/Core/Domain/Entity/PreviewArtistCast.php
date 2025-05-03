<?php

namespace App\Core\Domain\Entity;

final readonly class PreviewArtistCast
{
    public function __construct(
        public ?string $id,
        public string $name,
        public string $source,
        public ?string $avatarId
    ) {
    }
}