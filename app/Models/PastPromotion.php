<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PastPromotion extends Model
{
    protected $fillable = [
        'promotion_id',
        'status_flag',
        'year',
        'month',
    ];

    const UPDATED_AT = null;
    const DELETED_AT = null;

}
