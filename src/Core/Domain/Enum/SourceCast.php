<?php

namespace App\Core\Domain\Enum;

readonly class SourceCast
{
    public function __construct(
        public string $name,
        public string $id
    ) {
    }
}