<?php

namespace App\Core\Domain\Trait;

use App\Core\Domain\Entity\SimpleArtist;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use InvalidArgumentException;

trait SimpleArtistsTrait
{
    /** @var Collection<SimpleArtist> */
    private Collection $artists;

    private function init(): void
    {
        $this->artists = new ArrayCollection();
    }

    /** @var SimpleArtist[] $artists */
    public function setSimpleArtists(array $artists): void
    {
        $this->artists = new ArrayCollection($artists);
        $ids = array_map(fn (SimpleArtist $artist) => $artist->getSource()->getId(), $artists);

        if (array_unique($ids) !== $ids) {
            throw new InvalidArgumentException('Duplicate artist found');
        }

        foreach ($artists as $artist) {
            $this->artists->add($artist);
        }
    }

    /**
     * @return SimpleArtist[]
     */
    public function getSimpleArtists(): array
    {
        return $this->artists->toArray();
    }
}