<?php

namespace App\Core\Domain\Repository;

final readonly class SearchParams
{
    public function __construct(
        public ?string $artistId = null,
        /** @var array<string> */
        public ?array $ids = null
    ) {
    }
}