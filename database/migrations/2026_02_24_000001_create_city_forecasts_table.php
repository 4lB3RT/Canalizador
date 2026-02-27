<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('city_forecasts', function (Blueprint $table) {
            $table->id();
            $table->string('city_name', 100);
            $table->string('municipality_code', 10);
            $table->date('forecast_date');
            $table->smallInteger('max_temperature');
            $table->smallInteger('min_temperature');
            $table->string('weather_state');
            $table->smallInteger('precipitation_probability');
            $table->string('snow_level', 20);
            $table->string('wind_direction', 5);
            $table->smallInteger('wind_speed');
            $table->smallInteger('wind_gust');
            $table->smallInteger('max_thermal_sensation');
            $table->smallInteger('min_thermal_sensation');
            $table->smallInteger('max_humidity');
            $table->smallInteger('min_humidity');
            $table->smallInteger('uv_index');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['municipality_code', 'forecast_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('city_forecasts');
    }
};
