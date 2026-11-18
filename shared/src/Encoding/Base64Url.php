<?php

declare(strict_types=1);

namespace Vwork\Shared\Encoding;

use SodiumException;

// requires sodium extension
final readonly class Base64Url
{
    public static function encode(string $payload): string
    {
        return sodium_bin2base64($payload, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
    }

    public static function decode(string $payload): string|null
    {
        try {
            return sodium_base642bin($payload, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
        } catch (SodiumException) {
            return null;
        }
    }
}
