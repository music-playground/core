<?php

namespace App\Core\Infrastructure\Doctrine\Type;

use App\Core\Domain\Enum\Source;
use Doctrine\ODM\MongoDB\Types\Type;
use InvalidArgumentException;

class SourceType extends Type
{
    public function convertToDatabaseValue($value): string
    {
        if ($value instanceof Source === false) {
            throw new InvalidArgumentException('Invalid value type, it`s not a source');
        }

        return (string)$value->value;
    }

    public function convertToPHPValue($value): Source
    {
        return Source::from($value);
    }

    public function closureToPHP(): string
    {
        return '$return = \App\Core\Domain\Enum\Source::from($value);';
    }

    public function closureToMongo(): string
    {
        return '$return = \App\Core\Domain\Enum\Source::from($value);';
    }
}
