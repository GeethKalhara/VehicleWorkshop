<?php

declare(strict_types=1);

namespace Vwork\Shared\Test\Encoding;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Vwork\Shared\Encoding\Base64Url;

final class Base64UrlTest extends TestCase
{
    #[Test]
    // RFC 4648 §10 vectors, padding stripped
    #[TestWith(['', ''])]
    #[TestWith(['f', 'Zg'])]
    #[TestWith(['fo', 'Zm8'])]
    #[TestWith(['foo', 'Zm9v'])]
    #[TestWith(['foob', 'Zm9vYg'])]
    #[TestWith(['fooba', 'Zm9vYmE'])]
    #[TestWith(['foobar', 'Zm9vYmFy'])]
    // the two characters that differ from standard base64
    #[TestWith(["\xfb\xef\xbe", '----'])]
    #[TestWith(["\xff\xff\xff", '____'])]
    #[TestWith(["\xfb\xff", '-_8'])]
    public function it_encodes_and_decodes_known_vectors(string $binary, string $encoded): void
    {
        $this->assertSame($encoded, Base64Url::encode($binary));
        $this->assertSame($binary, Base64Url::decode($encoded));
    }

    #[Test]
    public function every_byte_value_round_trips(): void
    {
        $all = implode('', array_map(chr(...), range(0, 255)));

        $this->assertSame($all, Base64Url::decode(Base64Url::encode($all)));
    }

    #[Test]
    #[TestWith([1])]
    #[TestWith([2])]
    #[TestWith([3])]
    #[TestWith([31])]
    #[TestWith([32])]
    #[TestWith([64])]
    public function output_is_unpadded_url_safe_and_of_minimal_length(int $length): void
    {
        $encoded = Base64Url::encode(random_bytes(max(1, $length)));

        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]*$/', $encoded);
        $this->assertSame((int) ceil($length * 4 / 3), strlen($encoded));
    }

    #[Test]
    #[TestWith(['Zg=='])]     // padding is not part of this variant
    #[TestWith(['+/8'])]      // standard-alphabet characters
    #[TestWith(['Zh'])]       // non-zero trailing bits: a second spelling of "f" must not exist
    #[TestWith(['Z'])]        // a lone char can't hold a whole byte
    #[TestWith([' Zg'])]      // whitespace is not skipped
    #[TestWith(["Zg\n"])]
    #[TestWith(['not base64 at all!'])]
    public function malformed_input_returns_null_instead_of_throwing(string $input): void
    {
        $this->assertNull(Base64Url::decode($input));
    }
}
