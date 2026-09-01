<?php

declare(strict_types=1);

namespace JohannesClimacus\ArtisanFactory\DTO;

final readonly class FactoryRelation
{
    public function __construct(
        public string $modelName,
        public string $routeKey,
        public ?string $relationship = null
    ) {}
}
