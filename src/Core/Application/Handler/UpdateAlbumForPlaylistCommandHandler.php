<?php

namespace App\Core\Application\Handler;

use App\Core\Application\Updater\AlbumUpdater;
use App\Shared\Application\Interface\CommandBusInterface;
use MusicPlayground\Contract\Application\SongParser\Command\ParseTrackForPlaylistCommand;
use MusicPlayground\Contract\Application\SongParser\Command\UpdateAlbumForPlaylistCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateAlbumForPlaylistCommandHandler
{
    public function __construct(
        private CommandBusInterface $bus,
        private AlbumUpdater $updater
    ) {
    }

    public function __invoke(UpdateAlbumForPlaylistCommand $command): void
    {
        $albumData = $command->album;
        $album = $this->updater->fromDto($albumData);

        $this->bus->dispatch(new ParseTrackForPlaylistCommand(
            $command->operationId,
            $command->playlistId,
            $album->getId(),
            $command->track
        ));
    }
}