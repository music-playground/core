<?php

namespace App\Core\Application\Updater;

use App\Core\Application\Serializer\AlbumSerializer;
use App\Core\Application\Serializer\ArtistSerializer;
use App\Core\Domain\Entity\Album;
use App\Core\Domain\Repository\Album\AlbumRepositoryInterface;
use App\Shared\Domain\FlusherInterface;
use MusicPlayground\Contract\Application\SongParser\DTO\AlbumDTO;

final readonly class AlbumUpdater
{
    public function __construct(
        private AlbumRepositoryInterface $repository,
        private AlbumSerializer $serializer,
        private ArtistSerializer $artistSerializer,
        private FlusherInterface $flusher
    ) {
    }

    public function fromDto(AlbumDTO $dto): Album
    {
        $album = $this->repository->findBySource(
            $this->serializer->sourceFromDTO($dto->source)
        );

        if ($album !== null) {
            $album->setName($dto->name);
            $album->setCoverId($dto->cover);
            $album->setGenres($dto->genres);
            $album->setSimpleArtists($this->artistSerializer->manySimpleFroDTO($dto->artists));
            $album->setReleaseDate($dto->releaseDate);
        } else {
            $album = $this->serializer->fromDTO($dto);

            $this->repository->save($album);
        }

        $this->flusher->flush();

        return $album;
    }
}