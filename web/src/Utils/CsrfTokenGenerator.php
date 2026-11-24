<?php

declare(strict_types=1);

namespace Vwork\Web\Utils;

use Random\Randomizer;
use Vwork\Shared\Encoding\Base64Url;
use Vwork\Shared\Exception\VworkError;
use Vwork\Web\Utils\IUtility;

/**
 * Stateless CSRF tokens: keyed BLAKE2b of the session id, XOR-masked with fresh
 * random bytes on every create() so tokens are never repeated on the wire.
 * Wire format: base64url(mask ‖ (mask ⊕ hash)), 64 bytes → 86 chars.
 */
// requires sodium extension
final readonly class CsrfTokenGenerator implements IUtility
{
    private const int SIZE = 32;
    private const string PREFIX = "csrf\0";

    public function __construct(
        private string $secret,
        private Randomizer $rand,
    ) {
        $length = strlen($secret);
        if ($length < SODIUM_CRYPTO_GENERICHASH_KEYBYTES || $length > SODIUM_CRYPTO_GENERICHASH_KEYBYTES_MAX) {
            throw new VworkError('token key must be 32-64 bytes');
        }
    }

    public function create(string $id): string
    {
        return Base64Url::encode($this->mask($this->hash($id)));
    }

    public function verify(string $id, string $token): bool
    {
        $bytes = Base64Url::decode($token);
        // the length check matters: XOR truncates to the shorter operand, so without it
        // a valid token with bytes appended would still unmask to the correct hash
        if ($bytes === null || strlen($bytes) !== 2 * self::SIZE) {
            return false;
        }

        return hash_equals($this->hash($id), $this->unmask($bytes));
    }

    private function hash(string $id): string
    {
        return sodium_crypto_generichash(self::PREFIX . $id, $this->secret, self::SIZE);
    }

    private function mask(string $hash): string
    {
        $mask = $this->rand->getBytes(self::SIZE);
        return $mask . ($mask ^ $hash);
    }

    private function unmask(string $bytes): string
    {
        return substr($bytes, 0, self::SIZE) ^ substr($bytes, self::SIZE);
    }
}
