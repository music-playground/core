<?php

namespace App\Core\Infrastructure\Controller;

use App\Core\Domain\Exception\AlbumNotFoundException;
use App\Core\Domain\Repository\Album\AlbumRepositoryInterface;
use App\Core\Domain\Repository\Album\SearchParams;
use App\Shared\Domain\ValueObject\Pagination;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/v1/albums')]
final class AlbumController extends AbstractController
{
    public function __construct(
        private readonly AlbumRepositoryInterface $repository,
        private readonly int $maxLimitValue
    ) {
    }

    /**
     * @throws AlbumNotFoundException
     */
    #[Route(path: '/{id}', methods: 'GET')]
    public function getById(string $id): JsonResponse
    {
        return $this->json($this->repository->getCastById($id));
    }

    #[Route(path: '/', methods: 'GET')]
    public function getMany(
        #[MapQueryParameter(filter: FILTER_VALIDATE_INT)] ?int $limit = null,
        #[MapQueryParameter(filter: FILTER_VALIDATE_INT)] ?int $from = null,
        #[MapQueryParameter] ?string $ids = null,
        #[MapQueryParameter] ?string $artistId = null
    ): JsonResponse
    {
        $pagination = new Pagination(
            min(max($limit ?? $this->maxLimitValue, 0), $this->maxLimitValue),
            max($from ?? 0, 0)
        );
        $searchParams = new SearchParams($artistId, $ids ? explode(',', $ids) : null);

            return $this->json([
            'count' => $this->repository->count($searchParams),
            'items' => $this->repository->getCastAll($pagination, $searchParams)
        ]);
    }
}