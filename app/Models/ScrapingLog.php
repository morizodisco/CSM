<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScrapingLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'promotion_code_id',
        'imp',
        'access',
        'ctr',
        'occur_num',
        'occur_price',
        'confirm_num',
        'confirm_price',
        'cvr',
        'total',
        'confirm_price_check',
        'yesterday_check',
        'manual_posted_id',
        'manual_posted_at',
        'scraping_at',
        'created_at',
        'deleted_at',
    ];

    const UPDATED_AT = null;

    public $timestamps = false;

    public function PromotionCode()
    {
        return $this->belongsTo('App\Models\PromotionCode','promotion_code_id','id');
    }

    public function Genre()
    {
        return $this->belongsTo('App\Models\Genre','genre_id','id');
    }

    public function promotion()
    {
        return $this->hasOneThrough('App\Models\Promotion','App\Models\PromotionCode','id','id','promotion_code_id','name');
    }

}
