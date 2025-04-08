<?php

namespace App\Core\Domain\Entity;

class AlbumShortCast
{
    public function __construct(
        public string $id,
        public string $coverId,
        public string $name
    ) {
    }
}