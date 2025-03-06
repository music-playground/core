<?php

namespace App\Tests\Core\Integration\Album;

use App\Core\Domain\Entity\Album;
use App\Core\Domain\Entity\AlbumCast;
use App\Core\Domain\Entity\Artist;
use App\Core\Domain\Enum\Source;
use App\Core\Domain\Exception\AlbumNotFoundException;
use App\Core\Domain\Repository\AlbumRepositoryInterface;
use App\Core\Domain\Repository\ArtistRepositoryInterface;
use App\Core\Domain\Repository\SearchParams;
use App\Core\Domain\ValueObject\IdSource;
use App\Shared\Domain\FlusherInterface;
use App\Shared\Domain\ValueObject\Pagination;
use App\Tests\Core\Integration\Cleaner\CleanerInterface;
use DateTimeImmutable;
use DateTimeInterface;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNull;

final class AlbumRepositoryTest extends KernelTestCase
{
    use SaveAlbumWithArtistsTrait;

    private CleanerInterface $cleaner;
    private AlbumRepositoryInterface $repository;
    private ArtistRepositoryInterface $artistRepository;
    private FlusherInterface $flusher;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = $this->getContainer();
        /** @var CleanerInterface $cleaner */
        $cleaner = $container->get(CleanerInterface::class);
        /** @var FlusherInterface $flusher */
        $flusher = $container->get(FlusherInterface::class);
        /** @var ArtistRepositoryInterface $artistRepository */
        $artistRepository = $container->get(ArtistRepositoryInterface::class);
        /** @var AlbumRepositoryInterface $repository */
        $repository = $container->get(AlbumRepositoryInterface::class);

        $this->cleaner = $cleaner;
        $this->flusher = $flusher;
        $this->artistRepository = $artistRepository;
        $this->repository = $repository;

        $this->cleaner->clean([Album::class, Artist::class]);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->cleaner->clean([Album::class, Artist::class]);

