<?php

declare(strict_types=1);

namespace JohannesClimacus\ArtisanFactory\Tests\Unit\Input;

use InvalidArgumentException;
use JohannesClimacus\ArtisanFactory\Input\AttributeParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AttributeParserTest extends TestCase
{
    public function test_it_parses_attribute_options(): void
    {
        $attributes = (new AttributeParser)->parse([
            'name=a=b',
            'email_verified_at=null',
            'active=true',
            'blocked=false'
        ]);

        $this->assertSame([
            'name' => 'a=b',
            'email_verified_at' => null,
            'active' => true,
            'blocked' => false
        ], $attributes);
    }

    #[DataProvider('invalidOptions')]
    public function test_it_rejects_invalid_attribute_options(
        array $options,
        string $message
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        (new AttributeParser)->parse($options);
    }

    /**
     * @return iterable<string, array{array<int, string>, string}>
     */
    public static function invalidOptions(): iterable
    {
        yield 'missing equals sign' => [
            ['invalid-attribute'],
            "Attribute 'invalid-attribute' must be of a key=value format."
        ];

        yield 'empty key' => [
            ['=value'],
            'Attribute must not be empty.'
        ];

        yield 'duplicate key' => [
            ['name=First', 'name=Second'],
            "Attribute 'name' was provided more than once."
        ];
    }
}
