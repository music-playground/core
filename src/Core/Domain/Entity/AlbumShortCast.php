<?php

namespace App\Core\Domain\Entity;

use App\Core\Domain\ValueObject\AlbumCover;

class AlbumShortCast
{
    public function __construct(
        public string $id,
        public string $name,
        public ?AlbumCover $cover
    ) {
    }
}