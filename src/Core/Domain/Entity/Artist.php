<?php

namespace App\Core\Domain\Entity;


use App\Core\Domain\ValueObject\ArtistSource;

class Artist
{
    private ?string $id = null;
    /** @var array<string> */
    private array $genres = [];

    public function __construct(
        private string $name,
        private string $avatarId,
        private readonly ArtistSource $source
    ) {
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAvatarId(): string
    {
        return $this->avatarId;
    }

    public function getSource(): ArtistSource
    {
        return $this->source;
    }

    public function getGenres(): array
    {
        return $this->genres;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setAvatarUrl(string $avatarId): void
    {
        $this->avatarId = $avatarId;
    }

    public function tryAddGenre(string $genre): void
    {
        if (in_array($genre, $this->genres, true) === false) {
            $this->genres[] = $genre;
        }
    }
}