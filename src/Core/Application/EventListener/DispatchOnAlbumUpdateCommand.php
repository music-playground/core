<?php

namespace App\Core\Application\EventListener;

use App\Core\Application\Event\DomainUpdateEvent;
use App\Core\Application\Serializer\AlbumSerializer;
use App\Core\Domain\Entity\Album;
use App\Shared\Application\Interface\CommandBusInterface;
use MusicPlayground\Contract\Application\SongParser\Command\OnUpdateAlbumCommand;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: DomainUpdateEvent::class, method: '__invoke')]
final readonly class DispatchOnAlbumUpdateCommand
{
    public function __construct(
        private AlbumSerializer $serializer,
        private CommandBusInterface $bus
    ) {
    }

    public function __invoke(DomainUpdateEvent $event): void
    {
        $album = $event->entity;

        if ($album instanceof Album === false) {
            return;
        }

        $this->bus->dispatch(new OnUpdateAlbumCommand(
            $album->getId(),
            $this->serializer->sourceToDTO($album->getSource()),
            //TODO: Add existing tracks in album
            []
        ));
    }
}