<?php

namespace App\Core\Domain\Trait;

use App\Core\Domain\Entity\SimpleArtist;
use InvalidArgumentException;

trait SimpleArtistsTrait
{
    /** @var SimpleArtist[] */
    private $artists = [];

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
}