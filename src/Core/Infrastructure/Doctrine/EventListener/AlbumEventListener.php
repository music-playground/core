<?php

namespace App\Core\Infrastructure\Doctrine\EventListener;

use App\Core\Domain\Entity\Album;
use Doctrine\Bundle\MongoDBBundle\Attribute\AsDocumentListener;
use Doctrine\Common\Collections\Collection;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use ReflectionClass;

#[AsDocumentListener('postLoad')]
class AlbumEventListener
{
    public function postLoad(LifecycleEventArgs $args): void
    {
        $album = $args->getObject();

        if ($album instanceof Album === false) return;

        $reflection = new ReflectionClass($album);

        $artistProperty = $reflection->getProperty('artists');
        $value = $artistProperty->getValue($album);

        if ($value instanceof Collection === false) return;

        $artistProperty->setValue($album, $value->toArray());
    }
}