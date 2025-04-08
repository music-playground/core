<?php

namespace App\Core\Infrastructure\Doctrine\Type;

use App\Core\Domain\ValueObject\IdSource;
use Doctrine\ODM\MongoDB\Types\Type;
use InvalidArgumentException;

class IdSourceType extends Type
{
    public function convertToDatabaseValue($value): string
    {
        if ($value instanceof IdSource === false) {
            throw new InvalidArgumentException('Invalid value type, it`s not a id source');
        }

        return (string)$value;
    }

    public function convertToPHPValue($value): IdSource
    {
        return IdSource::from($value);
    }

    public function closureToPHP(): string
    {
        return '$return = \App\Core\Domain\ValueObject\IdSource::from($value);';
    }

    public function closureToMongo(): string
    {
        return '$return = \App\Core\Domain\ValueObject\IdSource::from($value);';
    }
}