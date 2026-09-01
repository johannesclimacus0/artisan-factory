<?php

declare(strict_types=1);

namespace JohannesClimacus\ArtisanFactory\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use JohannesClimacus\ArtisanFactory\Factories\FactoryModelCreator;
use JohannesClimacus\ArtisanFactory\Input\AttributeParser;
use JohannesClimacus\ArtisanFactory\Input\FactoryRelationParser;
use JohannesClimacus\ArtisanFactory\Output\CreatedModelsTable;
use Throwable;

#[Signature('factory:create
    {model : Model name}
    {--count=1 : Number of records to create}
    {--state=* : Factory state}
    {--set=* : Attribute in key=value format}
    {--details : Display all visible attributes of created models}
    {--for=* : Parent in [relationship=]Model:key format}
')]
#[Description('Create a record using model factory')]
class FactoryCreateCommand extends Command
{
    public function __construct(
        private readonly AttributeParser $attributeParser,
        private readonly FactoryRelationParser $relationParser,
        private readonly FactoryModelCreator $factoryModelCreator,
        private readonly CreatedModelsTable $createdModelsTable
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $modelArgument = $this->argument('model');

        if (! is_string($modelArgument)) {
            $this->error('Model name must be a string.');

            return self::FAILURE;
        }

        $modelName = $modelArgument;
        $modelNamespace = (string) config('factory-create.model_namespace',
            'App\\Models'
        );
        $modelClass = $modelNamespace.'\\'.$modelName;

        /** @var array<int, string> $attributeOptions */
        $attributeOptions = (array) $this->option('set');

        $count = (int) $this->option('count');
        $maxCount = (int) config('factory-create.max_count', 20);

        /** @var array<int, string> $states */
        $states = (array) $this->option('state');

        /** @var array<int, string> $relationOptions */
        $relationOptions = (array) $this->option('for');

        if ($count < 1 || $count > $maxCount) {
            $this->error('Count must be between 1 and '.$maxCount.'.');

            return self::FAILURE;
        }

        try {
            $attributes = $this->attributeParser->parse($attributeOptions);
            $relations = $this->relationParser->parse($relationOptions);

            $models = $this->factoryModelCreator->create(
                modelClass: $modelClass,
                modelName: $modelName,
                modelNamespace: $modelNamespace,
                count: $count,
                states: $states,
                relations: $relations,
                attributes: $attributes
            );

            if ($this->option('details')) {
                $this->displayModelDetails($models);
            } else {
                $this->displayCreatedModels($models);
            }
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Created {$models->count()} {$modelName} record".($models->count() === 1 ? '.' : 's.'));

        return self::SUCCESS;
    }

    /**
     * @param  Collection<int, Model>  $models
     */
    private function displayCreatedModels(Collection $models): void
    {
        $this->table(
            ['№', 'Model', 'Key', 'UUID'],
            $this->createdModelsTable->summaryRows($models)
        );
    }

    /**
     * @param  Collection<int, Model>  $models
     */
    private function displayModelDetails(Collection $models): void
    {
        foreach ($models as $model) {
            $this->line('<info>'.class_basename($model).' #'.$model->getKey().'</info>');

            $this->table(
                ['Attribute', 'Value'],
                $this->createdModelsTable->detailRows($model)
            );
        }
    }
}
