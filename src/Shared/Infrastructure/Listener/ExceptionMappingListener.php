<?php

namespace App\Shared\Infrastructure\Listener;

use App\Core\Domain\Exception\AlbumNotFoundException;
use App\Core\Domain\Exception\ArtistNotFoundException;
use App\Core\Domain\Exception\TrackNotFoundException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[AsEventListener(event: 'kernel.exception', priority: 101)]
final readonly class ExceptionMappingListener
{
    public function __construct(
        private array $mapping = [
            ArtistNotFoundException::class => ['Artist not found', 404, 100],
            AlbumNotFoundException::class => ['Album not found', 404, 200],
            TrackNotFoundException::class => ['Track not found', 404, 300]
        ]
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (isset($this->mapping[$exception::class])) {
            [$message, $status, $code] = $this->mapping[$exception::class];

            $event->setThrowable(new HttpException($status, $message, $exception, code: $code));
        }
    }
}