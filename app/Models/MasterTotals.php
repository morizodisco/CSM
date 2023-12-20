<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MasterTotals extends Model
{
    protected $fillable = [
        'genre_id',
        'year',
        'month',
        'profit_last_month',
        'profit_rate',
        'avg_profit',
        'expected_profit',
        'profit',
    ];

    const DELETED_AT = null;

}
