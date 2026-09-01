<?php

declare(strict_types=1);

namespace JohannesClimacus\ArtisanFactory\Factories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use JohannesClimacus\ArtisanFactory\DTO\FactoryRelation;
use JohannesClimacus\ArtisanFactory\Relations\RelatedModelResolver;
use ReflectionMethod;

final class FactoryModelCreator
{
    public function __construct(
        private readonly RelatedModelResolver $relatedModelResolver
    ) {}

    /**
     * @param  array<int, string>  $states
     * @param  array<int, FactoryRelation>  $relations
     * @param  array<string, mixed>  $attributes
     * @return Collection<int, Model>
     */
    public function create(
        string $modelClass,
        string $modelName,
        string $modelNamespace,
        int $count,
        array $states,
        array $relations,
        array $attributes
    ): Collection {
        if (! class_exists($modelClass)) {
            throw new InvalidArgumentException(
                "Model '{$modelName}' does not exist."
            );
        }

        if (! is_subclass_of($modelClass, Model::class)) {
            throw new InvalidArgumentException(
                "Class '{$modelClass}' is not an Eloquent model."
            );
        }

        if (! method_exists($modelClass, 'factory')) {
            throw new InvalidArgumentException(
                "Model '{$modelName}' must have a factory."
            );
        }

        $factory = $modelClass::factory();

        foreach ($states as $state) {
            if (! $this->isAllowedState($factory, $state)) {
                throw new InvalidArgumentException(
                    "Factory state '{$state}' is not found for model '{$modelName}'."
                );
            }

            $result = $factory->{$state}();

            if (! $result instanceof Factory) {
                throw new InvalidArgumentException(
                    "Factory state '{$state}' must return a factory instance."
                );
            }

            $factory = $result;
        }

        foreach ($relations as $relation) {
            $parent = $this->relatedModelResolver->resolve(
                $relation,
                $modelNamespace
            );

            $factory = $relation->relationship === null
                ? $factory->for($parent)
                : $factory->for($parent, $relation->relationship);
        }

        $models = $factory
            ->count($count)
            ->create($attributes);

        if ($models instanceof Model) {
            return new Collection([$models]);
        }

        return $models;
    }

    /**
     * @param  Factory<Model>  $factory
     */
    private function isAllowedState(Factory $factory, string $state): bool
    {
        if (! method_exists($factory, $state)) {
            return false;
        }

        $method = new ReflectionMethod($factory, $state);

        return
            $method->isPublic()
            && ! $method->isStatic()
            && $method->getNumberOfRequiredParameters() === 0
            && $method->getDeclaringClass()->getName() === $factory::class;
    }
}
