<?php

namespace App\Core\Application\Handler;

use App\Core\Application\Serializer\AlbumSerializer;
use App\Core\Application\Serializer\ArtistSerializer;
use App\Core\Application\Updater\AlbumUpdater;
use App\Core\Domain\Entity\Album;
use App\Core\Domain\Repository\Album\AlbumRepositoryInterface;
use App\Core\Domain\Repository\Track\TrackRepositoryInterface;
use App\Shared\Application\Interface\CommandBusInterface;
use App\Shared\Domain\FlusherInterface;
use MusicPlayground\Contract\Application\SongParser\Command\OnUpdateAlbumCommand;
use MusicPlayground\Contract\Application\SongParser\Command\UpdateAlbumCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateAlbumCommandHandler
{
    public function __construct(
        private TrackRepositoryInterface $trackRepository,
        private AlbumUpdater $updater,
        private CommandBusInterface $bus
    ) {
    }

    public function __invoke(UpdateAlbumCommand $command): void
    {
        $albumData = $command->dto;
        $album = $this->updater->fromDto($albumData);

        $this->bus->dispatch(new OnUpdateAlbumCommand(
            $album->getId(),
            $albumData->source,
            $this->trackRepository->getAllIdsByAlbum($album->getId())
        ));
    }
}