<?php

namespace App\Core\Infrastructure\Controller;

use App\Core\Domain\Exception\ArtistNotFoundException;
use App\Core\Domain\Repository\ArtistRepositoryInterface;
use App\Shared\Domain\ValueObject\Pagination;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/api/v1/artists')]
final class ArtistController extends AbstractController
{
    public function __construct(
        private readonly ArtistRepositoryInterface $repository,
        private readonly int $maxLimitValue
    ) {
    }

    #[Route(path: '/{id}')]
    public function getById(string $id): JsonResponse
    {
        try {
            $artist = $this->repository->getCastById($id);
        } catch (ArtistNotFoundException) {
            throw new HttpException(404, 'Artist not found', code: 100);
        }

        return new JsonResponse($artist);
    }

    #[Route]
    public function getMany(Request $request): JsonResponse
    {
        $params = $request->query->all();

        $pagination = new Pagination(
            min(max($params['limit'] ?? 0, 0), $this->maxLimitValue),
            max($params['from'] ?? 0, 0)
        );

        return new JsonResponse([
            'count' => $this->repository->count(),
            'items' => $this->repository->getCastAll($pagination)
        ]);
    }
}