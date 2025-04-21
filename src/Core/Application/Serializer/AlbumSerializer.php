<?php

namespace App\Core\Application\Serializer;

use App\Core\Domain\Entity\Album;
use App\Core\Domain\Enum\Source;
use App\Core\Domain\ValueObject\IdSource;
use MusicPlayground\Contract\Application\SongParser\DTO\AlbumDTO;
use MusicPlayground\Contract\Application\SongParser\DTO\AlbumSourceDTO;

final readonly class AlbumSerializer
{
    public function __construct(
        private ArtistSerializer $artistSerializer
    ) {
    }

    public function fromDTO(AlbumDTO $dto): Album
    {
        $album = new Album(
            $dto->name,
            $dto->cover,
            $this->sourceFromDTO($dto->source),
            $dto->genres,
            $dto->releaseDate
        );

        $album->setSimpleArtists($this->artistSerializer->manySimpleFroDTO($dto->artists));

        return $album;
    }

    public function sourceFromDTO(AlbumSourceDTO $dto): IdSource
    {
        return new IdSource($dto->id, Source::from($dto->name));
    }
}