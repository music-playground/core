<?php

namespace App\Core\Infrastructure\Doctrine\EventListener;

use App\Core\Application\Event\DomainUpdateEvent;
use Doctrine\Bundle\MongoDBBundle\Attribute\AsDocumentListener;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Psr\EventDispatcher\EventDispatcherInterface;

#[AsDocumentListener('postUpdate')]
final readonly class PostUpdateEventListener
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->eventDispatcher->dispatch(new DomainUpdateEvent($args->getObject()));
    }
}