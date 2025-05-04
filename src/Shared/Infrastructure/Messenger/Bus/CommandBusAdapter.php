<?php

namespace App\Shared\Infrastructure\Messenger\Bus;

use App\Shared\Application\Interface\CommandBusInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class CommandBusAdapter implements CommandBusInterface
{
    public function __construct(private MessageBusInterface $commandBus)
    {
    }

    /**
     * @throws ExceptionInterface
     */
    public function dispatch(object $command): void
    {
        $this->commandBus->dispatch($command);
    }
}