<?php

namespace JohannesClimacus\ArtisanFactory\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use JohannesClimacus\ArtisanFactory\Tests\Support\Models\TestPost;
use JohannesClimacus\ArtisanFactory\Tests\Support\Models\TestUser;
use JohannesClimacus\ArtisanFactory\Tests\TestCase;

class FactoryCreateCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_requested_number_of_models(): void
    {
        $this->artisan('factory:create', [
            'model' => 'TestUser',
            '--count' => 3
        ])
            ->expectsOutput('Created 3 TestUser records.')
            ->assertSuccessful();

        $this->assertSame(3, TestUser::query()->count());
    }

    public function test_command_uses_default_count(): void
    {
        $this->artisan('factory:create', [
            'model' => 'TestUser'
        ])
            ->expectsOutput('Created 1 TestUser record.')
            ->assertSuccessful();

        $this->assertSame(1, TestUser::query()->count());
    }

    public function test_command_applies_factory_state(): void
    {
        $this->artisan('factory:create', [
            'model' => 'TestUser',
            '--state' => ['unverified']
        ])->assertSuccessful();

        $this->assertNull(TestUser::query()->sole()->email_verified_at);
    }

    public function test_command_applies_attributes(): void
    {
        $this->artisan('factory:create', [
            'model' => 'TestUser',
            '--set' => [
                'name=a=b',
                'email=test@example.org',
                'email_verified_at=null'
            ]
        ])->assertSuccessful();

        $user = TestUser::query()->sole();

        $this->assertSame('a=b', $user->name);
        $this->assertSame('test@example.org', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_command_displays_created_model_details(): void
    {
        $this->artisan('factory:create', [
            'model' => 'TestUser',
            '--state' => ['unverified'],
            '--set' => [
                'name=Test User',
                'email=test@example.org'
            ],
            '--details' => true
        ])
            ->expectsOutputToContain('TestUser #1')
            ->expectsOutputToContain('Test User')
            ->expectsOutputToContain('test@example.org')
            ->expectsOutputToContain('NULL')
            ->assertSuccessful();
    }

    public function test_command_creates_model_for_inferred_and_explicit_relations(): void
    {
        $owner = TestUser::factory()->create();
        $sender = TestUser::factory()->create();

        $this->artisan('factory:create', [
            'model' => 'TestPost',
            '--for' => [
                'TestUser:'.$owner->uuid,
                'sender=TestUser:'.$sender->uuid
            ],
            '--set' => ['title=Related post']
        ])->assertSuccessful();

        $post = TestPost::query()->sole();

        $this->assertTrue($post->testUser->is($owner));
        $this->assertTrue($post->sender->is($sender));
        $this->assertSame('Related post', $post->title);
    }

    public function test_command_rejects_invalid_relation_format(): void
    {
        $this->artisan('factory:create', [
            'model' => 'TestPost',
            '--for' => ['TestUser']
        ])
            ->expectsOutput("Relation 'TestUser' must use Model:route-key format.")
            ->assertFailed();

        $this->assertSame(0, TestPost::query()->count());
    }

    public function test_command_fails_when_related_record_does_not_exist(): void
    {
        $this->artisan('factory:create', [
            'model' => 'TestPost',
            '--for' => ['TestUser:missing-uuid']
        ])->assertFailed();

        $this->assertSame(0, TestPost::query()->count());
    }

    public function test_command_rejects_count_below_one(): void
    {
        $this->artisan('factory:create', [
            'model' => 'TestUser',
            '--count' => 0
        ])
            ->expectsOutput('Count must be between 1 and 20.')
            ->assertFailed();

        $this->assertSame(0, TestUser::query()->count());
    }

    public function test_command_rejects_count_above_configured_limit(): void
    {
        config(['factory-create.max_count' => 2]);

        $this->artisan('factory:create', [
            'model' => 'TestUser',
            '--count' => 3
        ])
            ->expectsOutput('Count must be between 1 and 2.')
            ->assertFailed();

        $this->assertSame(0, TestUser::query()->count());
    }

    public function test_command_rejects_unknown_model(): void
    {
        $this->artisan('factory:create', [
            'model' => 'UnknownModel'
        ])
            ->expectsOutput("Model 'UnknownModel' does not exist.")
            ->assertFailed();
    }

    public function test_command_rejects_unknown_factory_state(): void
    {
        $this->artisan('factory:create', [
            'model' => 'TestUser',
            '--state' => ['unknown']
        ])
            ->expectsOutput("Factory state 'unknown' is not found for model 'TestUser'.")
            ->assertFailed();

        $this->assertSame(0, TestUser::query()->count());
    }

    public function test_command_rejects_inherited_factory_method_as_state(): void
    {
        $this->artisan('factory:create', [
            'model' => 'TestUser',
            '--state' => ['create']
        ])
            ->expectsOutput("Factory state 'create' is not found for model 'TestUser'.")
            ->assertFailed();

        $this->assertSame(0, TestUser::query()->count());
    }

    public function test_command_rejects_invalid_attribute_format(): void
    {
        $this->artisan('factory:create', [
            'model' => 'TestUser',
            '--set' => ['invalid-attribute']
        ])
            ->expectsOutput("Attribute 'invalid-attribute' must be of a key=value format.")
            ->assertFailed();

        $this->assertSame(0, TestUser::query()->count());
    }

    public function test_command_rejects_duplicate_attributes(): void
    {
        $this->artisan('factory:create', [
            'model' => 'TestUser',
            '--set' => [
                'name=First name',
                'name=Second name'
            ]
        ])
            ->expectsOutput("Attribute 'name' was provided more than once.")
            ->assertFailed();

        $this->assertSame(0, TestUser::query()->count());
    }
}
