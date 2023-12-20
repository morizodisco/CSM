<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PastPromotionCode extends Model
{
    protected $fillable = [
        'promotion_code_id',
        'status_flag',
        'display_num',
        'year',
        'month',
    ];

    const UPDATED_AT = null;
    const DELETED_AT = null;

    public function promotion_code()
    {
        return $this->belongsTo(PromotionCode::class);
    }

}
