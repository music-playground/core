<?php

namespace App\Core\Infrastructure\Doctrine\EventListener;

use App\Core\Application\Event\DomainInsertEvent;
use App\Core\Application\Event\DomainUpdateEvent;
use Doctrine\Bundle\MongoDBBundle\Attribute\AsDocumentListener;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Psr\EventDispatcher\EventDispatcherInterface;

#[AsDocumentListener('postUpdate')]
#[AsDocumentListener('postPersist')]
final readonly class DomainEventListener
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->eventDispatcher->dispatch(new DomainUpdateEvent($args->getObject()));
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->eventDispatcher->dispatch(new DomainInsertEvent($args->getObject()));
    }
}