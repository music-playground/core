<?php

namespace App\Core\Domain\Repository\Artist;

use App\Core\Domain\ValueObject\IdSource;

final readonly class SearchParams
{
    public function __construct(
        /** @var IdSource[] */
        public ?array $sources = []
    ) {
    }
}