<?php

namespace App\Shared\Infrastructure\Util;

final readonly class Query
{
    public function fromArray(array $params): string
    {
        $params = array_filter($params);

        return join('&', array_map(fn (string $k, string $v) => "$k=$v", array_keys($params), array_values($params)));
    }
}