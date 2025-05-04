<?php

namespace App\Shared\Infrastructure\Messenger\Transport;

use InvalidArgumentException;
use MusicPlayground\AmqpTransport\Amqp\AmqpChannelFactory;
use MusicPlayground\AmqpTransport\Amqp\Publisher;
use MusicPlayground\AmqpTransport\Messenger\Factory\AmqpExchangeFactory;
use MusicPlayground\AmqpTransport\Messenger\Transport\AmqpReceiver;
use MusicPlayground\AmqpTransport\Messenger\Transport\AmqpSender;
use MusicPlayground\AmqpTransport\Messenger\Transport\AmqpTransport;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportFactoryInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

final readonly class AmqpTransportFactory implements TransportFactoryInterface
{

    public function createTransport(#[\SensitiveParameter] string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        $credentials = $this->parseCredentials($dsn);

        return new AmqpTransport(
            new AmqpReceiver(new AmqpChannelFactory($credentials)),
            new AmqpSender(new Publisher(), new AmqpExchangeFactory(new AmqpChannelFactory($credentials)), $serializer)
        );
    }

    public function supports(#[\SensitiveParameter] string $dsn, array $options): bool
    {
        return str_starts_with($dsn, 'amqp://') === true;
    }

    private function parseCredentials(string $dsn): array
    {
        $credentials = parse_url($dsn);

        if ($credentials['scheme'] !== 'amqp') {
            throw new InvalidArgumentException('Invalid schema');
        }

        return [
            'host' => $credentials['host'] ?? 'localhost',
            'port' => $credentials['port'] ?? 5672,
            'login' => $credentials['user'] ?? 'guest',
            'password' => $credentials['pass'] ?? 'guest',
        ];
    }
}