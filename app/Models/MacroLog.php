<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MacroLog extends Model
{
    protected $fillable = [
        'created_at',
    ];

    const UPDATED_AT = null;

    public $timestamps = false;
}
