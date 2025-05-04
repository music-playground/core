<?php

namespace App\Core\Application\Serializer;

use App\Core\Domain\Entity\Playlist;
use App\Core\Domain\Enum\Source;
use App\Core\Domain\ValueObject\IdSource;
use MusicPlayground\Contract\Application\Playlist\DTO\PlaylistDTO;
use MusicPlayground\Contract\Application\Playlist\DTO\PlaylistSourceDTO;

final readonly class PlaylistSerializer
{
    public function fromDTO(PlaylistDTO $dto, string $creationOperationId): Playlist
    {
        return new Playlist(
            $dto->name,
            $this->sourceFromDTO($dto->source),
            $creationOperationId,
            $dto->coverId,
            $dto->description
        );
    }

    public function sourceFromDTO(PlaylistSourceDTO $dto): IdSource
    {
        return new IdSource($dto->id, Source::from($dto->name));
    }
}