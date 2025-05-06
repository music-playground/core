<?php

namespace App\Core\Domain\Entity;

use App\Core\Domain\ValueObject\IdSource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Playlist
{
    private ?string $id = null;
    private Collection $tracks;

    public function __construct(
        private string $name,
        private readonly IdSource $source,
        private readonly string $creationOperationId,
        private ?string $coverId,
        private ?string $description,
    ) {
        $this->tracks = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSource(): IdSource
    {
        return $this->source;
    }

    public function getCoverId(): ?string
    {
        return $this->coverId;
    }

    public function setCoverId(?string $coverId): void
    {
        $this->coverId = $coverId;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getCreationOperationId(): string
    {
        return $this->creationOperationId;
    }

    public function addTrack(string $trackId): void
    {
        if ($this->tracks->contains($trackId) === false) {
            $this->tracks->add($trackId);
        }
    }
}