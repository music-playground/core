<?php

namespace App\Core\Application\Event;

final readonly class DomainUpdateEvent
{
    public function __construct(public object $entity) {
    }
}