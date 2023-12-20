<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CodeTotal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'genre_id',
        'date',
        'time',
        'add_cost',
        'cpc',
        'mcpa',
        'is_num',
        'top_part',
        'best_part',
        'manual_posted_id',
        'manual_posted_at',
        'created_at',
        'deleted_at',
    ];

    const UPDATED_AT = null;

    public $timestamps = false;
}
