<?php

declare(strict_types=1);

namespace Tests\Feature\YouTube\Video;

use App\Models\User;
use Canalizador\YouTube\Channel\Infrastructure\DAO\ChannelDAO;
use Canalizador\YouTube\Video\Infrastructure\DAO\VideoDAO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GetVideosControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_401_when_no_token_provided(): void
    {
        $this->getJson('/api/youtube/videos')
            ->assertStatus(401);
    }

    public function test_returns_empty_when_user_has_no_videos(): void
    {
        $token = 'TOKEN_EMPTY';
        User::factory()->create(['api_token' => hash('sha256', $token)]);

        $this->getJson('/api/youtube/videos', [
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

    public function test_isolates_videos_per_user(): void
    {
        $tokenA = 'TOKEN_A';
        $tokenB = 'TOKEN_B';
        $userA = User::factory()->create(['api_token' => hash('sha256', $tokenA)]);
        $userB = User::factory()->create(['api_token' => hash('sha256', $tokenB)]);

        ChannelDAO::create($this->channelPayload('UC-A1', $userA->id));
        ChannelDAO::create($this->channelPayload('UC-A2', $userA->id));
        ChannelDAO::create($this->channelPayload('UC-B1', $userB->id));

        VideoDAO::create($this->videoPayload('a1-v1', 'UC-A1', 'Hola A1', 'video', 1));
        VideoDAO::create($this->videoPayload('a2-v1', 'UC-A2', 'Hola A2', 'video', 2));
        VideoDAO::create($this->videoPayload('b1-v1', 'UC-B1', 'Hola B1', 'video', 3));

        $response = $this->getJson('/api/youtube/videos', [
            'Authorization' => "Bearer {$tokenA}",
        ])->assertStatus(200);

        $response->assertJsonPath('meta.total', 2);
        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertEqualsCanonicalizing(['a1-v1', 'a2-v1'], $ids);
    }

    public function test_filters_by_category_channel_and_search(): void
    {
        $token = 'TOKEN_LIST';
        $user = User::factory()->create(['api_token' => hash('sha256', $token)]);
        ChannelDAO::create($this->channelPayload('UC-X', $user->id));
        ChannelDAO::create($this->channelPayload('UC-Y', $user->id));

        VideoDAO::create($this->videoPayload('v-1', 'UC-X', 'React 19 en producción', 'video', 1));
        VideoDAO::create($this->videoPayload('v-2', 'UC-X', 'Top 5 IA 2026', 'video', 2));
        VideoDAO::create($this->videoPayload('v-3', 'UC-Y', 'React shorts', 'short', 3));
        VideoDAO::create($this->videoPayload('v-4', 'UC-Y', 'Vlog del finde', 'video', 4));

        // Filter category=video
        $this->getJson('/api/youtube/videos?category=video', [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(200)
            ->assertJsonPath('meta.total', 3);

        // Filter channel_id=UC-X
        $this->getJson('/api/youtube/videos?channel_id=UC-X', [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(200)
            ->assertJsonPath('meta.total', 2);

        // Search by title
        $response = $this->getJson('/api/youtube/videos?q=react', [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(200);
        $response->assertJsonPath('meta.total', 2);

        // Combinado
        $response = $this->getJson('/api/youtube/videos?q=react&category=short', [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(200);
        $response->assertJsonPath('meta.total', 1);
        $response->assertJsonPath('data.0.id', 'v-3');
    }

    public function test_pagination_works(): void
    {
        $token = 'TOKEN_PAGE';
        $user = User::factory()->create(['api_token' => hash('sha256', $token)]);
        ChannelDAO::create($this->channelPayload('UC-P', $user->id));

        for ($i = 1; $i <= 5; $i++) {
            VideoDAO::create($this->videoPayload(sprintf('p-%02d', $i), 'UC-P', "Video {$i}", 'video', $i));
        }

        $response = $this->getJson('/api/youtube/videos?page=2&per_page=2', [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(200);

        $response->assertJsonCount(2, 'data');
        $response->assertJsonPath('meta.total', 5);
        $response->assertJsonPath('meta.last_page', 3);
    }

    public function test_returns_422_when_query_is_invalid(): void
    {
        $token = 'TOKEN_BAD';
        User::factory()->create(['api_token' => hash('sha256', $token)]);

        $this->getJson('/api/youtube/videos?per_page=999', [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(422);

        $this->getJson('/api/youtube/videos?category=potato', [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(422);
    }

    /**
     * @return array<string, mixed>
     */
    private function channelPayload(string $id, int $userId): array
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
