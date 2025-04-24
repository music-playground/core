<?php

namespace App\Core\Application\Handler;

use App\Core\Application\Serializer\ArtistSerializer;
use App\Core\Domain\Repository\Album\AlbumRepositoryInterface;
use App\Core\Domain\Repository\Artist\ArtistRepositoryInterface;
use App\Shared\Application\Interface\CommandBusInterface;
use App\Shared\Domain\FlusherInterface;
use MusicPlayground\Contract\Application\SongParser\Command\OnUpdateArtistCommand;
use MusicPlayground\Contract\Application\SongParser\Command\UpdateArtistCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateArtistCommandHandler
{
    public function __construct(
        private ArtistRepositoryInterface $repository,
        private AlbumRepositoryInterface $albumRepository,
        private FlusherInterface $flusher,
        private ArtistSerializer $serializer,
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
            $artist->setName($artistData->name);
            $artist->setGenres($artistData->genres);
            $artist->setAvatarId($artistData->avatarId);
        } else {
            $artist = $this->serializer->fromDTO($artistData);

            $this->repository->save($artist);
        }

        $this->flusher->flush();

        $containsAlbums = $this->albumRepository->findIdsByAuthor($artist->getId());
        $this->bus->dispatch(new OnUpdateArtistCommand($artistData->source, $containsAlbums));
    }
}