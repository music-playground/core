<?php

namespace App\Shared\Infrastructure\Doctrine;

use App\Shared\Domain\FlusherInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Throwable;

final readonly class MongoFlusher implements FlusherInterface
{
    public function __construct(
        private DocumentManager $dm,
        private bool $useTransaction = false
    ) {
    }

    /**
     * @throws MongoDBException
     * @throws Throwable
     */
    public function flush(): void
    {
        $this->dm->flush(['withTransaction' => $this->useTransaction]);
    }
}