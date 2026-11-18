<?php

declare(strict_types=1);

namespace Vwork\Shared\Encoding;

use JsonException;
use SimdJsonException;
use Throwable;
use Vwork\Shared\Exception\VworkError;

// requires https://github.com/awesomized/simdjson-plus-php-ext
final readonly class Json
{
    /**
     * Note: an empty array always encodes as `[]`, never `{}`.
     *
     * @param array<int|string, mixed> $payload
     */
    public static function encode(array $payload): string
    {
        return json_encode(
            $payload,
            JSON_THROW_ON_ERROR |
            JSON_UNESCAPED_SLASHES |
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_LINE_TERMINATORS |
            JSON_PRESERVE_ZERO_FRACTION,
        );

    }

    /**
     * @return array<int|string, mixed>|null
     */
    public static function decode(string $payload): ?array
    {
        try {
            $result = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
            return is_array($result) ? $result : null;
        } catch (JsonException) {
            return null;
        }
    }
}
