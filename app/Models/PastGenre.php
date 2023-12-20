<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PastGenre extends Model
{
    protected $fillable = [
        'genre_id',
        'status_flag',
        'year',
        'month',
    ];

    const UPDATED_AT = null;
    const DELETED_AT = null;

}
