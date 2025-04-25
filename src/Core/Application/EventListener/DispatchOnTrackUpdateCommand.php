<?php

namespace App\Core\Application\EventListener;

use App\Core\Application\Event\DomainInsertEvent;
use App\Core\Application\Event\DomainUpdateEvent;
use App\Core\Domain\Entity\Track;
use App\Shared\Application\Interface\CommandBusInterface;
use MusicPlayground\Contract\Application\SongParser\Command\OnUpdateTrackCommand;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: DomainInsertEvent::class, method: 'onInsert')]
#[AsEventListener(event: DomainUpdateEvent::class, method: 'onUpdate')]
final readonly class DispatchOnTrackUpdateCommand
{
    public function __construct(
        private CommandBusInterface $bus
    ) {
    }

    public function onInsert(DomainInsertEvent $event): void
    {
        $this->dispatch($event->entity);
    }

    public function onUpdate(DomainUpdateEvent $event): void
    {
        $this->dispatch($event->entity);
    }

    private function dispatch(object $track): void
    {
        if ($track instanceof Track === false) {
            return;
        }

        $this->bus->dispatch(new OnUpdateTrackCommand(
            $track->getId(),
            $track->getName(),
            $track->getAlbumId()
        ));
    }
}