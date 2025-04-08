<?php

namespace App\Core\Domain\Entity;

use App\Core\Domain\ValueObject\IdSource;

class SimpleArtist
{
    public function __construct(
        private string $name,
        private readonly IdSource $source
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSource(): IdSource
    {
        return $this->source;
    }
}