<?php

namespace App\Core\Domain\Entity;

use App\Core\Domain\ValueObject\IdSource;
use DateTimeImmutable;
use InvalidArgumentException;

class Album
{
    private ?string $id = null;
    /** @var SimpleArtist[] */
    private $artists = [];

    public function __construct(
        private string $name,
        private string $coverId,
        private readonly IdSource $source,
        private array $genres,
        /** @var SimpleArtist[] */
        private DateTimeImmutable $releaseDate
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCoverId(): string
    {
        return $this->coverId;
    }

    public function getSource(): IdSource
    {
        return $this->source;
    }

    public function getGenres(): array
    {
        return $this->genres;
    }

    public function getReleaseDate(): DateTimeImmutable
    {
        return $this->releaseDate;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setCoverId(string $coverId): void
    {
        $this->coverId = $coverId;
    }

    public function setGenres(array $genres): void
    {
        $this->genres = $genres;
    }

    /** @var SimpleArtist[] $artists */
    public function setSimpleArtists(array $artists): void
    {
        $this->artists = [];
        $ids = array_map(fn (SimpleArtist $artist) => $artist->getSource()->getId(), $artists);

        if (array_unique($ids) !== $ids) {
            throw new InvalidArgumentException('Duplicate artist found');
        }

        foreach ($artists as $artist) {
            $this->artists []= $artist;
        }
    }

    public function setReleaseDate(DateTimeImmutable $releaseDate): void
    {
        $this->releaseDate = $releaseDate;
    }
}