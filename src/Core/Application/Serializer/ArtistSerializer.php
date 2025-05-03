<?php

namespace App\Core\Application\Serializer;

use App\Core\Domain\Entity\Artist;
use App\Core\Domain\Entity\PreviewArtistCast;
use App\Core\Domain\Entity\SimpleArtist;
use App\Core\Domain\Enum\Source;
use App\Core\Domain\ValueObject\IdSource;
use MusicPlayground\Contract\Application\SongParser\DTO\ArtistDTO;
use MusicPlayground\Contract\Application\SongParser\DTO\ArtistSourceDTO;
use MusicPlayground\Contract\Application\SongParser\DTO\PreviewArtistDTO;
use MusicPlayground\Contract\Application\SongParser\DTO\SimpleArtistDTO;

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

    public function toDto(Artist $artist): ArtistDTO
    {
        return new ArtistDTO(
            $artist->getName(),
            $artist->getAvatarId(),
            $this->sourceToDTO($artist->getSource()),
            $artist->getGenres()
        );
    }

    public function sourceFromDTO(ArtistSourceDTO $dto): IdSource
    {
        return new IdSource($dto->id, Source::from($dto->name));
    }

    public function sourceToDTO(IdSource $source): ArtistSourceDTO
    {
        return new ArtistSourceDTO($source->getName(), $source->getId());
    }

    public function simpleFromDTO(SimpleArtistDTO $dto): SimpleArtist
    {
        return new SimpleArtist($dto->name, new IdSource($dto->source->id, Source::from($dto->source->name)));
    }

    /** @var SimpleArtistDTO[] $dtos */
    public function manySimpleFroDTO(array $dtos): array
    {
        return array_map(fn (SimpleArtistDTO $dto) => $this->simpleFromDTO($dto), $dtos);
    }

    public function previewCastToDTO(PreviewArtistCast $cast): PreviewArtistDTO
    {
        return new PreviewArtistDTO(
            $cast->id,
            $cast->name,
            $this->sourceToDTO(IdSource::from($cast->source)),
            $cast->avatarId
        );
    }

    /**
     * @param PreviewArtistCast[] $casts
     * @return PreviewArtistDTO[]
     */
    public function manyPreviewCastToDTO(array $casts): array
    {
        return array_map(fn (PreviewArtistCast $cast) => $this->previewCastToDTO($cast), $casts);
    }
}