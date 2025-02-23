<?php

namespace App\Tests\Shared;

use Swaggest\JsonSchema\Exception;
use Swaggest\JsonSchema\InvalidValue;
use Swaggest\JsonSchema\Schema;

trait SchemaAssertTrait
{
    /**
     * @throws Exception
     * @throws InvalidValue
     */
    public function assertSchema(mixed $data, array $schema): void
    {
        $schema = Schema::import($schema);

        $schema->in($data);
    }
}