<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('avatars', function (Blueprint $table) {
            $table->renameColumn('platform_id', 'voice_id');
        });

        Schema::table('avatars', function (Blueprint $table) {
            $table->string('voice_id')->nullable()->after('user_id')->change();
        });
    }

    public function down(): void
    {
        Schema::table('avatars', function (Blueprint $table) {
            $table->renameColumn('voice_id', 'platform_id');
        });

        Schema::table('avatars', function (Blueprint $table) {
            $table->string('platform_id')->nullable()->after('description')->change();
        });
    }
};
