<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('avatars', function (Blueprint $table) {
            $table->string('platform_id')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('avatars', function (Blueprint $table) {
            $table->dropColumn('platform_id');
        });
    }
};
