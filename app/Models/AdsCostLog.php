<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdsCostLog extends Model
{
    protected $fillable = [
        'genre_id',
        'cost',
        'log_at',
        'created_at',
    ];

    const UPDATED_AT = null;
}
