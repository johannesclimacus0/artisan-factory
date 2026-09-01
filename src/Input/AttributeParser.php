<?php

declare(strict_types=1);

namespace JohannesClimacus\ArtisanFactory\Input;

use InvalidArgumentException;

final class AttributeParser
{
    /**
     * @param  array<int, string>  $options
     * @return array<string, mixed>
     */
    public function parse(array $options): array
    {
        $attributes = [];

        foreach ($options as $option) {
            if (! str_contains($option, '=')) {
                throw new InvalidArgumentException(
                    "Attribute '{$option}' must be of a key=value format."
                );
            }

            [$key, $value] = explode('=', $option, 2);

            $key = trim($key);

            if ($key === '') {
                throw new InvalidArgumentException(
                    'Attribute must not be empty.'
                );
            }

            if (array_key_exists($key, $attributes)) {
                throw new InvalidArgumentException(
                    "Attribute '{$key}' was provided more than once."
                );
            }

            $attributes[$key] = $this->parseValue($value);
        }

        return $attributes;
    }

    private function parseValue(string $value): mixed
    {
        return match (strtolower(trim($value))) {
            'null' => null,
            'true' => true,
            'false' => false,
            default => $value
        };
    }
}
