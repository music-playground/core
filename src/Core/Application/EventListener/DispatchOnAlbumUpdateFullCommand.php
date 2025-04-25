<?php

namespace App\Core\Application\EventListener;

use App\Core\Application\Event\OnUpdateAlbumEvent;
use App\Core\Application\Serializer\AlbumSerializer;
use App\Core\Domain\Entity\SimpleArtist;
use App\Core\Domain\Repository\Artist\ArtistRepositoryInterface;
use App\Core\Domain\Repository\Artist\SearchParams;
use App\Core\Domain\Repository\Track\TrackRepositoryInterface;
use App\Shared\Application\Interface\CommandBusInterface;
use App\Shared\Domain\ValueObject\Pagination;
use MusicPlayground\Contract\Application\SongParser\Command\OnUpdateAlbumFullCommand;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: OnUpdateAlbumEvent::class, method: '__invoke')]
final readonly class DispatchOnAlbumUpdateFullCommand
{
    public function __construct(
        private ArtistRepositoryInterface $artistRepository,
        private TrackRepositoryInterface $trackRepository,
        private AlbumSerializer $serializer,
        private CommandBusInterface $bus
    ) {
    }

    public function __invoke(OnUpdateAlbumEvent $event): void
    {
        try {
            $album = $event->album;
            $simpleArtists = $album->getSimpleArtists();
            $artists = $this->artistRepository->getCastAll(
                new Pagination(count($simpleArtists), 0),
                new SearchParams(array_map(fn(SimpleArtist $artist) => $artist->getSource(), $simpleArtists))
            );
            $tracks = $this->trackRepository->getAllNamesByAlbumId($album->getId());

            $this->bus->dispatch(new OnUpdateAlbumFullCommand(
                $this->serializer->toFullDto($album, $tracks, $artists)
            ));
        } catch (\Throwable $e) {
            var_dump($e);
        }
    }
}