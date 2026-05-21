<?php

declare(strict_types=1);

namespace Tests\Feature\YouTube\Video;

use App\Models\User;
use Canalizador\YouTube\Video\Application\UseCases\SyncVideo\SyncVideo;
use Canalizador\YouTube\Video\Application\UseCases\SyncVideo\SyncVideoRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

final class SyncVideoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_401_when_no_token_provided(): void
    {
        $this->postJson('/api/youtube/videos/sync', ['platform_id' => 'dQw4w9WgXcQ'])
            ->assertStatus(401);
    }

    public function test_returns_403_when_google_is_not_linked(): void
    {
        $token = 'TKN';
        User::factory()->create(['api_token' => hash('sha256', $token)]);

        $this->postJson('/api/youtube/videos/sync', ['platform_id' => 'dQw4w9WgXcQ'], [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(403);
    }

    public function test_returns_422_when_platform_id_missing(): void
    {
        $token = 'TKN_G';
        User::factory()->create([
            'api_token'           => hash('sha256', $token),
            'google_access_token' => 'ya29.abc',
            'google_expires_in'   => 3600,
        ]);

        $this->postJson('/api/youtube/videos/sync', [], [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['platform_id']);
    }

    public function test_executes_use_case_synchronously_and_returns_200(): void
    {
        $token = 'TKN_OK';
        $user = User::factory()->create([
            'api_token'           => hash('sha256', $token),
            'google_access_token' => 'ya29.abc',
            'google_expires_in'   => 3600,
        ]);

        $mock = Mockery::mock(SyncVideo::class);
        $mock->shouldReceive('execute')
            ->once()
            ->with(Mockery::on(function (SyncVideoRequest $req) use ($user) {
                return $req->platformId->value() === 'dQw4w9WgXcQ'
                    && $req->userId->value() === $user->id;
            }));
        $this->app->instance(SyncVideo::class, $mock);

        $this->postJson('/api/youtube/videos/sync', ['platform_id' => 'dQw4w9WgXcQ'], [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(200)
            ->assertJsonPath('data.status', 'synced')
            ->assertJsonPath('data.platform_id', 'dQw4w9WgXcQ');
    }
}
