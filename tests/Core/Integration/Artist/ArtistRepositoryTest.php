<?php

namespace App\Tests\Core\Integration\Artist;

use App\Core\Domain\Entity\Artist;
use App\Core\Domain\Entity\ArtistCast;
use App\Core\Domain\Enum\Source;
use App\Core\Domain\Exception\ArtistNotFoundException;
use App\Core\Domain\Repository\ArtistRepositoryInterface;
use App\Core\Domain\ValueObject\IdSource;
use App\Shared\Domain\FlusherInterface;
use App\Shared\Domain\ValueObject\Pagination;
use App\Tests\Core\Integration\Cleaner\CleanerInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNull;

final class ArtistRepositoryTest extends KernelTestCase
{
    private ArtistRepositoryInterface $repository;
    private FlusherInterface $flusher;
    private CleanerInterface $cleaner;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = self::getContainer();
        /** @var ArtistRepositoryInterface $repository */
        $repository = $container->get('public.' . ArtistRepositoryInterface::class);
        /** @var FlusherInterface $flusher */
        $flusher = $container->get('public.' . FlusherInterface::class);
        /** @var CleanerInterface $cleaner */
        $cleaner = $container->get(CleanerInterface::class);

        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->cleaner = $cleaner;

        $this->cleaner->clean([Artist::class]);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->cleaner->clean([Artist::class]);

        parent::tearDown();
    }

    /**
     * @throws ArtistNotFoundException
     */
    public function test_save_receive_and_delete(): void
    {
        $artist = new Artist('etherfoun', 'afd6f', new IdSource('1', Source::Spotify));

        $this->repository->save($artist);
        $this->flusher->flush();

        $savedArtist = $this->repository->getById($artist->getId());

        $this->assertArtists($artist, $savedArtist);

        $this->repository->delete($artist->getId());
        $this->flusher->flush();

        $this->expectException(ArtistNotFoundException::class);

        $this->repository->getById($artist->getId());
    }

    /**
     * @throws ArtistNotFoundException
     */
    public function test_cast_save_receive_and_delete(): void
    {
        $artist = new Artist('9mice', 'az41f', new IdSource('1', Source::Spotify));

        $this->repository->save($artist);
        $this->flusher->flush();

        $cast = $this->repository->getCastById($artist->getId());

        $this->assertArtistAndCast($artist, $cast);

        $this->repository->delete($artist->getId());
        $this->flusher->flush();

        $this->expectException(ArtistNotFoundException::class);

        $this->repository->getCastById($artist->getId());
    }

    public function test_batch_save_and_get_all(): void
    {
        $first = new Artist('etherfoun', 'afd6f', new IdSource('1', Source::Spotify));
        $second = new Artist('OG Buda', 'dz561', new IdSource('2', Source::Spotify));

        $this->repository->save($first);
        $this->repository->save($second);
        $this->flusher->flush();

        $artists = $this->repository->getCastAll(new Pagination(3, 0));

        assertCount(2, $artists);

        $this->assertArtistAndCast($second, $artists[0]);
        $this->assertArtistAndCast($first, $artists[1]);
    }

    public function test_duplicate_save(): void
    {
        $first = new Artist('etherfoun1', 'afd6f', new IdSource('1', Source::Spotify));
        $second = new Artist('etherfoun2', 'vz112', new IdSource('1', Source::Spotify));

        $this->repository->save($first);
        $this->repository->save($second);

        $this->expectException(RuntimeException::class);

        $this->flusher->flush();
    }

    public function test_delete_not_existed(): void
    {
        $this->expectException(ArtistNotFoundException::class);

        $this->repository->getById('nobody');
    }

    public function test_find_by_source(): void
    {
        $artist = new Artist('CUPSIZE', 'afx12', new IdSource('ff1d6n', Source::Spotify));
        $this->repository->save($artist);

        $this->flusher->flush();

        $savedArtist = $this->repository->findBySource($artist->getSource());

        $this->assertArtists($artist, $savedArtist);

        $null = $this->repository->findBySource(new IdSource('ff1d5p', Source::Spotify));

        assertNull($null, 'Not wrote artist can`t be found');
    }

    private function assertArtists(Artist $current, Artist $expected): void
    {
        assertEquals($expected->getId(), $current->getId());
        assertEquals($expected->getName(), $current->getName());
        assertEquals($expected->getSource()->getName(), $current->getSource()->getName());
        assertEquals($expected->getSource()->getId(), $current->getSource()->getId());
        assertEquals($expected->getAvatarId(), $current->getAvatarId());
        assertEquals($expected->getGenres(), $current->getGenres());
    }

    private function assertArtistAndCast(Artist $current, ArtistCast $expected): void
    {
        assertEquals($expected->id, $current->getId());
        assertEquals($expected->name, $current->getName());
        assertEquals($expected->source->name, $current->getSource()->getName());
        assertEquals($expected->source->id, $current->getSource()->getId());
        assertEquals($expected->avatar, $current->getAvatarId());
        assertEquals($expected->genres, $current->getGenres());
    }
}