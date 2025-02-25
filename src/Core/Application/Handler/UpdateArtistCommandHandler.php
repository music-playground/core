<?php

namespace App\Core\Application\Handler;

use App\Core\Application\Serializer\ArtistSerializer;
use App\Core\Domain\Entity\Artist;
use App\Core\Domain\Repository\AlbumRepositoryInterface;
use App\Core\Domain\Repository\ArtistRepositoryInterface;
use App\Shared\Application\Interface\CommandBusInterface;
use App\Shared\Application\ObjectStorage\ObjectStorageInterface;
use App\Shared\Domain\FlusherInterface;
use MusicPlayground\Contract\Application\SongParser\Command\OnUpdateArtistCommand;
use MusicPlayground\Contract\Application\SongParser\Command\UpdateArtistCommand;
use MusicPlayground\Contract\Application\SongParser\DTO\ArtistDTO;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateArtistCommandHandler
{
    public function __construct(
        private ArtistRepositoryInterface $repository,
        private AlbumRepositoryInterface $albumRepository,
        private FlusherInterface $flusher,
        private ArtistSerializer $serializer,
        private ObjectStorageInterface $avatarStorage,
        private CommandBusInterface $bus
    ) {
    }

    public function __invoke(UpdateArtistCommand $command): void
    {
        $artistData = $command->dto;
        $artist = $this->repository->findBySource(
            $this->serializer->sourceFromDTO($artistData->source)
        );

        if ($artist !== null) {
            $this->updateArtist($artist, $artistData);
        } else {
            $artist = $this->serializer->fromDTO($artistData);
        }

        $this->flusher->flush();

        $containsAlbums = $this->albumRepository->findIdsByAuthor($artist->getId());
        $this->bus->dispatch(new OnUpdateArtistCommand($artistData->source, $artist->getId(), $containsAlbums));
    }

    private function updateArtist(Artist $artist, ArtistDTO $data): void
    {
        $artist->setName($data->name);
        $artist->setGenres($data->genres);

        if ($data->avatarId !== $artist->getAvatarId()) {
            $this->avatarStorage->delete($artist->getAvatarId());
            $artist->setAvatarId($data->avatarId);
        }
    }
}