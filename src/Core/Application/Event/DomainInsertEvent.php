<?php

namespace App\Core\Application\Event;

final class DomainInsertEvent
{
    public function __construct(public object $entity) {
    }
}