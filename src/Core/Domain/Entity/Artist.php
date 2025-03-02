<?php

namespace App\Core\Domain\Entity;


use App\Core\Domain\ValueObject\IdSource;

class Artist
{
    private ?string $id = null;
    /** @var array<string> */
    private array $genres = [];

    public function __construct(
        private string $name,
        private string $avatarId,
        private readonly IdSource $source
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

    public function getSource(): IdSource
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

    public function setAvatarId(string $avatarId): void
    {
        $this->avatarId = $avatarId;
    }

    public function setGenres(array $genres): void
    {
        $this->genres = $genres;
    }
}