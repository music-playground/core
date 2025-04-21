<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function boot(): void
    {
        define('MONGO_OBJECT_ID_REGEX', '/^[a-fA-F0-9]{24}$/');

        parent::boot();
    }
}
