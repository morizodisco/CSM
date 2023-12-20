<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HealthCheckList extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'check_url',
        'alert_emails',
        'remarks',
        'status_flag',
        'last_check_at',
    ];

    public function health_check_logs()
    {
        return $this->hasMany(HealthCheckLog::class);
    }

}
