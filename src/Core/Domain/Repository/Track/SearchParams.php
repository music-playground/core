<?php

namespace App\Core\Domain\Repository\Track;

final readonly class SearchParams
{
    public function __construct(
        public ?string $albumId = null
    ) {
    }
}