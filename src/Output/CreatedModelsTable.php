<?php

declare(strict_types=1);

namespace JohannesClimacus\ArtisanFactory\Output;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

final class CreatedModelsTable
{
    /**
     * @param  Collection<int, Model>  $models
     * @return array<int, array<int, mixed>>
     */
    public function summaryRows(Collection $models): array
    {
        return $models->values()
            ->map(function (Model $model, int $index): array {
                return [
                    $index + 1,
                    class_basename($model),
                    $model->getKey(),
                    $model->getAttribute('uuid') ?? '-'
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array{string, string}>
     */
    public function detailRows(Model $model): array
    {
        return collect($model->attributesToArray())
            ->map(fn (mixed $value, string $attribute): array => [
                $attribute,
                $this->formatValue($value)
            ])
            ->values()
            ->all();
    }

    private function formatValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return json_encode(
            $value,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }
}
