<?php

namespace App\Core\Application\Handler;

use App\Core\Application\Updater\TrackUpdater;
use App\Core\Domain\Repository\Playlist\PlaylistRepositoryInterface;
use App\Shared\Application\Interface\CommandBusInterface;
use MusicPlayground\Contract\Application\Operation\OperationNotificationsCommand;
use MusicPlayground\Contract\Application\SongParser\Command\UpdateTrackForPlaylistCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler]
final readonly class UpdateTrackForPlaylistCommandHandler
{
    public function __construct(
        private CommandBusInterface $bus,
        private PlaylistRepositoryInterface $playlistRepository,
        private TrackUpdater $updater
    ) {
    }

    public function __invoke(UpdateTrackForPlaylistCommand $command): void
    {
        $track = $this->updater->fromDTO($command->track);

        $this->playlistRepository->saveTrack($track->getId(), $command->playlistId);
        $this->bus->dispatch(new OperationNotificationsCommand(
            $command->operationId,
            'Track`s been added to playlist',
            423,
            ['id' => $track->getId()]
        ));
    }
}