<?php

namespace App\Core\Application\Handler;

use App\Core\Application\Serializer\AlbumSerializer;
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
        private CommandBusInterface $bus
    ) {
    }

    public function __invoke(UpdateAlbumCommand $command): void
    {
        $data = $command->dto;
        $album = $this->repository->findBySource(
            $this->serializer->sourceFromDTO($data->source)
        );

        if ($album !== null) {
            $this->updateAlbum($album, $command);
        } else {
            $album = $this->serializer->fromDTO($command->dto);
            $album->addAuthorId($command->artistId);

            $this->repository->save($album);
        }

        $this->flusher->flush();
        $this->bus->dispatch(new OnUpdateAlbumCommand($command->dto->source, $command->dto->artist->source, []));
    }

    private function updateAlbum(Album $album, UpdateAlbumCommand $command): void
    {
        $dto = $command->dto;

        $album->setName($dto->name);
        $album->setCoverId($dto->cover);
        $album->setGenres($dto->genres);
        $album->addAuthorId($command->artistId);
        $album->setReleaseDate($dto->releaseDate);
    }
}