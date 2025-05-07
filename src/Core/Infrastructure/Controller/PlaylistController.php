<?php

namespace App\Core\Infrastructure\Controller;

use App\Core\Domain\Repository\Playlist\PlaylistRepositoryInterface;
use App\Core\Domain\Repository\Playlist\SearchParams;
use App\Shared\Application\Interface\CommandBusInterface;
use App\Shared\Domain\ValueObject\Pagination;
use MusicPlayground\Contract\Application\SongParser\Command\ParsePlaylistCommand;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/v1/playlists')]
class PlaylistController extends AbstractController
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
        private readonly PlaylistRepositoryInterface $repository,
        private readonly int $maxLimitValue
    ) {
    }

    #[Route('/parse/{id}', methods: 'POST')]
    public function parse(string $id): JsonResponse
    {
        $this->commandBus->dispatch($command = new ParsePlaylistCommand($id));

        return $this->json(['operationId' => $command->operationId]);
    }

    #[Route('/', methods: 'GET')]
    public function getMany(
        #[MapQueryParameter(filter: FILTER_VALIDATE_INT)] ?int $limit = null,
        #[MapQueryParameter(filter: FILTER_VALIDATE_INT)] ?int $from = null,
        #[MapQueryParameter] ?string $ids = null
    ): JsonResponse {
        $pagination = new Pagination(
            min(max($limit ?? $this->maxLimitValue, 0), $this->maxLimitValue),
            max($from ?? 0, 0)
        );
        $searchParams = new SearchParams($ids ? explode(',', $ids) : null);

        return $this->json([
            'count' => $this->repository->count($searchParams),
            'items' => $this->repository->getCastAll($pagination, $searchParams)
        ]);
    }
}