<?php

namespace App\Core\Infrastructure\Controller;

use App\Shared\Application\Interface\CommandBusInterface;
use MusicPlayground\Contract\Application\SongParser\Command\ParsePlaylistCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/v1/playlists')]
class PlaylistController extends AbstractController
{
    public function __construct(
        private readonly CommandBusInterface $commandBus
    ) {
    }

    #[Route('/parse/{id}', methods: 'POST')]
    public function parse(string $id): JsonResponse
    {
        $this->commandBus->dispatch($command = new ParsePlaylistCommand($id));

        return $this->json(['operationId' => $command->operationId]);
    }
}