<?php

namespace App\Core\Infrastructure\Doctrine\Repository;

use App\Core\Domain\Entity\Artist;
use App\Core\Domain\Entity\ArtistCast;
use App\Core\Domain\Exception\ArtistNotFoundException;
use App\Core\Domain\Repository\Artist\ArtistRepositoryInterface;
use App\Core\Domain\Repository\Artist\SearchParams;
use App\Core\Domain\ValueObject\ArtistAvatar;
use App\Core\Domain\ValueObject\IdSource;
use App\Shared\Domain\Repository\LockMode;
use App\Shared\Domain\ValueObject\Pagination;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\LockException;
use Doctrine\ODM\MongoDB\Mapping\MappingException;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

final readonly class MongoArtistRepository implements ArtistRepositoryInterface
{
    private DocumentRepository $repository;

    public function __construct(private DocumentManager $dm)
    {
        $this->repository = $this->dm->getRepository(Artist::class);
    }

    public function save(Artist $artist): void
    {
        $this->dm->persist($artist);
    }

    /**
     * @throws MappingException
     * @throws LockException
     * @throws ArtistNotFoundException
     */
    public function getById(string $id, LockMode $lock = LockMode::NONE): Artist
    {
        $artist = $this->repository->find($id);

        if ($artist === null) {
            throw new ArtistNotFoundException();
        }

        return $artist;
    }

    /**
     * @throws MappingException
     * @throws LockException
     */
    public function findById(string $id, LockMode $lock = LockMode::NONE): ?Artist
    {
        return $this->repository->find($id);
    }

    /**
     * @throws ArtistNotFoundException
     * @throws MappingException
     * @throws LockException
     */
    public function getCastById(string $id): ArtistCast
    {
        $artist = $this->getById($id);

        return $this->artistToCast($artist);
    }

    public function getCastAll(Pagination $pagination, ?SearchParams $params = null): array
    {
        if ($pagination->getCount() === 0) return [];

        $artists = $this->repository->findBy(
            $params?->sources !== null ? ['source' => ['$in' => $params->sources]] : [],
            ['id' => -1],
            $pagination->getCount(),
            $pagination->getFrom()
        );

        return array_map(fn (Artist $artist) => $this->artistToCast($artist), $artists);
    }

    /**
     * @throws MongoDBException
     */
    public function count(): int
    {
        return $this->repository->createQueryBuilder()->find()->count()->getQuery()->execute();
    }

    /**
     * @throws MappingException
     * @throws LockException
     * @throws ArtistNotFoundException
     */
    public function delete(string $id): void
    {
        $artist = $this->getById($id, LockMode::PESSIMISTIC_EXCLUSIVE);

        $this->dm->remove($artist);
    }

    private function artistToCast(Artist $artist): ArtistCast
    {
        $source = $artist->getSource();

        return new ArtistCast(
            $artist->getId(),
            $artist->getName(),
            (string)$source,
            $artist->getGenres(),
            new ArtistAvatar($artist->getAvatarId())
        );
    }

    public function findBySource(IdSource $source, LockMode $lock = LockMode::NONE): ?Artist
    {
        return $this->repository->findOneBy(['source' => $source]);
    }
}