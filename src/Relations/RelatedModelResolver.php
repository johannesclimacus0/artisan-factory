<?php

declare(strict_types=1);

namespace JohannesClimacus\ArtisanFactory\Relations;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use JohannesClimacus\ArtisanFactory\DTO\FactoryRelation;

final class RelatedModelResolver
{
    public function resolve(FactoryRelation $relation, string $modelNamespace): Model
    {
        $modelClass = $modelNamespace.'\\'.ltrim($relation->modelName, '\\');

        if (! class_exists($modelClass)) {
            throw new InvalidArgumentException(
                "Related model '{$relation->modelName}' does not exist."
            );
        }

        if (! is_subclass_of($modelClass, Model::class)) {
            throw new InvalidArgumentException(
                "Class '{$modelClass}' is not an Eloquent model."
            );
        }

        $model = new $modelClass;

        return $model->newQuery()
            ->where($model->getRouteKeyName(), $relation->routeKey)
            ->firstOrFail();
    }
}
