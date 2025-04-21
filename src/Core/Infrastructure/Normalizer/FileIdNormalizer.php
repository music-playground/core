<?php

namespace App\Core\Infrastructure\Normalizer;

use App\Core\Domain\ValueObject\AlbumCover;
use App\Core\Domain\ValueObject\ArtistAvatar;
use App\Core\Domain\ValueObject\Audio;
use App\Core\Domain\ValueObject\FileId;
use InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class FileIdNormalizer implements NormalizerInterface
{
    public const DOMAIN_MAPPING = [
        ArtistAvatar::class => 'artist-avatar',
        AlbumCover::class => 'album-cover',
        Audio::class => 'track'
    ];

    public function __construct(private string $host) {
    }

    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $domain = $this::DOMAIN_MAPPING[$data::class] ?? null;

        if ($domain === null) {
            throw new InvalidArgumentException('This domain has no mapping');
        }

        return "$this->host/$domain/$data";
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof FileId;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [FileId::class => true];
    }
}