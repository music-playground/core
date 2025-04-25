<?php

namespace App\Core\Application\EventListener;

use App\Core\Application\Event\OnUpdateAlbumEvent;
use App\Core\Application\Serializer\AlbumSerializer;
use App\Shared\Application\Interface\CommandBusInterface;
use MusicPlayground\Contract\Application\SongParser\Command\OnUpdateAlbumCommand;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: OnUpdateAlbumEvent::class, method: '__invoke')]
final readonly class DispatchOnAlbumUpdateCommand
{
    public function __construct(
        private AlbumSerializer $serializer,
        private CommandBusInterface $bus
    ) {
    }

    public function __invoke(OnUpdateAlbumEvent $event): void
    {
        $this->bus->dispatch(new OnUpdateAlbumCommand(
            $event->album->getId(),
            $this->serializer->sourceToDTO($event->album->getSource()),
            //TODO: Add existing tracks in album
            []
        ));
    }
}