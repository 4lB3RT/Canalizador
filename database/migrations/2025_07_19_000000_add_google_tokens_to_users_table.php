<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('google_access_token')->nullable()->after('password');
            $table->text('google_refresh_token')->nullable()->after('google_access_token');
            $table->integer('google_expires_in')->nullable()->after('google_refresh_token');
            $table->string('google_scope')->nullable()->after('google_expires_in');
            $table->string('google_token_type')->nullable()->after('google_scope');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'google_access_token',
                'google_refresh_token',
                'google_expires_in',
                'google_scope',
                'google_token_type',
            ]);
        });
    }
};
