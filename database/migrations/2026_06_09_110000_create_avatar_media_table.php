<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avatar_media', function (Blueprint $table) {
            $table->uuid('avatar_id');
            $table->uuid('media_id');
            $table->string('type');
            $table->timestamps();

            $table->primary(['avatar_id', 'media_id']);
            $table->index('avatar_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avatar_media');
    }
};
