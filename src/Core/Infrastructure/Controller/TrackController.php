<?php

namespace App\Core\Infrastructure\Controller;

use App\Core\Domain\Exception\TrackNotFoundException;
use App\Core\Domain\Repository\Track\SearchParams;
use App\Core\Domain\Repository\Track\TrackRepositoryInterface;
use App\Shared\Domain\ValueObject\Pagination;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/v1/tracks')]
final class TrackController extends AbstractController
{
    public function __construct(
        private readonly TrackRepositoryInterface $repository,
        private readonly int $maxLimitValue
    ) {
    }

    /**
     * @throws TrackNotFoundException
     */
    #[Route(path: '/{id}', methods: 'GET')]
    public function getById(string $id): JsonResponse
    {
        return $this->json($this->repository->getCastById($id));
    }

    #[Route(path: '/', methods: 'GET')]
    public function getMany(
        #[MapQueryParameter(filter: FILTER_VALIDATE_INT)] ?string $limit = null,
        #[MapQueryParameter(filter: FILTER_VALIDATE_INT)] ?string $from = null,
        #[MapQueryParameter] ?string $albumId = null
    ): JsonResponse {
        $pagination = new Pagination(
            min(max($limit ?? $this->maxLimitValue, 0), $this->maxLimitValue),
            max($from ?? 0, 0)
        );
        $searchParams = new SearchParams($albumId);

        return $this->json([
            'count' => $this->repository->count($searchParams),
            'items' => $this->repository->getCastAll($pagination, $searchParams)
        ]);
    }
}