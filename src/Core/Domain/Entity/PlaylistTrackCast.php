<?php

namespace App\Core\Domain\Entity;

use App\Core\Domain\ValueObject\Audio;

final readonly class PlaylistTrackCast
{
    public function __construct(
        public string $name,
        public Audio $file,
        /** @var string[]  */
        public array $artists
    ) {
    }
}