<?php

namespace App\Core\Application\Handler;

use App\Core\Application\Serializer\PlaylistSerializer;
use App\Core\Domain\Repository\Playlist\PlaylistRepositoryInterface;
use App\Shared\Application\Interface\CommandBusInterface;
use App\Shared\Domain\FlusherInterface;
use MusicPlayground\Contract\Application\Operation\OperationNotificationsCommand;
use MusicPlayground\Contract\Application\Playlist\Command\CreatePlaylistCommand;
use MusicPlayground\Contract\Application\Playlist\Command\OnCreatedPlaylistCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CretePlaylistCommandHandler
{
    public function __construct(
        private PlaylistRepositoryInterface $repository,
        private PlaylistSerializer $serializer,
        private FlusherInterface $flusher,
        private CommandBusInterface $bus
    ) {
    }

    public function __invoke(CreatePlaylistCommand $command): void
    {
        $playlist = $this->repository->findBySource(
            $this->serializer->sourceFromDTO($command->playlist->source)
        );

        if ($playlist !== null && $playlist->getCreationOperationId() !== $command->operationId) {
            $this->bus->dispatch(
                new OperationNotificationsCommand(
                    $command->operationId, 'This playlist is already imported', 415
                )
            );

            return;
        }

        $this->repository->save(
            $playlist ?: $this->serializer->fromDTO($command->playlist, $command->operationId)
        );
        $this->flusher->flush();

        $this->bus->dispatchMany([
            new OnCreatedPlaylistCommand(
                $command->operationId, $playlist->getId(), $command->playlist->source
            ),
            new OperationNotificationsCommand(
                $command->operationId, 'Playlist import started', 421
            )
        ]);
    }
}