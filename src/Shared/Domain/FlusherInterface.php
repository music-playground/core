<?php

namespace App\Shared\Domain;

interface FlusherInterface
{
    public function flush(): void;
}