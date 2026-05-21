<?php

declare(strict_types=1);

namespace Tests\Feature\Shared\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class UpdateProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_401_when_no_token_provided(): void
    {
        $this->postJson('/api/profile', ['name' => 'X'])
            ->assertStatus(401);
    }

    public function test_updates_name(): void
    {
        $token = 'TKN';
        $user = User::factory()->create([
            'api_token' => hash('sha256', $token),
            'name'      => 'Old',
        ]);

        $this->postJson('/api/profile', ['name' => 'New'], [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(200)
            ->assertJsonPath('data.name', 'New');

        $this->assertSame('New', $user->fresh()->name);
    }

    public function test_returns_422_when_email_is_taken_by_other_user(): void
    {
        $token = 'TKN_A';
        User::factory()->create([
            'api_token' => hash('sha256', $token),
            'email'     => 'mine@example.com',
        ]);
        User::factory()->create(['email' => 'taken@example.com']);

        $this->postJson('/api/profile', ['email' => 'taken@example.com'], [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_updates_email(): void
    {
        $token = 'TKN_EM';
        $user = User::factory()->create([
            'api_token' => hash('sha256', $token),
            'email'     => 'old@example.com',
        ]);

        $this->postJson('/api/profile', ['email' => 'new@example.com'], [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(200)
            ->assertJsonPath('data.email', 'new@example.com');

        $this->assertSame('new@example.com', $user->fresh()->email);
    }

    public function test_password_requires_confirmation(): void
    {
        $token = 'TKN_PW';
        User::factory()->create([
            'api_token' => hash('sha256', $token),
            'password'  => Hash::make('old-pass-123'),
        ]);

        $this->postJson('/api/profile', [
            'password'         => 'new-secret-123',
            'current_password' => 'old-pass-123',
        ], [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_password_requires_current_password(): void
    {
        $token = 'TKN_PW_NOCUR';
        User::factory()->create([
            'api_token' => hash('sha256', $token),
            'password'  => Hash::make('old-pass-123'),
        ]);

        $this->postJson('/api/profile', [
            'password'              => 'new-secret-123',
            'password_confirmation' => 'new-secret-123',
        ], [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_password_rejects_invalid_current_password(): void
    {
        $token = 'TKN_PW_BAD';
        $user = User::factory()->create([
            'api_token' => hash('sha256', $token),
            'password'  => Hash::make('old-pass-123'),
        ]);

        $this->postJson('/api/profile', [
            'password'              => 'new-secret-123',
            'password_confirmation' => 'new-secret-123',
            'current_password'      => 'wrong-current',
        ], [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);

        // Password must remain unchanged
        $this->assertTrue(Hash::check('old-pass-123', $user->fresh()->password));
    }

    public function test_updates_password_when_current_and_confirmation_match(): void
    {
        $token = 'TKN_PWOK';
        $user = User::factory()->create([
            'api_token' => hash('sha256', $token),
            'password'  => Hash::make('old-pass-123'),
        ]);

        $this->postJson('/api/profile', [
            'password'              => 'new-secret-123',
            'password_confirmation' => 'new-secret-123',
            'current_password'      => 'old-pass-123',
        ], [
            'Authorization' => "Bearer {$token}",
        ])->assertStatus(200);

        $this->assertTrue(Hash::check('new-secret-123', $user->fresh()->password));
    }

    public function test_uploads_avatar(): void
    {
        Storage::fake('public');

        $token = 'TKN_AV';
        $user = User::factory()->create(['api_token' => hash('sha256', $token)]);

        $file = UploadedFile::fake()->image('avatar.jpg', 100, 100);

        $response = $this->post('/api/profile', [
            'avatar' => $file,
        ], [
            'Authorization' => "Bearer {$token}",
            'Accept'        => 'application/json',
        ])->assertStatus(200);

        $avatarUrl = $response->json('data.avatar_url');
        $this->assertNotNull($avatarUrl);

        $fresh = $user->fresh();
        $this->assertNotNull($fresh->avatar_path);
        Storage::disk('public')->assertExists($fresh->avatar_path);
    }
}
