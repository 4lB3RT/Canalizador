<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'google_access_token',
        'google_refresh_token',
        'google_expires_in',
        'google_scope',
        'google_token_type',
        'api_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function generateApiToken(): string
    {
        $token = \Illuminate\Support\Str::random(80);

        $this->update([
            'api_token' => hash('sha256', $token),
        ]);

        return $token;
    }

    public static function findByApiToken(string $token): ?self
    {
        return self::where('api_token', $token)->first();
    }
}
