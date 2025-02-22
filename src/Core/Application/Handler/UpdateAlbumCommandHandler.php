<?php

namespace App\Core\Application\Handler;

use MusicPlayground\Contract\Application\SongParser\Command\UpdateAlbumCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateAlbumCommandHandler
{
    public function __invoke(UpdateAlbumCommand $command): void
    {
    }
}