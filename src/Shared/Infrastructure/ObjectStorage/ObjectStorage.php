<?php

namespace App\Shared\Infrastructure\ObjectStorage;

use App\Shared\Application\ObjectStorage\ObjectStorageInterface;

class ObjectStorage implements ObjectStorageInterface
{

    public function save(string $key, string $file)
    {
    }

    public function delete(string $key): bool
    {
        return true;
    }
}