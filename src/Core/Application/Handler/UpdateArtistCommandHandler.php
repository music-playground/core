<?php

namespace App\Core\Application\Handler;

use MusicPlayground\Contract\Application\SongParser\Command\UpdateArtistCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateArtistCommandHandler
{
    public function __invoke(UpdateArtistCommand $command): void
    {
    }
}