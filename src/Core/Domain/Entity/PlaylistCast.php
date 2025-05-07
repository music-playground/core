<?php

namespace App\Core\Domain\Entity;

use App\Core\Domain\ValueObject\PlaylistCover;

final readonly class PlaylistCast
{
    public function __construct(
        public string $id,
        public string $name,
        public PlaylistCover $cover,
        public ?string $description,
        /** @var PlaylistTrackCast[] */
        public array $tracks
    ) {
    }
}