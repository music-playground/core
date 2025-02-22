<?php

declare(strict_types = 1);

namespace App\Shared\Infrastructure\Messenger\Middleware;

use InvalidArgumentException;
use MusicPlayground\Contract\Application\Command\CommandWithIdInterface;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

final readonly class AmqpWithIdMiddleware implements MiddlewareInterface
{
    public function __construct(private array $attributes)
    {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();

        if ($message instanceof CommandWithIdInterface === false) {
            throw new InvalidArgumentException('Message is not a command with id');
        }

        $attributes = $this->attributes[$message::class] ?? [];

        return $stack->next()->handle(
            $envelope->with(
                new AmqpStamp($message->getId(), attributes: $attributes)
            ),
            $stack
        );
    }
}