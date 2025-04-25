<?php

namespace App\Core\Application\Handler;

use App\Core\Application\Serializer\ArtistSerializer;
use App\Core\Application\Serializer\TrackSerializer;
use App\Core\Domain\Entity\Track;
use App\Core\Domain\Repository\Track\TrackRepositoryInterface;
use App\Shared\Domain\FlusherInterface;
use MusicPlayground\Contract\Application\SongParser\Command\UpdateTrackCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateTrackCommandHandler
{
    public function __construct(
        private TrackRepositoryInterface $repository,
        private TrackSerializer $serializer,
        private ArtistSerializer $artistSerializer,
        private FlusherInterface $flusher
    ) {
    }

    public function __invoke(UpdateTrackCommand $command): void
    {
        $trackData = $command->dto;
        $track = $this->repository->findBySource($this->serializer->sourceFromDTO($trackData->source));

        if ($track === null) {
            $track = $this->serializer->fromDTO($command->dto);

            $this->repository->save($track);
        } else {
            $this->updateTrack($track, $command);
        }

        $this->flusher->flush();

        throw new \Exception();
    }

    private function updateTrack(Track $track, UpdateTrackCommand $command): void
    {
        $dto = $command->dto;

        $track->setName($dto->name);
        $track->setFileId($dto->fileId);
        $track->setSimpleArtists($this->artistSerializer->manySimpleFroDTO($dto->simpleArtists));
    }
}