<?php

declare(strict_types=1);

namespace Tests\Feature\Shared\Header;

use App\Models\User;
use Canalizador\YouTube\Channel\Infrastructure\DAO\ChannelDAO;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GetHeaderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_401_when_no_token_provided(): void
    {
        $this->getJson('/api/header')
            ->assertStatus(401);
    }

    public function test_returns_user_without_google_and_no_channels(): void
    {
        $token = 'TOKEN_HEADER_BASIC';
        $user = User::factory()->create([
            'api_token' => hash('sha256', $token),
            'name' => 'Albert',
            'email' => 'albert@example.com',
        ]);

        $this->getJson('/api/header', [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(200)
            ->assertExactJson([
                'data' => [
                    'user' => [
                        'id'    => $user->id,
                        'name'  => 'Albert',
                        'email' => 'albert@example.com',
                    ],
                    'google_linked'  => false,
                    'channels_count' => 0,
                ],
            ]);
    }

    public function test_returns_google_linked_and_channels_count(): void
    {
        $token = 'TOKEN_HEADER_FULL';
        $user = User::factory()->create([
            'api_token'           => hash('sha256', $token),
            'name'                => 'Albert',
            'email'               => 'albert@example.com',
            'google_access_token' => 'ya29.abc',
            'google_expires_in'   => 3600,
        ]);

        ChannelDAO::create($this->channelPayload('UC-A', $user->id));
        ChannelDAO::create($this->channelPayload('UC-B', $user->id));

        $this->getJson('/api/header', [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(200)
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.google_linked', true)
            ->assertJsonPath('data.channels_count', 2);
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
}
