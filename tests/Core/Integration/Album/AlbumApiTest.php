<?php

namespace App\Tests\Core\Integration\Album;

use App\Core\Domain\Entity\Album;
use App\Core\Domain\Entity\Artist;
use App\Core\Domain\Repository\Album\AlbumRepositoryInterface;
use App\Shared\Domain\FlusherInterface;
use App\Shared\Infrastructure\Util\Query;
use App\Tests\Core\Integration\Cleaner\CleanerInterface;
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
    private CleanerInterface $cleaner;
    private FlusherInterface $flusher;

    protected function setUp(): void
    {
        $this->client = self::createClient();

        $container = $this->getContainer();
        /** @var AlbumRepositoryInterface $repository */
        $repository = $container->get(AlbumRepositoryInterface::class);
        /** @var FixtureInterface $fixture */
        $fixture = $container->get('album-fixture');
        /** @var CleanerInterface $cleaner */
        $cleaner = $container->get(CleanerInterface::class);
        /** @var FlusherInterface $flusher */
        $flusher = $container->get(FlusherInterface::class);

        $this->repository = $repository;
        $this->cleaner = $cleaner;
        $this->flusher = $flusher;

        $this->cleaner->clean([Album::class, Artist::class]);
        [$this->albums, $this->artists] = $fixture->load();

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->cleaner->clean([Album::class, Artist::class]);

        parent::tearDown();
    }

    public function test_get_by_id_endpoint(): void
    {
        $this->client->request('GET', '/api/v1/albums/' . $this->albums[0]->getId());
        $this->assertResponseIsSuccessful();
        $body = json_decode($this->client->getResponse()->getContent());

        $this->assertSchema($body, $this->generateAlbumSchema($this->albums[0], [ $this->artists[0] ]));

        $this->repository->delete($this->albums[0]->getId());
        $this->flusher->flush();

        $this->client->request('GET', '/api/v1/albums/' . $this->albums[0]->getId());
        $this->assertResponseStatusCodeSame(404);
        $body = json_decode($this->client->getResponse()->getContent());

        $this->assertSchema($body, json_decode(json_encode([
            'type' => 'object',
            'properties' => [
                'error' => [ 'enum' => [ 'Album not found' ] ],
                'code' => [ 'enum' => [ 200 ] ]
            ],
            'required' => ['error', 'code']
        ])));
    }

    /**
     * @dataProvider searchProvider
     */
    public function test_get_many_endpoint(array $params, array $results, int $count): void
    {
        if (isset($params['artistId'])) {
            $params['artistId'] = $this->artists[$params['artistId']]->getId();
        }

        if (isset($params['ids'])) {
            $params['ids'] = join(',', array_map(fn (int $albumIndex) => $this->albums[$albumIndex]->getId(), $params['ids']));
        }

        $this->client->request('GET', '/api/v1/albums/?' . (new Query())->fromArray($params));
        $this->assertResponseIsSuccessful();
        $body = json_decode($this->client->getResponse()->getContent());

        $items = [];

        foreach ($results as [$album, $artists]) {
            $items []= [$this->albums[$album], array_map(fn ($arist) => $this->artists[$arist], $artists)];
        }

        $schema = $this->generatePaginationAlbumSchema($items, $count);
        $this->assertSchema($body, $schema);
    }

    private function generatePaginationAlbumSchema(array $albums, int $count): object
    {
        $items = array_map(function (array $album) {
            return $this->generateAlbumSchema($album[0], $album[1]);
        }, $albums);

        return json_decode(json_encode([
            'type' => 'object',
            'properties' => [
                ...[ 'count' => [ 'enum' => [ $count ] ] ],
                ...(count($items) !== 0 ? ['items' => [ 'type' => 'array', 'items' => $items ]] : [])
            ],
            'required' => [ 'count', 'items' ]
        ]));
    }

    private function generateAlbumSchema(Album $album, array $artists): object
    {
        $artistItems = array_map(function (Artist $artist) {
            return json_decode(json_encode([
                'type' => 'object',
                'properties' => [
                    'id' => [ 'enum' => [ $artist->getId() ] ],
                    'name' => [ 'enum' => [ $artist->getName() ] ],
                    'avatarId' => [ 'enum' => [ $artist->getAvatarId() ] ]
                ]
            ]));
        }, $artists);

        return json_decode(json_encode([
            'type' => 'object',
            'properties' => [
                'id' => ['enum' => [ $album->getId() ] ],
                'name' => ['enum' => [ $album->getName()] ],
                'cover' => ['enum' => [ $album->getCoverId() ] ],
                'genres' => [ 'type' => 'array', 'const' => $album->getGenres() ],
                'source' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => [ 'enum' => [ $album->getSource()->getName() ] ],
                        'id' => [ 'enum' => [ $album->getSource()->getId() ] ]
                    ],
                    'required' => [ 'name', 'id' ]
                ],
                'releaseDate' => [ 'enum' => [ $album->getReleaseDate()->format('Y-m-d') ] ],
                'artists' => [ 'type' => 'array', 'items' => $artistItems ]
            ],
            'required' => [ 'id', 'name', 'cover', 'genres', 'source', 'releaseDate', 'artists' ]
        ]));
    }

    public static function searchProvider(): array
    {
        return [
            [ [ 'limit' => 2, 'from' => 1 ], [ [ 8, [ 9 ] ], [ 7, [ 9 ] ] ], 10 ],
            [ [ 'limit' => 1, 'from' => 1, 'artistId' => 5 ], [ [ 4, [ 5 ] ] ], 2 ],
            [ [ 'limit' => -1, 'from' => -1 ], [], 10 ],
            [ [ 'ids' => [ 1, 3, 7 ] ], [ [ 7, [ 9 ] ], [ 3, [ 0, 2, 4 ] ], [ 1, [ 0, 1 ] ] ], 3 ]
        ];
    }
}