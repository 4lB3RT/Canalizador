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
        Schema::dropIfExists('avatar_image');
        
        Schema::create('avatar_image', function (Blueprint $table) {
            $table->foreignUuid('avatar_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('image_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->primary(['avatar_id', 'image_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avatar_image');
    }
};
