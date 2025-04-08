<?php

namespace App\Core\Application\Handler;

use App\Core\Application\Serializer\AlbumSerializer;
use App\Core\Application\Serializer\ArtistSerializer;
use App\Core\Domain\Entity\Album;
use App\Core\Domain\Repository\AlbumRepositoryInterface;
use App\Shared\Application\Interface\CommandBusInterface;
use App\Shared\Domain\FlusherInterface;
use MusicPlayground\Contract\Application\SongParser\Command\OnUpdateAlbumCommand;
use MusicPlayground\Contract\Application\SongParser\Command\UpdateAlbumCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateAlbumCommandHandler
{
    public function __construct(
        private AlbumRepositoryInterface $repository,
        private FlusherInterface $flusher,
        private AlbumSerializer $serializer,
        private CommandBusInterface $bus,
        private ArtistSerializer $artistSerializer
    ) {
    }

    public function __invoke(UpdateAlbumCommand $command): void
    {
        $albumData = $command->dto;
        $album = $this->repository->findBySource(
            $this->serializer->sourceFromDTO($albumData->source)
        );

        if ($album !== null) {
            $this->updateAlbum($album, $command);
        } else {
            $album = $this->serializer->fromDTO($albumData);

            $this->repository->save($album);
        }

        $this->flusher->flush();
        $this->bus->dispatch(new OnUpdateAlbumCommand($albumData->source, []));
    }

    private function updateAlbum(Album $album, UpdateAlbumCommand $command): void
    {
        $albumData = $command->dto;

        $album->setName($albumData->name);
        $album->setCoverId($albumData->cover);
        $album->setGenres($albumData->genres);
        $album->setSimpleArtists($this->artistSerializer->manySimpleFroDTO($albumData->artists));
        $album->setReleaseDate($albumData->releaseDate);
    }
}