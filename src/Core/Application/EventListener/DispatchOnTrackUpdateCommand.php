<?php

namespace App\Core\Application\EventListener;

use App\Core\Application\Event\DomainUpdateEvent;
use App\Core\Domain\Entity\Track;
use App\Shared\Application\Interface\CommandBusInterface;
use MusicPlayground\Contract\Application\SongParser\Command\OnUpdateTrackCommand;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: DomainUpdateEvent::class, method: '__invoke')]
final readonly class DispatchOnTrackUpdateCommand
{
    public function __construct(
        private CommandBusInterface $bus
    ) {
    }

    public function __invoke(DomainUpdateEvent $event): void
    {
        $track = $event->entity;

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