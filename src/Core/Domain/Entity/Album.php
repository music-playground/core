<?php

namespace App\Core\Domain\Entity;

use App\Core\Domain\Trait\SimpleArtistsTrait;
use App\Core\Domain\ValueObject\IdSource;
use DateTimeImmutable;

class Album
{
    use SimpleArtistsTrait {
        init as simpleArtistsInit;
    }

    private ?string $id = null;

    public function __construct(
        private string $name,
        private string $coverId,
        private readonly IdSource $source,
        private array $genres,
        private DateTimeImmutable $releaseDate
    ) {
        $this->simpleArtistsInit();
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

    public function setReleaseDate(DateTimeImmutable $releaseDate): void
    {
        $this->releaseDate = $releaseDate;
    }
}