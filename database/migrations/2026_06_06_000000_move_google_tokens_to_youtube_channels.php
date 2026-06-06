<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('youtube_channels', function (Blueprint $table) {
            $table->text('access_token')->nullable()->after('auto_publish');
            $table->text('refresh_token')->nullable()->after('access_token');
            $table->timestamp('token_expires_at')->nullable()->after('refresh_token');
            $table->text('token_scope')->nullable()->after('token_expires_at');
            $table->string('token_type')->nullable()->after('token_scope');
        });

        // Move any existing tokens into the channels row (encrypted blobs are
        // copied verbatim; same APP_KEY, so the encrypted cast reads them back).
        // Portable across MySQL (prod) and SQLite (tests).
        if (Schema::hasTable('channel_google_tokens')) {
            foreach (DB::table('channel_google_tokens')->get() as $t) {
                DB::table('youtube_channels')->where('id', $t->channel_id)->update([
                    'access_token'     => $t->access_token,
                    'refresh_token'    => $t->refresh_token,
                    'token_expires_at' => $t->expires_at,
                    'token_scope'      => $t->scope,
                    'token_type'       => $t->token_type,
                ]);
            }

            Schema::dropIfExists('channel_google_tokens');
        }
    }

    public function down(): void
    {
        Schema::create('channel_google_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('channel_id')->unique();
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('scope')->nullable();
            $table->string('token_type')->nullable();
            $table->timestamps();

            $table->foreign('channel_id')
                ->references('id')->on('youtube_channels')
                ->onDelete('cascade');
        });

        DB::statement(<<<'SQL'
            INSERT INTO channel_google_tokens
                (channel_id, access_token, refresh_token, expires_at, scope, token_type, created_at, updated_at)
            SELECT id, access_token, refresh_token, token_expires_at, token_scope, token_type, NOW(), NOW()
            FROM youtube_channels
            WHERE access_token IS NOT NULL
        SQL);

        Schema::table('youtube_channels', function (Blueprint $table) {
            $table->dropColumn([
                'access_token',
                'refresh_token',
                'token_expires_at',
                'token_scope',
                'token_type',
            ]);
        });
    }
};
