<?php

namespace App\Shared\Infrastructure\Listener;

use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

#[When('prod')]
#[When('test')]
#[AsEventListener(event: 'kernel.exception', priority: 100)]
class HttpExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $httpException = $exception instanceof HttpExceptionInterface ? $exception
            : new HttpException(500, 'Internal server error', $exception);

        $event->setResponse(new JsonResponse(
            array_filter(['error' => $httpException->getMessage(), 'code' => $httpException->getCode()]),
            $httpException->getStatusCode(),
            ['Content-Type' => 'application/problem+json']
        ));
    }
}