<?php

namespace App\Core\Domain\ValueObject;

use Stringable;

abstract readonly class FileId implements Stringable
{
    public function __construct(private string $fileId) {
    }

    public function __toString(): string
    {
        return $this->fileId;
    }
}