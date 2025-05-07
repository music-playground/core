<?php

namespace App\Core\Domain\Repository\Playlist;

final readonly class SearchParams
{
    public function __construct(
        /** @var string[]|null */
        public ?array $ids = null
    ) {
    }
}