<?php

declare(strict_types=1);

namespace Tests\Feature\YouTube\Channel;

use App\Models\User;
use Canalizador\YouTube\Channel\Infrastructure\DAO\ChannelDAO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UpdateChannelControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_401_when_no_token_provided(): void
    {
        $this->putJson('/api/youtube/channels/UC-X', ['auto_sync' => true])
            ->assertStatus(401);
    }

    public function test_returns_404_when_channel_belongs_to_another_user(): void
    {
        $tokenA = 'TKN_A';
        $tokenB = 'TKN_B';
        User::factory()->create(['api_token' => hash('sha256', $tokenA)]);
        $userB = User::factory()->create(['api_token' => hash('sha256', $tokenB)]);

        ChannelDAO::create($this->channelPayload('UC-B', $userB->id, false));

        $this->putJson('/api/youtube/channels/UC-B', ['auto_sync' => true], [
            'Authorization' => "Bearer {$tokenA}",
        ])->assertStatus(404);
    }

    public function test_toggles_auto_sync(): void
    {
        $token = 'TKN_OWN';
        $user = User::factory()->create(['api_token' => hash('sha256', $token)]);

        ChannelDAO::create($this->channelPayload('UC-OWN', $user->id, false));

        $this->putJson('/api/youtube/channels/UC-OWN', ['auto_sync' => true], [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(200)
            ->assertJsonPath('data.auto_sync', true);

        $this->assertTrue((bool) ChannelDAO::find('UC-OWN')->auto_sync);

        $this->putJson('/api/youtube/channels/UC-OWN', ['auto_sync' => false], [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(200)
            ->assertJsonPath('data.auto_sync', false);

        $this->assertFalse((bool) ChannelDAO::find('UC-OWN')->auto_sync);
    }

    public function test_toggles_auto_publish(): void
    {
        $token = 'TKN_PUB';
        $user = User::factory()->create(['api_token' => hash('sha256', $token)]);

        ChannelDAO::create($this->channelPayload('UC-PUB', $user->id, false));

        $this->putJson('/api/youtube/channels/UC-PUB', ['auto_publish' => true], [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(200)
            ->assertJsonPath('data.auto_publish', true);

        $this->assertTrue((bool) ChannelDAO::find('UC-PUB')->auto_publish);
    }

    public function test_returns_422_when_auto_sync_is_not_boolean(): void
    {
        $token = 'TKN_BAD';
        $user = User::factory()->create(['api_token' => hash('sha256', $token)]);
        ChannelDAO::create($this->channelPayload('UC-BAD', $user->id, false));

        $this->putJson('/api/youtube/channels/UC-BAD', ['auto_sync' => 'potato'], [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['auto_sync']);
    }

    /**
     * @return array<string, mixed>
     */
    private function channelPayload(string $id, int $userId, bool $autoSync): array
    {
        return [
            'id'               => $id,
            'user_id'          => $userId,
            'title'            => 'Channel',
            'description'      => 'desc',
            'custom_url'       => null,
            'published_at'     => '2025-01-01 00:00:00',
            'thumbnail_url'    => null,
            'country'          => 'ES',
            'view_count'       => 0,
            'subscriber_count' => 0,
            'video_count'      => 0,
            'privacy_status'   => 'public',
            'channel_brand'    => 'brand',
            'auto_sync'        => $autoSync,
            'auto_publish'     => false,
        ];
    }
}
