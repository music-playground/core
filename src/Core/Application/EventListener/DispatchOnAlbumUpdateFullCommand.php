<?php

namespace App\Core\Application\EventListener;

use App\Core\Application\Event\DomainUpdateEvent;
use App\Core\Application\Serializer\AlbumSerializer;
use App\Core\Domain\Entity\Album;
use App\Core\Domain\Entity\SimpleArtist;
use App\Core\Domain\Repository\Artist\ArtistRepositoryInterface;
use App\Core\Domain\Repository\Artist\SearchParams;
use App\Shared\Application\Interface\CommandBusInterface;
use App\Shared\Domain\ValueObject\Pagination;
use MusicPlayground\Contract\Application\SongParser\Command\OnUpdateAlbumFullCommand;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: DomainUpdateEvent::class, method: '__invoke')]
final readonly class DispatchOnAlbumUpdateFullCommand
{
    public function __construct(
        private ArtistRepositoryInterface $artistRepository,
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

        $simpleArtists = $album->getSimpleArtists();
        $artists = $this->artistRepository->getCastAll(
            new Pagination(count($simpleArtists), 0),
            new SearchParams(array_map(fn(SimpleArtist $artist) => $artist->getSource(), $simpleArtists))
        );

        $this->bus->dispatch(new OnUpdateAlbumFullCommand(
            $this->serializer->toFullDto($album, $artists)
        ));
    }
}