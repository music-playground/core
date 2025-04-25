<?php

namespace App\Core\Application\EventListener;

use App\Core\Application\Event\DomainInsertEvent;
use App\Core\Application\Event\DomainUpdateEvent;
use App\Core\Application\Serializer\ArtistSerializer;
use App\Core\Domain\Entity\Artist;
use App\Core\Domain\Repository\Album\AlbumRepositoryInterface;
use App\Shared\Application\Interface\CommandBusInterface;
use MusicPlayground\Contract\Application\SongParser\Command\OnUpdateArtistCommand;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: DomainInsertEvent::class, method: 'onInsert')]
#[AsEventListener(event: DomainUpdateEvent::class, method: 'onUpdate')]
final readonly class DispatchOnArtistUpdateCommand
{
    public function __construct(
        private AlbumRepositoryInterface $albumRepository,
        private ArtistSerializer $serializer,
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

    private function dispatch(object $artist): void
    {
        if ($artist instanceof Artist === false) {
            return;
        }

        $containsAlbums = $this->albumRepository->findIdsByAuthor($artist->getId());

        $this->bus->dispatch(new OnUpdateArtistCommand(
            $this->serializer->sourceToDTO($artist->getSource()),
            $containsAlbums
        ));
    }
}