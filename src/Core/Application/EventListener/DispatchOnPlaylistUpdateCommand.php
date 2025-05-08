<?php

namespace App\Core\Application\EventListener;

use App\Core\Application\Event\DomainInsertEvent;
use App\Core\Application\Event\DomainUpdateEvent;
use App\Core\Domain\Entity\Playlist;
use App\Shared\Application\Interface\CommandBusInterface;
use MusicPlayground\Contract\Application\Playlist\Command\UpdateFullPlaylistCommand;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: DomainInsertEvent::class, method: 'onInsert')]
#[AsEventListener(event: DomainUpdateEvent::class, method: 'onUpdate')]
final readonly class DispatchOnPlaylistUpdateCommand
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

    private function dispatch(object $playlist): void
    {
        if ($playlist instanceof Playlist === false) {
            return;
        }

        $this->bus->dispatch(new UpdateFullPlaylistCommand(
            $playlist->getId(),
            $playlist->getName(),
            $playlist->getCoverId(),
            $playlist->getDescription()
        ));
    }
}