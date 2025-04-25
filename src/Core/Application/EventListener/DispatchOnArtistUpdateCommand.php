<?php

namespace App\Core\Application\EventListener;

use App\Core\Application\Event\DomainUpdateEvent;
use App\Core\Domain\Entity\Artist;
use App\Core\Domain\Repository\Album\AlbumRepositoryInterface;
use App\Shared\Application\Interface\CommandBusInterface;
use MusicPlayground\Contract\Application\SongParser\Command\OnUpdateArtistCommand;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: DomainUpdateEvent::class, method: '__invoke')]
final readonly class DispatchOnArtistUpdateCommand
{
    public function __construct(
        private AlbumRepositoryInterface $albumRepository,
        private CommandBusInterface $bus
    ) {
    }

    public function __invoke(DomainUpdateEvent $event): void
    {
        $artist = $event->entity;

        if ($artist instanceof Artist === false) {
            return;
        }

        $containsAlbums = $this->albumRepository->findIdsByAuthor($artist->getId());
        $this->bus->dispatch(new OnUpdateArtistCommand($artist->getSource(), $containsAlbums));
    }
}