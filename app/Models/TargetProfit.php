<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TargetProfit extends Model
{
    protected $fillable = [
        'genre_id',
        'target_profit',
        'year',
        'month',
    ];

    const DELETED_AT = null;

}
