<?php

declare(strict_types=1);

namespace Tests\Feature\YouTube\Video;

use App\Models\User;
use Helmreel\YouTube\Channel\Infrastructure\DAO\ChannelDAO;
use Helmreel\YouTube\Video\Infrastructure\DAO\VideoDAO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GetChannelVideosControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_401_when_no_token_provided(): void
    {
        $this->getJson('/api/youtube/channels/UC-ANY/videos')
            ->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized']);
    }

    public function test_returns_404_when_channel_does_not_exist(): void
    {
        $token = 'TOKEN_X';
        User::factory()->create(['api_token' => hash('sha256', $token)]);

        $this->getJson('/api/youtube/channels/UC-MISSING/videos', [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(404)
            ->assertJson(['error' => 'Channel not found']);
    }

    public function test_returns_404_when_channel_belongs_to_another_user(): void
    {
        $tokenA = 'TOKEN_A';
        $tokenB = 'TOKEN_B';
        $userA = User::factory()->create(['api_token' => hash('sha256', $tokenA)]);
        $userB = User::factory()->create(['api_token' => hash('sha256', $tokenB)]);

        ChannelDAO::create($this->channelPayload('UC-OWN-B', $userB->id, 'Channel B'));

        $this->getJson('/api/youtube/channels/UC-OWN-B/videos', [
            'Authorization' => "Bearer {$tokenA}",
        ])->assertStatus(404);
    }

    public function test_returns_empty_data_when_channel_has_no_videos(): void
    {
        $token = 'TOKEN_EMPTY';
        $user = User::factory()->create(['api_token' => hash('sha256', $token)]);

        ChannelDAO::create($this->channelPayload('UC-EMPTY', $user->id, 'Empty Channel'));

        $this->getJson('/api/youtube/channels/UC-EMPTY/videos', [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(200)
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

    public function test_pagination_and_category_filter(): void
    {
        $token = 'TOKEN_LIST';
        $user = User::factory()->create(['api_token' => hash('sha256', $token)]);

        ChannelDAO::create($this->channelPayload('UC-MIX', $user->id, 'Mixed'));

        for ($i = 1; $i <= 5; $i++) {
            VideoDAO::create($this->videoPayload("vid-{$i}", 'UC-MIX', "Video {$i}", 'video', $i));
        }
        for ($i = 1; $i <= 3; $i++) {
            VideoDAO::create($this->videoPayload("short-{$i}", 'UC-MIX', "Short {$i}", 'short', 100 + $i));
        }

        // Sin filtro → todos
        $this->getJson('/api/youtube/channels/UC-MIX/videos?per_page=20', [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(200)
            ->assertJsonPath('meta.total', 8);

        // Filtrado por category=video
        $response = $this->getJson('/api/youtube/channels/UC-MIX/videos?category=video&per_page=2&page=2', [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(200);

        $response->assertJsonPath('meta.total', 5);
        $response->assertJsonPath('meta.page', 2);
        $response->assertJsonPath('meta.per_page', 2);
        $response->assertJsonPath('meta.last_page', 3);
        $response->assertJsonCount(2, 'data');

        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'published_at', 'category', 'status', 'channel_id', 'duration'],
            ],
            'meta' => ['page', 'per_page', 'total', 'last_page'],
        ]);
    }

    public function test_returns_422_when_per_page_is_invalid(): void
    {
        $token = 'TOKEN_BAD';
        $user = User::factory()->create(['api_token' => hash('sha256', $token)]);
        ChannelDAO::create($this->channelPayload('UC-422', $user->id, 'Channel'));

        $this->getJson('/api/youtube/channels/UC-422/videos?per_page=999', [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(422);

        $this->getJson('/api/youtube/channels/UC-422/videos?category=potato', [
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

    /**
     * @return array<string, mixed>
     */
    private function videoPayload(string $id, string $channelId, string $title, string $category, int $offsetDays): array
    {
        return [
            'id'           => $id,
            'platform_id'  => null,
            'parent_id'    => null,
            'channel_id'   => $channelId,
            'title'        => $title,
            'description'  => null,
            'url'          => 'https://www.youtube.com/watch?v=' . $id,
            'category'     => $category,
            'status'       => 'public',
            'duration'     => 10,
            'published_at' => date('Y-m-d H:i:s', strtotime("-{$offsetDays} days")),
            'local_path'   => null,
            'audio_path'   => null,
        ];
    }
}
