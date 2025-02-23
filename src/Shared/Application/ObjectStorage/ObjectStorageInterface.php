<?php

namespace App\Shared\Application\ObjectStorage;

interface ObjectStorageInterface
{
    public function save(string $key, string $file);
}