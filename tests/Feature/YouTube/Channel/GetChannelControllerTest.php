<?php

declare(strict_types=1);

namespace Tests\Feature\YouTube\Channel;

use App\Models\User;
use Canalizador\YouTube\Channel\Infrastructure\DAO\ChannelDAO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GetChannelControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_401_when_no_token_provided(): void
    {
        $this->getJson('/api/youtube/channels/UC-X')
            ->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized']);
    }

    public function test_returns_404_when_channel_does_not_exist(): void
    {
        $token = 'TOKEN_X';
        User::factory()->create(['api_token' => hash('sha256', $token)]);

        $this->getJson('/api/youtube/channels/UC-DOES-NOT-EXIST', [
            'Authorization' => "Bearer {$token}",
        ])
            ->assertStatus(404)
            ->assertJson(['error' => 'Channel not found']);
    }

    public function test_returns_404_when_channel_belongs_to_another_user(): void
    {
        $tokenA = 'TOKEN_A';
        $tokenB = 'TOKEN_B';
        $userA = User::factory()->create(['api_token' => hash('sha256', $tokenA)]);
        $userB = User::factory()->create(['api_token' => hash('sha256', $tokenB)]);

        ChannelDAO::create($this->channelPayload('UC-B-OWNED', $userB->id, 'Owned by B'));

        $this->getJson('/api/youtube/channels/UC-B-OWNED', [
            'Authorization' => "Bearer {$tokenA}",
        ])
            ->assertStatus(404)
            ->assertJson(['error' => 'Channel not found']);
    }

    public function test_returns_200_with_channel_when_owned_by_authenticated_user(): void
    {
        $token = 'TOKEN_OWN';
        $user = User::factory()->create(['api_token' => hash('sha256', $token)]);

        ChannelDAO::create($this->channelPayload('UC-MINE', $user->id, 'My Channel'));

        $response = $this->getJson('/api/youtube/channels/UC-MINE', [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(200);

        $response->assertJsonPath('data.id', 'UC-MINE');
        $response->assertJsonPath('data.user_id', $user->id);
        $response->assertJsonPath('data.title', 'My Channel');

        $response->assertJsonStructure([
            'data' => [
                'id',
                'user_id',
                'title',
                'description',
                'custom_url',
                'published_at',
                'thumbnail_url',
                'country',
                'view_count',
                'subscriber_count',
                'video_count',
                'privacy_status',
                'channel_brand',
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function channelPayload(string $id, int $userId, string $title): array
    {
        return [
            'id'               => $id,
            'user_id'          => $userId,
            'title'            => $title,
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
        ];
    }
}
