<?php

declare(strict_types=1);

namespace JohannesClimacus\ArtisanFactory\Input;

use InvalidArgumentException;
use JohannesClimacus\ArtisanFactory\DTO\FactoryRelation;

final class FactoryRelationParser
{
    /**
     * @param  array<int, string>  $options
     * @return array<int, FactoryRelation>
     */
    public function parse(array $options): array
    {
        $relations = [];
        foreach ($options as $option) {
            $relations[] = $this->parseOption($option);
        }

        return $relations;
    }

    private function parseOption(string $option): FactoryRelation
    {
        $relationship = null;
        $modelReference = $option;

        if (str_contains($option, '=')) {
            [$relationship, $modelReference] = explode('=', $option, 2);

            $relationship = trim($relationship);

            if ($relationship === '') {
                throw new InvalidArgumentException(
                    'Relationship name must not be empty.'
                );
            }
        }

        if (! str_contains($modelReference, ':')) {
            throw new InvalidArgumentException(
                "Relation '{$option}' must use Model:route-key format."
            );
        }

        [$modelName, $routeKey] = explode(':', $modelReference, 2);

        $modelName = trim($modelName);
        $routeKey = trim($routeKey);

        if ($modelName === '') {
            throw new InvalidArgumentException(
                'Related model name must not be empty.'
            );
        }

        if ($routeKey === '') {
            throw new InvalidArgumentException(
                'Related model route key must not be empty.'
            );
        }

        return new FactoryRelation(
            $modelName,
            $routeKey,
            $relationship
        );
    }
}
