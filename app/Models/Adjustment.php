<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Adjustment extends Model
{

    protected $fillable = [
        'year',
        'month',
        'genre_id',
        'add_cost',
        'code_id',
        'confirm_num',
        'confirm_price',
        'access',
    ];

    const DELETED_AT = null;
}
