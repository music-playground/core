<?php

namespace App\Core\Infrastructure\Controller;

use App\Core\Domain\Exception\ArtistNotFoundException;
use App\Core\Domain\Repository\ArtistRepositoryInterface;
use App\Shared\Domain\ValueObject\Pagination;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/v1/artists')]
final class ArtistController extends AbstractController
{
    public function __construct(
        private readonly ArtistRepositoryInterface $repository,
        private readonly int $maxLimitValue
    ) {
    }

    /**
     * @throws ArtistNotFoundException
     */
    #[Route(path: '/{id}', methods: 'GET')]
    public function getById(string $id): JsonResponse
    {
        return $this->json($this->repository->getCastById($id));
    }

    #[Route(methods: 'GET')]
    public function getMany(
        #[MapQueryParameter(filter: FILTER_VALIDATE_INT)] ?int $limit = null,
        #[MapQueryParameter(filter: FILTER_VALIDATE_INT)] ?int $from = null,
    ): JsonResponse {
        $pagination = new Pagination(
            min(max($limit ?? $this->maxLimitValue, 0), $this->maxLimitValue),
            max($from ?? 0, 0)
        );

        return $this->json([
            'count' => $this->repository->count(),
            'items' => $this->repository->getCastAll($pagination)
        ]);
    }
}