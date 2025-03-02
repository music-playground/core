<?php

namespace App\Tests\Core\Integration\Album;

use App\Core\Domain\Entity\Album;
use App\Core\Domain\Entity\Artist;
use App\Core\Domain\Repository\AlbumRepositoryInterface;
use App\Shared\Infrastructure\Util\Query;
use App\Tests\Shared\FixtureInterface;
use App\Tests\Shared\SchemaAssertTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AlbumApiTest extends WebTestCase
{
    use SchemaAssertTrait;
    use SaveAlbumWithArtistsTrait;

    private KernelBrowser $client;
    private AlbumRepositoryInterface $repository;
    /** @var Album[] */
    private array $albums;
    /** @var Artist[] */
    private array $artists;

    protected function setUp(): void
    {
        $this->client = self::createClient();

        $container = $this->getContainer();
        /** @var AlbumRepositoryInterface $repository */
        $repository = $container->get(AlbumRepositoryInterface::class);
        /** @var FixtureInterface $fixture */
        $fixture = $container->get('album-fixture');

        $this->repository = $repository;

        [$this->albums, $this->artists] = $fixture->load();

        parent::setUp();
    }

    public function test_get_many_endpoint(array $data): void
    {
        [ $params, $results ] = $data;

        if (isset($params['artist-id'])) {
            $params['artist-id'] = $this->artists[$params['artist-id']];
        }

        $this->client->request('GET', '/api/v1/albums?' . (new Query())->fromArray($params));
        $this->assertResponseIsSuccessful();
        $body = json_decode($this->client->getResponse()->getContent(), true);

        foreach ($results as [$album, $artists]) {
            $this->assertSchema($body, $this->generatePaginationAlbumSchema(
                [$this->albums[$album], array_map(fn ($arist) => $this->artists[$arist], $artists)],
                count($this->albums)
            ));
        }
    }

    private function generatePaginationAlbumSchema(array $albums, int $count): object
    {
        $items = array_map(function (array $album) {
            return $this->generateAlbumSchema($album[0], $album[1]);
        }, $albums);

        return json_decode(json_encode([
            'type' => 'object',
            'properties' => [
                'count' => [ 'type' => 'int', 'enum' => [ $count ] ],
                'items' => [ 'type' => 'array', 'items' => $items ]
            ]
        ]));
    }

    private function generateAlbumSchema(Album $album, array $artists): object
    {
        $artistItems = array_map(function (Artist $artist) {
            return [
                'type' => 'object',
                'properties' => [
                    'id' => $artist->getId(),
                    'name' => $artist->getName(),
                    'avatarId' => $artist->getAvatarId()
                ],
                'required' => ['id', 'name', 'avatarId']
            ];
        }, $artists);

        return json_decode(json_encode([
            'type' => 'object',
            'properties' => [
                'id' => ['enum' => [$album->getId()]],
                'name' => ['enum' => [$album->getName()]],
                'cover' => ['enum' => [$album->getCoverId()]],
                'genres' => ['type' => 'array', 'const' => $album->getGenres()],
                'source' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => ['enum' => [$album->getSource()->getName()]],
                        'id' => ['enum' => [$album->getSource()->getId()]]
                    ],
                    'required' => ['name', 'id']
                ],
                'releaseDate' => ['type' => 'array', 'enum' => [$album->getReleaseDate()->format('Y-m-d')]],
                'artists' => ['type' => 'array', 'items' => $artistItems]
            ],
            'required' => ['id', 'name', 'cover', 'genres', 'source', 'releaseDate', 'artists']
        ]));
    }

    public static function searchProvider(): array
    {
        return [
            [ [ 'limit' => 2, 'offset' => 1 ],  [ [ 8, [ 9 ] ], [ 7, [ 9 ] ] ] ],
            [ [ 'limit' => 1, 'offset' => 1, 'author-id' => 5 ], [ [ 4, [ 5 ] ] ] ],
            [ [ 'limit' => -1, 'offset' => -1 ], [] ],
            [ [ 'limit' => 'abc', 'offset' => 'abc' ], [] ],
            [ [ 'ids' => [ 1, 3, 7 ] ], [ [ 1, [ 0, 1 ] ], [ 3, [ 0, 2, 4 ] ], [ 7, [ 9 ] ] ] ]
        ];
    }
}