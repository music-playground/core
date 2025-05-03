<?php

namespace App\Core\Application\EventListener;

use App\Core\Application\Event\DomainInsertEvent;
use App\Core\Application\Event\DomainUpdateEvent;
use App\Core\Application\Serializer\AlbumSerializer;
use App\Core\Domain\Entity\Album;
use App\Core\Domain\Repository\Artist\ArtistRepositoryInterface;
use App\Shared\Application\Interface\CommandBusInterface;
use MusicPlayground\Contract\Application\SongParser\Command\OnUpdateAlbumFullCommand;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: DomainInsertEvent::class, method: 'onInsert')]
#[AsEventListener(event: DomainUpdateEvent::class, method: 'onUpdate')]
final readonly class DispatchOnAlbumUpdateFullCommand
{
    public function __construct(
        private ArtistRepositoryInterface $artistRepository,
        private AlbumSerializer $serializer,
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

    private function dispatch(object $album): void
    {
        if ($album instanceof Album === false) {
            return;
        }

        $allArtists = $album->getSimpleArtists();
        $artists = $this->artistRepository->concat($allArtists);

        $this->bus->dispatch(new OnUpdateAlbumFullCommand(
            $this->serializer->toFullDto($album, $artists)
        ));
    }
}