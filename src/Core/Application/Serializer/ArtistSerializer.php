<?php

namespace App\Core\Application\Serializer;

use App\Core\Domain\Entity\Artist;
use App\Core\Domain\Enum\Source;
use App\Core\Domain\ValueObject\IdSource;
use MusicPlayground\Contract\Application\SongParser\DTO\ArtistDTO;
use MusicPlayground\Contract\Application\SongParser\DTO\ArtistSourceDTO;

class ArtistSerializer
{
    public function fromDTO(ArtistDTO $dto): Artist
    {
        return new Artist(
            $dto->name,
            $this->sourceFromDTO($dto->source),
            $dto->avatarId
        );
    }

    public function sourceFromDTO(ArtistSourceDTO $dto): IdSource
    {
        return new IdSource($dto->id, Source::from($dto->name));
    }
}