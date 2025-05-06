<?php

namespace App\Core\Application\Handler;

use App\Core\Application\Serializer\ArtistSerializer;
use App\Core\Application\Serializer\TrackSerializer;
use App\Core\Application\Updater\TrackUpdater;
use App\Core\Domain\Entity\Track;
use App\Core\Domain\Repository\Track\TrackRepositoryInterface;
use App\Shared\Domain\FlusherInterface;
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