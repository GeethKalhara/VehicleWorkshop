<?php

declare(strict_types=1);

namespace Vwork\Shared\Test\Encoding;

use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use SimdJsonEncoderException;
use Throwable;
use Vwork\Shared\Encoding\Json;
use Vwork\Shared\Exception\VworkError;

final class JsonTest extends TestCase
{
    #[Test]
    public function it_encodes_to_compact_json(): void
    {
        $this->assertSame(
            '{"a":1,"b":[true,null],"c":"x","d":-2.5}',
            Json::encode(['a' => 1, 'b' => [true, null], 'c' => 'x', 'd' => -2.5]),
        );
    }

    #[Test]
    public function unicode_and_slashes_are_emitted_raw_and_quotes_escaped(): void
    {
        $this->assertSame('{"u":"é/\\""}', Json::encode(['u' => 'é/"']));
    }

    #[Test]
    public function nested_data_round_trips_with_types_preserved(): void
    {
        $data = [
            'id' => 42,
            'neg' => PHP_INT_MIN,
            'ratio' => 0.25,
            'name' => "Zoë \u{1F600}\n\t",
            'flags' => [true, false, null],
            'deep' => ['a' => ['b' => ['c' => []]]],
        ];

        $this->assertSame($data, Json::decode(Json::encode($data)));
    }

    #[Test]
    public function an_empty_array_encodes_as_a_list_so_empty_objects_do_not_round_trip(): void
    {
        $this->assertSame('[]', Json::encode([]));
        $this->assertSame([], Json::decode('{}'));
        $this->assertSame('{"m":[]}', Json::encode(Json::decode('{"m":{}}') ?? []));
    }

    #[Test]
    #[TestWith(['1'])]
    #[TestWith(['"text"'])]
    #[TestWith(['null'])]
    #[TestWith(['true'])]
    public function a_non_array_top_level_value_decodes_to_null(string $json): void
    {
        $this->assertNull(Json::decode($json));
    }

    #[Test]
    #[TestWith([''])]
    #[TestWith(['{'])]
    #[TestWith(['{"a":}'])]
    #[TestWith(['[1] x'])]         // trailing garbage
    #[TestWith(["[\"\xff\"]"])]    // invalid UTF-8
    #[TestWith(["{'a':1}"])]       // single quotes
    #[TestWith(['[1,]'])]          // trailing comma
    public function malformed_json_decodes_to_null_instead_of_throwing(string $json): void
    {
        $this->assertNull(Json::decode($json));
    }
}
