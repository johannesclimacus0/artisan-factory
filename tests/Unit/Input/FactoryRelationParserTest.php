<?php

declare(strict_types=1);

namespace JohannesClimacus\ArtisanFactory\Tests\Unit\Input;

use InvalidArgumentException;
use JohannesClimacus\ArtisanFactory\Input\FactoryRelationParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FactoryRelationParserTest extends TestCase
{
    public function test_it_parses_relation_with_inferred_relationship(): void
    {
        $relations = (new FactoryRelationParser)->parse(['User:15']);

        $this->assertCount(1, $relations);
        $this->assertSame('User', $relations[0]->modelName);
        $this->assertSame('15', $relations[0]->routeKey);
        $this->assertNull($relations[0]->relationship);
    }

    public function test_it_parses_relation_with_explicit_relationship(): void
    {
        $relations = (new FactoryRelationParser)->parse(['sender=User:15']);

        $this->assertCount(1, $relations);
        $this->assertSame('User', $relations[0]->modelName);
        $this->assertSame('15', $relations[0]->routeKey);
        $this->assertSame('sender', $relations[0]->relationship);
    }

    #[DataProvider('invalidRelationOptions')]
    public function test_it_rejects_invalid_relation_option(
        string $option,
        string $expectedMessage
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        (new FactoryRelationParser)->parse([$option]);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function invalidRelationOptions(): iterable
    {
        yield 'missing route key separator' => [
            'User',
            "Relation 'User' must use Model:route-key format."
        ];

        yield 'empty model name' => [
            ':15',
            'Related model name must not be empty.'
        ];

        yield 'empty route key' => [
            'User:',
            'Related model route key must not be empty.'
        ];

        yield 'empty relationship name' => [
            '=User:15',
            'Relationship name must not be empty.'
        ];
    }
}
