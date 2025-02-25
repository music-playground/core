<?php

namespace App\Shared\Application\ObjectStorage;

interface ObjectStorageInterface
{
    public function save(string $key, string $file);

    /** @return bool TRUE - when object existed, FALSE - if object not found */
    public function delete(string $key): bool;
}