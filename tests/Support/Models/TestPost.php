<?php

declare(strict_types=1);

namespace JohannesClimacus\ArtisanFactory\Tests\Support\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use JohannesClimacus\ArtisanFactory\Tests\Support\Factories\TestPostFactory;

final class TestPost extends Model
{
    /** @use HasFactory<TestPostFactory> */
    use HasFactory;

    protected $table = 'test_posts';

    protected $guarded = [];

    public function testUser(): BelongsTo
    {
        return $this->belongsTo(TestUser::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(TestUser::class, 'sender_id');
    }

    protected static function newFactory(): TestPostFactory
    {
        return TestPostFactory::new();
    }
}
