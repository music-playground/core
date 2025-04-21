<?php

namespace App\Tests\Core\Integration\Album;

use App\Core\Domain\Entity\Album;
use App\Core\Domain\Repository\Album\AlbumRepositoryInterface;
use App\Core\Domain\Repository\ArtistRepositoryInterface;
use App\Shared\Domain\FlusherInterface;

trait SaveAlbumWithArtistsTrait
{
    private AlbumRepositoryInterface $repository;
    private ArtistRepositoryInterface $artistRepository;
    private FlusherInterface $flusher;

    private function saveAlbumWithArtists(Album $album, array $artists): void
    {
        foreach ($artists as $artist) {
            $this->artistRepository->save($artist);
        }

        $this->flusher->flush();

        foreach ($artists as $artist) {
            $album->addAuthorId($artist->getId());
        }

        $this->repository->save($album);
    }
}