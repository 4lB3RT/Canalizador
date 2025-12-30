<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('channels', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('channel_brand')->nullable();
            $table->string('custom_url')->nullable();
            $table->timestamp('published_at');
            $table->string('thumbnail_url')->nullable();
            $table->string('country', 2)->nullable();
            $table->unsignedBigInteger('view_count')->default(0);
            $table->unsignedBigInteger('subscriber_count')->default(0);
            $table->unsignedBigInteger('video_count')->default(0);
            $table->string('privacy_status', 20)->default('public'); 
            $table->timestamps();
            
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
