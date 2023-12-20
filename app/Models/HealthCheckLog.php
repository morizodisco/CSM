<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class HealthCheckLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'health_check_list_id',
        'open_time',
        'close_time',
        'drawing_time',
        'status_flag',
    ];

    public function health_check_list()
    {
        return $this->belongsTo(HealthCheckList::class);
    }

}
