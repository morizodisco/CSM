<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ConfirmTotal extends Model
{

    protected $fillable = [
        'year',
        'month',
        'genre_id',
        'add_cost',
        'cpc',
        'mcpa',
        'is_num',
        'top_part',
        'best_part',
    ];

    const DELETED_AT = null;
}
