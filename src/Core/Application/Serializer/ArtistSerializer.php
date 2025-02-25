<?php

namespace App\Core\Application\Serializer;

use App\Core\Domain\Entity\Artist;
use App\Core\Domain\Enum\Source;
use App\Core\Domain\ValueObject\ArtistSource;
use MusicPlayground\Contract\Application\SongParser\DTO\ArtistDTO;
use MusicPlayground\Contract\Application\SongParser\DTO\ArtistSourceDTO;

class ArtistSerializer
{
    public function fromDTO(ArtistDTO $dto): Artist
    {
        return new Artist(
            $dto->name,
            $dto->avatarId,
            $this->sourceFromDTO($dto->source)
        );
    }

    public function sourceFromDTO(ArtistSourceDTO $dto): ArtistSource
    {
        return new ArtistSource($dto->id, Source::from($dto->name));
    }
}