<?php

namespace App\Core\Infrastructure\Doctrine\Type;

use App\Core\Domain\Enum\Source;
use Doctrine\ODM\MongoDB\Types\StringType;
use InvalidArgumentException;

class SourceType extends StringType
{
    public function convertToDatabaseValue($value): string
    {
        if ($value instanceof  Source === false) {
            throw new InvalidArgumentException('Invalid value type, it`s not a source');
        }

        return (string)$value->value;
    }

    public function convertToPHPValue($value): Source
    {
        return Source::from($value);
    }
}
