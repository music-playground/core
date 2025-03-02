<?php

namespace App\Core\Domain\ValueObject;

use App\Core\Domain\Enum\Source;

final readonly class IdSource
{
    public function __construct(
        private string $id,
        private Source $name
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name->value;
    }
}