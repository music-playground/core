<?php

namespace App\Tests\Core\Integration\Artist;

use App\Core\Domain\Entity\Artist;
use App\Core\Domain\Enum\Source;
use App\Core\Domain\Exception\ArtistNotFoundException;
use App\Core\Domain\Repository\ArtistRepositoryInterface;
use App\Core\Domain\ValueObject\ArtistSource;
use App\Shared\Domain\FlusherInterface;
use App\Tests\Shared\SchemaAssertTrait;
use Random\RandomException;
use Swaggest\JsonSchema\Exception;
use Swaggest\JsonSchema\InvalidValue;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ArtistApiTest extends WebTestCase
{
    use SchemaAssertTrait;

    private KernelBrowser $client;
    private ArtistRepositoryInterface $repository;
    private FlusherInterface $flusher;
    private string $imageUrl;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $container = $this->client->getContainer();
        /** @var ArtistRepositoryInterface $repository */
        $repository = $container->get(ArtistRepositoryInterface::class);
        /** @var FlusherInterface $flusher */
        $flusher = $container->get(FlusherInterface::class);
        $imageUrl = $container->getParameter('image-url');

        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->imageUrl = $imageUrl;
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

        $body = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSchema($body, $this->generateArtistSchema($artist, $id));

        $this->repository->delete($id);
        $this->flusher->flush();

        $this->client->request('GET', "/api/v1/artists/$id");

        $this->assertResponseStatusCodeSame(404);

        $body = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSchema($body, ['error' => 'Artist not found', 'code' => 100]);
    }

    /**
     * @throws Exception
     * @throws RandomException
     * @throws InvalidValue
     */
    public function test_get_many_endpoint(int $from, int $limit): void
    {
        $savedArtists = [];
        $schema = [];

        for ($i = 0; $i < $from + $limit + 1; $i++) {
            $savedArtists[] = $this->repository->save(
                new Artist("Artist$i", hash('md5', random_bytes(5)), new ArtistSource($i, Source::Spotify))
            );
        }

        $this->flusher->flush();
        $schema['count'] = $this->repository->count();

        for ($i = $from + $limit; $i >= $from; $i++)  {
            $schema []= $this->generateArtistSchema($savedArtists[$i]);
        }

        $this->client->request('GET', "/api/v1/artists?from=$from&limit=$limit");

        $this->assertResponseIsSuccessful();
        $body = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSchema($body, $schema);
    }

    private function generateArtistSchema(Artist $artist): array
    {
        return [
            'id' => $artist->getId(),
            'name' => $artist->getName(),
            'avatar' => $this->imageUrl . '/' . $artist->getAvatarId(),
            'genres' => $artist->getGenres(),
            'source' => $artist->getSource()
        ];
    }

    public static function artistsProvider(): array
    {
        return [
            [new Artist('LOV66', 'afd11', new ArtistSource('1', Source::Spotify))],
            [new Artist('Baby Melo', 'dzg1a', new ArtistSource('2', Source::Spotify))],
        ];
    }
}