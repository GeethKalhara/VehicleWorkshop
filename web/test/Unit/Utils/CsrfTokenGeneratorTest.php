<?php

declare(strict_types=1);

namespace Vwork\Web\Test\Unit\Utils;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;
use Vwork\Shared\Encoding\Base64Url;
use Vwork\Web\Utils\CsrfTokenGenerator;
use Vwork\Shared\Exception\VworkError;

final class CsrfTokenGeneratorTest extends TestCase
{
    private const string SECRET = 'q29bvc9tqccb43xZLmmpor2imn7ewqdf2v43x';

    private static function csrf(string $secret = self::SECRET): CsrfTokenGenerator
    {
        return new CsrfTokenGenerator($secret, new Randomizer());
    }

    #[Test]
    #[TestWith([32])]
    #[TestWith([64])]
    public function a_key_of_32_to_64_bytes_is_accepted(int $length): void
    {
        $csrf = self::csrf(str_repeat('k', $length));

        $this->assertTrue($csrf->verify('session', $csrf->create('session')));
    }

    #[Test]
    #[TestWith([0])]
    #[TestWith([31])]
    #[TestWith([65])]
    public function a_key_outside_32_to_64_bytes_is_rejected(int $length): void
    {
        $this->expectException(VworkError::class);
        $this->expectExceptionMessage('token key must be 32-64 bytes');

        self::csrf(str_repeat('k', $length));
    }

    #[Test]
    #[TestWith(['q2f3b9238'])]
    #[TestWith(['nlwiec'])]
    #[TestWith([''])]
    public function a_created_token_verifies_for_its_own_session(string $id): void
    {
        $csrf = self::csrf();

        $this->assertTrue($csrf->verify($id, $csrf->create($id)));
    }

    #[Test]
    public function a_token_verifies_on_another_instance_with_the_same_key(): void
    {
        $this->assertTrue(self::csrf()->verify('session', self::csrf()->create('session')));
    }

    #[Test]
    public function the_same_session_gets_a_different_looking_token_every_time(): void
    {
        $csrf = self::csrf();
        $a = $csrf->create('session');
        $b = $csrf->create('session');

        // masked: never equal, both valid
        $this->assertNotSame($a, $b);
        $this->assertTrue($csrf->verify('session', $a));
        $this->assertTrue($csrf->verify('session', $b));
    }

    #[Test]
    public function the_mask_comes_from_the_injected_randomizer(): void
    {
        $a = new CsrfTokenGenerator(self::SECRET, new Randomizer(new Xoshiro256StarStar(42)));
        $b = new CsrfTokenGenerator(self::SECRET, new Randomizer(new Xoshiro256StarStar(42)));

        $this->assertSame($a->create('session'), $b->create('session'));
    }

    #[Test]
    public function tokens_are_url_safe_base64_without_padding(): void
    {
        // 32-byte mask + 32-byte masked hash = 64 bytes = 86 chars
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]{86}$/', self::csrf()->create('session'));
    }

    #[Test]
    #[TestWith(['q2f3b9238', 'nlwiec'])]
    #[TestWith(['session', 'session '])]
    #[TestWith(['session', 'Session'])]
    #[TestWith(['session', ''])]
    #[TestWith(['a', "a\0"])]
    public function a_token_does_not_verify_for_another_session(string $owner, string $other): void
    {
        $csrf = self::csrf();

        $this->assertFalse($csrf->verify($other, $csrf->create($owner)));
    }

    #[Test]
    public function a_token_does_not_verify_under_another_secret(): void
    {
        $token = self::csrf()->create('session');

        $this->assertFalse(self::csrf('another-secret-entirely-0123456789')->verify('session', $token));
    }

    #[Test]
    #[TestWith([0])]  // inside the mask
    #[TestWith([50])] // inside the masked hash
    #[TestWith([84])] // second-to-last char; the last (85) also carries 4 unused padding bits
    public function a_tampered_token_does_not_verify(int $at): void
    {
        $csrf = self::csrf();
        $token = $csrf->create('session');
        $token[$at] = $token[$at] === 'A' ? 'B' : 'A';

        $this->assertFalse($csrf->verify('session', $token));
    }

    #[Test]
    #[TestWith([''])]
    #[TestWith(['not base64 at all!'])]
    #[TestWith(['c2hvcnQ'])] // valid base64, too short
    public function malformed_tokens_are_rejected_not_thrown(string $token): void
    {
        $this->assertFalse(self::csrf()->verify('session', $token));
    }

    #[Test]
    public function a_token_with_extra_bytes_is_rejected(): void
    {
        $csrf = self::csrf();

        $this->assertFalse($csrf->verify('session', $csrf->create('session') . 'AAAA'));
    }

    #[Test]
    #[TestWith([63])]
    #[TestWith([65])]
    #[TestWith([96])]
    public function a_token_of_the_wrong_decoded_length_is_rejected(int $length): void
    {
        $csrf = self::csrf();
        $bytes = Base64Url::decode($csrf->create('session'));
        // keep a valid 64-byte prefix so only the length is wrong
        $resized = substr(str_pad($bytes ?? '', $length, $bytes ?? ''), 0, $length);

        $this->assertFalse($csrf->verify('session', Base64Url::encode($resized)));
    }
}
