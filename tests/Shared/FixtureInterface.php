<?php

namespace App\Tests\Shared;

interface FixtureInterface
{
    public function load(): mixed;
}