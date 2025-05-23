<?php

namespace App\Tests\Core\Fixtures;

use App\Core\Domain\Entity\Album;
use App\Core\Domain\Entity\Artist;
use App\Core\Domain\Enum\Source;
use App\Core\Domain\ValueObject\IdSource;
use App\Shared\Domain\FlusherInterface;
use App\Tests\Shared\FixtureInterface;
use DateTimeImmutable;
use Doctrine\ODM\MongoDB\DocumentManager;
use Random\RandomException;

final readonly class AlbumFixture implements FixtureInterface
{
    public function __construct(
        private DocumentManager $dm,
        private FlusherInterface $flusher
    ) {
    }

    /**
     * @throws RandomException
     */
    public function load(): array
    {
        $artists = $this->getArtists();

        foreach ($artists as $artist) {
            $this->dm->persist($artist);
        }

        $this->flusher->flush();

        $albums = $this->getAlbums($artists);

        foreach ($albums as $album) {
            $this->dm->persist($album);
        }

        $this->flusher->flush();

        return [$albums, $artists];
    }

    /**
     * @throws RandomException
     */
    private function getArtists(): array
    {
        return [
            new Artist('Og Buda', new IdSource('c591b8f430', Source::Spotify), md5(random_bytes(5))), # 0
            new Artist('Mayot', new IdSource('d82f91a237', Source::Spotify), md5(random_bytes(5))), # 1
            new Artist('Blago White', new IdSource('a4c5d8e210', Source::Spotify), md5(random_bytes(5))), # 2
            new Artist('Kizaru', new IdSource('f7849cbe99', Source::Spotify), md5(random_bytes(5))), # 3
            new Artist('Big Baby Tape', new IdSource('b5e3a7d612', Source::Spotify), md5(random_bytes(5))), # 4
            new Artist('Morgenshtern', new IdSource('e0d4a3b178', Source::Spotify), md5(random_bytes(5))), # 5
            new Artist('Slava Marlow', new IdSource('c1a8f2e357', Source::Spotify), md5(random_bytes(5))), # 6
            new Artist('Pharaoh', new IdSource('d3f8b5c901', Source::Spotify), md5(random_bytes(5))), # 7
            new Artist('Oxxxymiron', new IdSource('a6b9c8d402', Source::Spotify), md5(random_bytes(5))), # 8
            new Artist('GONE.Fludd', new IdSource('b7d6e5f303', Source::Spotify), md5(random_bytes(5))) # 9
        ];
    }

    /**
     * @throws RandomException
     */
    private function getAlbums(array $artists): array
    {
        return [
            new Album('SZN', md5(random_bytes(5)), new IdSource('1', Source::Spotify), ['rap'], [$artists[0]->getId()], new DateTimeImmutable()), # 0
            new Album('Colors', md5(random_bytes(5)), new IdSource('2', Source::Spotify), ['rap', 'trap'], [$artists[0]->getId(), $artists[1]->getId()], new DateTimeImmutable('-1 year')), # 1
            new Album('Bandana', md5(random_bytes(5)), new IdSource('3', Source::Spotify), ['rap', 'boom bap'], [$artists[2]->getId(), $artists[3]->getId()], new DateTimeImmutable('-2 years')), # 2
            new Album('Raw', md5(random_bytes(5)), new IdSource('4', Source::Spotify), ['hip-hop'], [$artists[0]->getId(), $artists[2]->getId(), $artists[4]->getId()], new DateTimeImmutable('-3 months')), # 3
            new Album('Legendary', md5(random_bytes(5)), new IdSource('5', Source::Spotify), ['trap', 'hip-hop'], [$artists[5]->getId()], new DateTimeImmutable('-5 years')), # 4
            new Album('Ice', md5(random_bytes(5)), new IdSource('6', Source::Spotify), ['trap'], [$artists[6]->getId(), $artists[7]->getId()], new DateTimeImmutable('-6 months')), # 5
            new Album('Red Eyes', md5(random_bytes(5)), new IdSource('7', Source::Spotify), ['hip-hop'], [$artists[7]->getId(), $artists[8]->getId()], new DateTimeImmutable('-1 year')), # 6
            new Album('Gorgorod', md5(random_bytes(5)), new IdSource('8', Source::Spotify), ['rap', 'conceptual'], [$artists[9]->getId()], new DateTimeImmutable('-8 years')), # 7
            new Album('Superstar', md5(random_bytes(5)), new IdSource('9', Source::Spotify), ['rap', 'pop'], [$artists[9]->getId()], new DateTimeImmutable('-2 years')), # 8
            new Album('MONEYRAIN', md5(random_bytes(5)), new IdSource('10', Source::Spotify), ['trap', 'drill'], [$artists[3]->getId(), $artists[5]->getId(), $artists[7]->getId()], new DateTimeImmutable('-4 months')) # 9
        ];
    }
}
