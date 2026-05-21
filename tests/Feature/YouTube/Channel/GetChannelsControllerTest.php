<?php

declare(strict_types=1);

namespace Tests\Feature\YouTube\Channel;

use App\Models\User;
use Canalizador\YouTube\Channel\Infrastructure\DAO\ChannelDAO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GetChannelsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_401_when_no_token_provided(): void
    {
        $this->getJson('/api/youtube/channels')
            ->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized']);
    }

    public function test_returns_401_when_token_is_invalid(): void
    {
        $this->getJson('/api/youtube/channels', [
            'Authorization' => 'Bearer not-a-valid-token',
        ])->assertStatus(401);
    }

    public function test_returns_empty_data_when_user_has_no_channels(): void
    {
        $token = 'TEST_TOKEN_A';
        User::factory()->create(['api_token' => hash('sha256', $token)]);

        $this->getJson('/api/youtube/channels', [
            'Authorization' => "Bearer {$token}",
        ])
            ->assertStatus(200)
            ->assertExactJson([
                'data' => [],
                'meta' => [
                    'page'      => 1,
                    'per_page'  => 12,
                    'total'     => 0,
                    'last_page' => 1,
                ],
            ]);
    }

    public function test_returns_only_channels_belonging_to_authenticated_user(): void
    {
        $tokenA = 'TOKEN_A';
        $tokenB = 'TOKEN_B';
        $userA = User::factory()->create(['api_token' => hash('sha256', $tokenA)]);
        $userB = User::factory()->create(['api_token' => hash('sha256', $tokenB)]);

        ChannelDAO::create($this->channelPayload('UC-A1', $userA->id, 'Channel A1'));
        ChannelDAO::create($this->channelPayload('UC-A2', $userA->id, 'Channel A2'));
        ChannelDAO::create($this->channelPayload('UC-B1', $userB->id, 'Channel B1'));

        $response = $this->getJson('/api/youtube/channels', [
            'Authorization' => "Bearer {$tokenA}",
        ])->assertStatus(200);

        $response->assertJsonCount(2, 'data');
        $response->assertJsonPath('meta.total', 2);
        $response->assertJsonPath('meta.page', 1);

        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertEqualsCanonicalizing(['UC-A1', 'UC-A2'], $ids);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
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
            ],
            'meta' => ['page', 'per_page', 'total', 'last_page'],
        ]);
    }

    public function test_pagination_returns_requested_slice(): void
    {
        $token = 'TOKEN_PAGE';
        $user = User::factory()->create(['api_token' => hash('sha256', $token)]);

        for ($i = 1; $i <= 5; $i++) {
            ChannelDAO::create($this->channelPayload(sprintf('UC-%02d', $i), $user->id, "Channel {$i}"));
        }

        $response = $this->getJson('/api/youtube/channels?page=2&per_page=2', [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(200);

        $response->assertJsonCount(2, 'data');
        $response->assertJsonPath('meta.page', 2);
        $response->assertJsonPath('meta.per_page', 2);
        $response->assertJsonPath('meta.total', 5);
        $response->assertJsonPath('meta.last_page', 3);
    }

    public function test_returns_422_when_pagination_is_invalid(): void
    {
        $token = 'TOKEN_BAD';
        User::factory()->create(['api_token' => hash('sha256', $token)]);

        $this->getJson('/api/youtube/channels?page=0', [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(422);

        $this->getJson('/api/youtube/channels?per_page=999', [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(422);
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
