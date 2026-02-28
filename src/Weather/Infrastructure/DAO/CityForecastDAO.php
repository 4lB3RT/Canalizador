<?php

declare(strict_types=1);

namespace Canalizador\Weather\Infrastructure\DAO;

use Illuminate\Database\Eloquent\Model;

class CityForecastDAO extends Model
{
    protected $table = 'city_forecasts';

    public $timestamps = false;

    protected $fillable = [
        'city_name',
        'municipality_code',
        'forecast_date',
        'max_temperature',
        'min_temperature',
        'weather_state',
        'precipitation_probability',
        'snow_level',
        'wind_direction',
        'wind_speed',
        'wind_gust',
        'max_thermal_sensation',
        'min_thermal_sensation',
        'max_humidity',
        'min_humidity',
        'uv_index',
        'summary',
    ];

    protected $casts = [
        'forecast_date' => 'date',
        'max_temperature' => 'integer',
        'min_temperature' => 'integer',
        'precipitation_probability' => 'integer',
        'wind_speed' => 'integer',
        'wind_gust' => 'integer',
        'max_thermal_sensation' => 'integer',
        'min_thermal_sensation' => 'integer',
        'max_humidity' => 'integer',
        'min_humidity' => 'integer',
        'uv_index' => 'integer',
    ];
}
