<?php

namespace App\Core\Application\Handler;

use App\Core\Application\Serializer\ArtistSerializer;
use App\Core\Domain\Repository\Artist\ArtistRepositoryInterface;
use App\Shared\Domain\FlusherInterface;
use MusicPlayground\Contract\Application\SongParser\Command\UpdateArtistCommand;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateArtistCommandHandler
{
    public function __construct(
        private ArtistRepositoryInterface $repository,
        private FlusherInterface $flusher,
        private ArtistSerializer $serializer
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
    }
}