        parent::tearDown();
    }

    public function test_save_receive_and_delete(): void
    {
        $album = new Album('FREERIO 3', '1x1ffe', new IdSource('1', Source::Spotify), ['rap'], ['1'], new DateTimeImmutable());

        $this->repository->save($album);
        $this->flusher->flush();

        $savedAlbum = $this->repository->getById($album->getId());

        $this->assertAlbums($album, $savedAlbum);

        $this->repository->delete($savedAlbum->getId());
        $this->flusher->flush();

        $this->expectException(AlbumNotFoundException::class);

        $this->repository->getById($album->getId());
    }

    /**
     * @throws AlbumNotFoundException
     */
    public function test_cast_save_get_and_delete(): void
    {
        $artists = [
            new Artist('Kizaru', new IdSource('1', Source::Spotify), '1xd3fb'),
            new Artist('Big Baby Tape', new IdSource('2', Source::Spotify), '1bdf3z')
        ];
        $album = new Album('Bandana I', '1ffdz', new IdSource('1', Source::Spotify), ['rap'], [], new DateTimeImmutable());

        $this->saveAlbumWithArtists($album, $artists);
        $this->flusher->flush();

        $cast = $this->repository->getCastById($album->getId());

        $this->assertAlbumAndCast($album, $artists, $cast);

        $this->repository->delete($album->getId());
        $this->flusher->flush();

        $this->expectException(AlbumNotFoundException::class);

        $this->repository->getCastById($album->getId());
    }

    public function test_batch_save_and_count(): void
    {
        $albums = [];

        for ($albumIndex = 0; $albumIndex < 10; $albumIndex++) {
            $albums[$albumIndex][0] = new Album(
                "Album $albumIndex",
                md5(random_bytes(5)),
                new IdSource($albumIndex, Source::Spotify),
                [$albumIndex],
                [],
                new DateTimeImmutable()
            );

            for ($artistIndex = 0; $artistIndex < $albumIndex + 1; $artistIndex++) {
                $albums[$albumIndex][1][] = new Artist(
                    "Artist $albumIndex.$artistIndex",
                    new IdSource("$albumIndex.$artistIndex", Source::Spotify),
                    md5(random_bytes(5))
                );
            }

            $this->saveAlbumWithArtists($albums[$albumIndex][0], $albums[$albumIndex][1]);
        }

        $this->flusher->flush();

        $casts = $this->repository->getCastAll(new Pagination(3, 1));

        for ($i = 0; $i < 3; $i++) {
            [$album, $artist] = $albums[count($albums) - 2 - $i];

            $this->assertAlbumAndCast($album, $artist, array_shift($casts));
        }

        assertCount(0, $casts);
        assertEquals(10, $this->repository->count());

        $a = $albums[0][1][0]->getId();
        $artistsCasts = $this->repository->getCastAll(
            new Pagination(1, 0), new SearchParams(artistId: $a)
        );

        assertCount(1, $artistsCasts);
        $this->assertAlbumAndCast($albums[0][0], $albums[0][1], $artistsCasts[0]);
    }

    public function test_find_by_source(): void
    {
        $album = new Album('Flirt na vpiske', '1xd21', new IdSource('1', Source::Spotify), ['pop'], ['1'], new DateTimeImmutable());

        $this->repository->save($album);
        $this->flusher->flush();

        $found = $this->repository->findBySource($album->getSource());

        $this->assertAlbums($album, $found);

        $this->repository->delete($album->getId());
        $this->flusher->flush();

        assertNull($this->repository->findBySource($album->getSource()), 'Not existed source can`t be found in repository');
    }

    public function test_duplicate_insert(): void
    {
        $firstAlbum = new Album(
            'NO COMMERCIAL LYRICS', '1ff3d', new IdSource('1', Source::Spotify), ['rap'], ['1'], new DateTimeImmutable()
        );
        $secondAlbum = new Album(
            'OPG CITY', '3zuyd', new IdSource('1', Source::Spotify), ['hip-hop'], ['2'], new DateTimeImmutable()
        );

        $this->repository->save($firstAlbum);
        $this->repository->save($secondAlbum);

        $this->expectException(RuntimeException::class);

        $this->flusher->flush();
    }

    public function test_find_by_artist_id(): void
    {
        $albums = [
            new Album('BROKE LIVES MATTER', '1z1b4', new IdSource('1', Source::Spotify), [], ['1', '2'], new DateTimeImmutable()),
            new Album('BOLSHIE KURTKI', '98trh', new IdSource('2', Source::Spotify), [], ['2', '3'], new DateTimeImmutable()),
            new Album('AA LANGUAGE', 'l11fd', new IdSource('3', Source::Spotify), [], ['1', '3'], new DateTimeImmutable())
        ];

        foreach ($albums as $album) {
            $this->repository->save($album);
        }

        $this->flusher->flush();

        $ids = $this->repository->findIdsByAuthor('2');

        assertEquals([$albums[0]->getId(), $albums[1]->getId()], $ids);
    }

    private function assertAlbums(Album $current, Album $expected): void
    {
        assertEquals($expected->getId(), $current->getId());
        assertEquals($expected->getName(), $current->getName());
        assertEquals($expected->getSource()->getId(), $current->getSource()->getId());
        assertEquals($expected->getSource()->getName(), $current->getSource()->getName());
        assertEquals($expected->getReleaseDate()->format(DateTimeInterface::ATOM), $current->getReleaseDate()->format(DateTimeInterface::ATOM));
        assertEquals($expected->getGenres(), $current->getGenres());
        assertEquals($expected->getCoverId(), $current->getCoverId());
    }

    /** @param Artist[] $artists */
    private function assertAlbumAndCast(Album $current, array $artists, AlbumCast $expected): void
    {
        assertEquals($current->getId(), $expected->id);
        assertEquals($current->getName(), $expected->name);
        assertEquals($current->getGenres(), $expected->genres);
        assertEquals($current->getSource()->getName(), $expected->source->name);
        assertEquals($current->getSource()->getId(), $expected->source->id);
        assertEquals($current->getReleaseDate()->format('Y-m-d'), $expected->releaseDate);
        assertEquals($current->getGenres(), $expected->genres);
        assertEquals($current->getCoverId(), $expected->cover);

        for ($i = 0; $i < count($artists); $i++) {
            assertEquals($artists[$i]->getId(), $expected->artists[$i]->id);
            assertEquals($artists[$i]->getName(), $expected->artists[$i]->name);
            assertEquals($artists[$i]->getAvatarId(), $expected->artists[$i]->avatarId);
        }
    }
}