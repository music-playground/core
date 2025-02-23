<?php

namespace App\Tests\Core\Integration\Cleaner;

interface CleanerInterface
{
    public function clean(array $classes): void;
}