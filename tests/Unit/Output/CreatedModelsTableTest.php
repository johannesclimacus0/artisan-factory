<?php

declare(strict_types=1);

namespace JohannesClimacus\ArtisanFactory\Tests\Unit\Output;

use Illuminate\Database\Eloquent\Collection;
use JohannesClimacus\ArtisanFactory\Output\CreatedModelsTable;
use JohannesClimacus\ArtisanFactory\Tests\Support\Models\TestUser;
use PHPUnit\Framework\TestCase;

final class CreatedModelsTableTest extends TestCase
{
    public function test_it_builds_summary_rows_for_created_models(): void
    {
        $model = new TestUser;
        $model->forceFill([
            'id' => 15,
            'uuid' => '00000000-0000-0000-0000-000000000001'
        ]);

        $rows = (new CreatedModelsTable)->summaryRows(
            new Collection([$model])
        );

        $this->assertSame([
            [
                1,
                'TestUser',
                15,
                '00000000-0000-0000-0000-000000000001'
            ]
        ], $rows);
    }

    public function test_it_builds_detail_rows_and_formats_values(): void
    {
        $model = new TestUser;
        $model->forceFill([
            'id' => 15,
            'name' => 'Test User',
            'active' => true,
            'settings' => ['locale' => 'ru'],
            'email_verified_at' => null,
            'secret' => 'must-not-be-displayed'
        ]);
        $model->setHidden(['secret']);

        $rows = (new CreatedModelsTable)->detailRows($model);

        $this->assertSame([
            ['id', '15'],
            ['name', 'Test User'],
            ['active', 'true'],
            ['settings', '{"locale":"ru"}'],
            ['email_verified_at', 'NULL']
        ], $rows);
    }
}
