<?php

namespace App\Tests\Core\Integration\Artist;

use App\Core\Domain\Entity\Artist;
use App\Core\Domain\Enum\Source;
use App\Core\Domain\Exception\ArtistNotFoundException;
use App\Core\Domain\Repository\ArtistRepositoryInterface;
use App\Core\Domain\ValueObject\IdSource;
use App\Shared\Domain\FlusherInterface;
use App\Tests\Core\Integration\Cleaner\CleanerInterface;
use App\Tests\Shared\SchemaAssertTrait;
use Random\RandomException;
use Swaggest\JsonSchema\Exception;
use Swaggest\JsonSchema\InvalidValue;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use function PHPUnit\Framework\assertEquals;

final class ArtistApiTest extends WebTestCase
{
    use SchemaAssertTrait;

    private KernelBrowser $client;
    private ArtistRepositoryInterface $repository;
    private FlusherInterface $flusher;
    private CleanerInterface $cleaner;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $container = $this->client->getContainer();
        /** @var ArtistRepositoryInterface $repository */
        $repository = $container->get(ArtistRepositoryInterface::class);
        /** @var FlusherInterface $flusher */
        $flusher = $container->get(FlusherInterface::class);
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
     * @throws Exception
     * @throws InvalidValue
     * @throws ArtistNotFoundException
     * @dataProvider artistsProvider
     */
    public function test_get_by_id_endpoint(Artist $artist): void
    {
        $this->repository->save($artist);
        $this->flusher->flush();

        $id = $artist->getId();

        $this->client->request('GET', "/api/v1/artists/$id");
        $this->assertResponseIsSuccessful();

        $body = json_decode($this->client->getResponse()->getContent());
        $this->assertSchema($body, (object)$this->generateArtistSchema($artist));

        $this->repository->delete($id);
        $this->flusher->flush();

        $this->client->request('GET', "/api/v1/artists/$id");

        $this->assertResponseStatusCodeSame(404);

        $body = json_decode($this->client->getResponse()->getContent());
        $this->assertSchema($body, json_decode(json_encode([
            'type' => 'object',
            'properties' => [
                'error' => [ 'enum' => [ 'Artist not found' ] ],
                'code' => [ 'enum' => [ 100 ] ]
            ],
            'required' => ['error', 'code']
        ])));
    }

    /**
     * @throws Exception
     * @throws RandomException
     * @throws InvalidValue
     * @dataProvider paginationProvider
     */
    public function test_get_many_endpoint(int $from, int $limit): void
    {
        $savedArtists = [];
        $schema = [
            'type' => 'object',
            'properties' => [
                'items' => [ 'type' => 'array' ]
            ],
            'required' => [ 'count', 'items' ]
        ];

        for ($i = 0; $i < $from + $limit + 1; $i++) {
            $artist = new Artist("Artist$i", new IdSource($i, Source::Spotify), md5(random_bytes(5)));

            $this->repository->save($artist);

            $savedArtists[] = $artist;
        }

        $this->flusher->flush();
        $count = $this->repository->count();

        $schema['properties']['count']['enum'] = [ $count ];

        for ($i = $limit; $i >= $from; $i--)  {
            $schema['properties']['items']['items'] []= $this->generateArtistSchema($savedArtists[$i]);
        }

        $this->client->request('GET', "/api/v1/artists?from=$from&limit=$limit");

        $this->assertResponseIsSuccessful();
        $body = json_decode($this->client->getResponse()->getContent());
        $this->assertSchema($body, json_decode(json_encode($schema)));
    }

    /**
     * @throws RandomException
     */
    public function test_get_many_params_constrains(): void
    {
        $limit = $this->client->getContainer()->getParameter('api.max_limit_value');

        for ($i = 0; $i < $limit + 1; $i++) {
            $artist = new Artist("Artist$i", new IdSource($i, Source::Spotify), md5(random_bytes(5)));

            $this->repository->save($artist);
        }

        $this->flusher->flush();

        $this->client->request('GET', '/api/v1/artists?limit=' . ($limit + 1));
        $this->assertResponseIsSuccessful();
        $body = json_decode($this->client->getResponse()->getContent(), true);

        assertEquals($limit + 1, $body['count']);
        assertEquals($limit, count($body['items']), 'Returned items count must be limited to parameter value');

        $this->client->request('GET', '/api/v1/artists?limit=-1&from=-1');
        $this->assertResponseIsSuccessful();
        $body = json_decode($this->client->getResponse()->getContent(), true);

        assertEquals($limit + 1, $body['count']);
        assertEquals(0, count($body['items']), 'When limit param value is negative, its cast to 0');
    }

    private function generateArtistSchema(Artist $artist): object
    {
        return json_decode(json_encode([
            'type' => 'object',
            'properties' => [
                'id' => [ 'enum' => [ $artist->getId() ] ],
                'name' => [ 'enum' => [ $artist->getName() ] ],
                'source' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => [ 'enum' => [ $artist->getSource()->getName() ] ],
                        'id' => [ 'enum' => [ $artist->getSource()->getId() ] ]
                    ],
                    'required' => ['name', 'id']
                ],
                'genres' => [ 'type' => 'array', 'const' => $artist->getGenres() ],
                'avatar' => [ 'enum' => [ $artist->getAvatarId() ] ]
            ],
            'required' => ['id', 'name', 'avatar', 'source', 'genres']
        ]));
    }

    public static function artistsProvider(): array
    {
        return [
            [new Artist('LOV66', new IdSource('1', Source::Spotify), 'afd11')],
            [new Artist('Baby Melo', new IdSource('2', Source::Spotify), 'dzg1a')],
        ];
    }

    public static function paginationProvider(): array
    {
        return [
            [0, 5],
            [1, 2],
            [3, 1]
        ];
    }
}