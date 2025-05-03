<?php

namespace App\Core\Infrastructure\Util;

use App\Core\Domain\Entity\Artist;
use App\Core\Domain\Entity\PreviewArtistCast;
use App\Core\Domain\Entity\SimpleArtist;

final readonly class PreviewArtistsFactory
{
    /**
     * @param SimpleArtist[] $all
     * @param Artist[] $existing
     * @return PreviewArtistCast[]
     */
    public function create(array $all, array $existing): array
    {
        $existingMap = [];

        foreach ($existing as $artist) {
            $existingMap[(string)$artist->getSource()] = $artist;
        }

        return array_map(function (SimpleArtist $artist) use ($existingMap) {
            /** @var SimpleArtist|Artist $artist */
            $artist = $existingMap[(string)$artist->getSource()] ?? $artist;

            return new PreviewArtistCast(
                $artist instanceof Artist ? $artist->getId() : null,
                $artist->getName(),
                (string)$artist->getSource(),
                $artist instanceof Artist ? $artist->getAvatarId() : null
            );
        },  $all);
    }
}