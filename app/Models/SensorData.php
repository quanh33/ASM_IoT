<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Cache
 *
 * @property string $temp
 * @property string $humi
 * @property string $light
 * @property Carbon|null $created_at
 *
 * @package App\Models
 */

class SensorData extends Model
{
    protected $table = 'sensor_data';

    protected $fillable = [
        'temp',
        'humi',
        'light'
    ];
}
