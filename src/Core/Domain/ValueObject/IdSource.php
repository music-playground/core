<?php

namespace App\Core\Domain\ValueObject;

use App\Core\Domain\Enum\Source;
use Stringable;

final readonly class IdSource implements Stringable
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

    public function __toString(): string
    {
        return $this->getName() . ':' . $this->getId();
    }

    public static function from(string $value): self
    {
        [$name, $id] = explode(':', $value, 2);

        return new self($id, Source::from($name));
    }
}