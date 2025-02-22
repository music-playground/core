<?php

namespace App\Core\Application\Handler;

use App\Shared\Application\Interface\CommandBusInterface;
use MusicPlayground\Contract\Application\SongParser\Command\OnUpdateArtistCommand;
use MusicPlayground\Contract\Application\SongParser\Command\UpdateArtistCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateArtistCommandHandler
{
    public function __construct(private CommandBusInterface $bus)
    {
    }

    public function __invoke(UpdateArtistCommand $command): void
    {
        $this->bus->dispatch(new OnUpdateArtistCommand(
            $command->dto->source
        ));
    }
}