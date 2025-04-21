<?php

namespace App\Core\Application\Serializer;

use App\Core\Domain\Entity\Track;
use App\Core\Domain\Enum\Source;
use App\Core\Domain\ValueObject\IdSource;
use MusicPlayground\Contract\Application\SongParser\DTO\TrackDTO;
use MusicPlayground\Contract\Application\SongParser\DTO\TrackSourceDTO;

final readonly class TrackSerializer
{
    public function __construct(
      private ArtistSerializer $artistSerializer
    ) {
    }

    public function fromDTO(TrackDTO $dto): Track
    {
        $track = new Track(
            $dto->name,
            $dto->fileId,
            $dto->albumId,
            $this->sourceFromDTO($dto->source)
        );

        $track->setSimpleArtists($this->artistSerializer->manySimpleFroDTO($dto->simpleArtists));

        return $track;
    }

    public function sourceFromDTO(TrackSourceDTO $dto): IdSource
    {
        return new IdSource($dto->id, Source::from($dto->name));
    }
}