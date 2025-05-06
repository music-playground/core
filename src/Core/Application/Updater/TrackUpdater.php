<?php

namespace App\Core\Application\Updater;

use App\Core\Application\Serializer\ArtistSerializer;
use App\Core\Application\Serializer\TrackSerializer;
use App\Core\Domain\Entity\Track;
use App\Core\Domain\Repository\Track\TrackRepositoryInterface;
use App\Shared\Domain\FlusherInterface;
use MusicPlayground\Contract\Application\SongParser\DTO\TrackDTO;

final readonly class TrackUpdater
{
    public function __construct(
        private TrackRepositoryInterface $repository,
        private TrackSerializer $serializer,
        private ArtistSerializer $artistSerializer,
        private FlusherInterface $flusher
    ) {
    }

    public function fromDTO(TrackDTO $dto): Track
    {
        $track = $this->repository->findBySource($this->serializer->sourceFromDTO($dto->source));

        if ($track === null) {
            $track = $this->serializer->fromDTO($dto);

            $this->repository->save($track);
        } else {
            $track->setName($dto->name);
            $track->setFileId($dto->fileId);
            $track->setSimpleArtists($this->artistSerializer->manySimpleFroDTO($dto->simpleArtists));
        }

        $this->flusher->flush();

        return $track;
    }
}