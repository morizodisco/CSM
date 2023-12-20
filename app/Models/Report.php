<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'genre_id',
        'report_num',
        'start_date',
        'end_date',
        'rate_start_date',
        'rate_end_date',
        'minutes',
        'report_date',
    ];
}
