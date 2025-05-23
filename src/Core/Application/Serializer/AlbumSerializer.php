<?php

namespace App\Core\Application\Serializer;

use App\Core\Domain\Entity\Album;
use App\Core\Domain\Entity\ArtistCast;
use App\Core\Domain\Entity\ArtistShortCast;
use App\Core\Domain\Entity\PreviewArtistCast;
use App\Core\Domain\Enum\Source;
use App\Core\Domain\ValueObject\IdSource;
use MusicPlayground\Contract\Application\SongParser\DTO\AlbumDTO;
use MusicPlayground\Contract\Application\SongParser\DTO\AlbumSourceDTO;
use MusicPlayground\Contract\Application\SongParser\DTO\FullAlbumDTO;
use MusicPlayground\Contract\Application\SongParser\DTO\PreviewArtistDTO;

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

    /**
     * @param PreviewArtistCast[] $artists
     */
    public function toFullDto(Album $album, array $artists): FullAlbumDTO
    {
        return new FullAlbumDTO(
            $album->getId(),
            $album->getName(),
            $album->getGenres(),
            $album->getCoverId(),
            $this->artistSerializer->manyPreviewCastToDTO($artists)
        );
    }

    public function sourceFromDTO(AlbumSourceDTO $dto): IdSource
    {
        return new IdSource($dto->id, Source::from($dto->name));
    }

    public function sourceToDTO(IdSource $source): AlbumSourceDTO
    {
        return new AlbumSourceDTO($source->getName(), $source->getId());
    }
}