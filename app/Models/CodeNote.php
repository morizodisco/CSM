<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CodeNote extends Model
{
    protected $fillable = [
        'genre_id',
        'date',
        'year_month',
        'change_point',
        'consideration',
    ];

    const UPDATED_AT = null;
    const DELETED_AT = null;

}
