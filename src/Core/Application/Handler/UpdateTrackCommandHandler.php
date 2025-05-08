<?php

namespace App\Core\Application\Handler;

use App\Core\Application\Updater\TrackUpdater;
use MusicPlayground\Contract\Application\SongParser\Command\UpdateTrackCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateTrackCommandHandler
{
    public function __construct(private TrackUpdater $updater)
    {
    }

    public function __invoke(UpdateTrackCommand $command): void
    {
        $this->updater->fromDTO($command->dto);
    }
}