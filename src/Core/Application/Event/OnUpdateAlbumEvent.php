<?php

namespace App\Core\Application\Event;

use App\Core\Domain\Entity\Album;

final class OnUpdateAlbumEvent
{
    public function __construct(public Album $album) {
    }
}