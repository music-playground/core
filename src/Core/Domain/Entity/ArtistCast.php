<?php

namespace App\Core\Domain\Entity;

use App\Core\Domain\Enum\SourceCast;

readonly class ArtistCast
{
    public function __construct(
        public string $id,
        public string $name,
        public string $source,
        public array $genres,
        public ?string $avatar = null
    ) {
    }
}