<?php

namespace App\Shared\Application\Interface;

interface CommandBusInterface
{
    public function dispatch(object $command): void;
}