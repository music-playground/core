<?php

namespace App\Shared\Domain\ValueObject;

use InvalidArgumentException;

final readonly class Pagination
{
    public function __construct(private int $count, private int $from) {
        if ($this->count < 0) {
            throw new InvalidArgumentException('Count must be a positive integer');
        }

        if ($this->from < 0) {
            throw new InvalidArgumentException('From must be a positive integer');
        }
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getFrom(): int
    {
        return $this->from;
    }
}
