<?php

namespace App\Core\Infrastructure\Util;

use App\Core\Domain\Entity\ArtistShortCast;
use App\Core\Domain\ValueObject\ArtistAvatar;

class ShortArtistsFactory
{
    /** @return ArtistShortCast[] */
    public function create(array $all, array $existing): array
    {
        $existingMap = [];

        foreach ($existing as $artist) {
            $existingMap[$artist['source']] = $artist;
        }

        return array_map(function (array $artist) use ($existingMap) {
            $artist = $existingMap[$artist['source']] ?? $artist;
            return new ArtistShortCast(
                $artist['name'],
                $artist['_id'] ?? null,
                isset($artist['avatarId']) ? new ArtistAvatar($artist['avatarId']) : null);
        },  $all);
    }
}