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
            $table->unsignedBigInteger('user_id')->nullable()->after('script_id');
            $table->string('category')->nullable()->after('user_id');
            $table->string('title')->nullable()->after('category');
            $table->timestamp('updated_at')->nullable()->after('created_at');

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('scripts', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'category', 'title', 'updated_at']);
        });
    }
};
