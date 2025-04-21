<?php

namespace App\Core\Domain\Entity;

use App\Core\Domain\Trait\SimpleArtistsTrait;
use App\Core\Domain\ValueObject\IdSource;

class Track
{
    use SimpleArtistsTrait;

    private ?string $id = null;
    
    public function __construct(
        private string $name,
        private string $fileId,
        private readonly string $albumId,
        private readonly IdSource $source
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

    public function getFileId(): string
    {
        return $this->fileId;
    }

    public function getSource(): IdSource
    {
        return $this->source;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setFileId(string $fileId): void
    {
        $this->fileId = $fileId;
    }
}