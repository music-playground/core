<?php

namespace App\Tests\Core\Integration\Cleaner;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Throwable;

final class MongoCleaner implements CleanerInterface
{
    private array $entities = [];

    public function __construct(private readonly DocumentManager $dm)
    {
    }

    /**
     * @throws MongoDBException
     * @throws Throwable
     */
    public function clean(array $classes): void
    {
        $this->dm->clear();

        foreach ($classes as $class) {
            $this->dm->getDocumentCollection($class)->deleteMany([]);
        }

        $this->dm->flush();
    }
}