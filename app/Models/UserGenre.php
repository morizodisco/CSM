<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class UserGenre extends Model
{
    protected $fillable = [
        'user_id',
        'genre_id',
        'category_type',
    ];

    const UPDATED_AT = null;
}
