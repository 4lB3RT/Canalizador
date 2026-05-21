<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
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

        Schema::create('channel_oauth_states', function (Blueprint $table) {
            $table->id();
            $table->string('state', 100)->unique();
            $table->unsignedBigInteger('user_id');
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('scope')->nullable();
            $table->string('token_type')->nullable();
            $table->json('available_channels');
            $table->timestamp('expires_state_at');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_oauth_states');
        Schema::dropIfExists('channel_google_tokens');
    }
};
