<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scripts', function (Blueprint $table) {
            $table->string('language', 5)->default('es')->after('category');
        });

        Schema::table('videos', function (Blueprint $table) {
            $table->string('language', 5)->default('es')->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('scripts', function (Blueprint $table) {
            $table->dropColumn('language');
        });

        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn('language');
        });
    }
};
