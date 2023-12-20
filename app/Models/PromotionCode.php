<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromotionCode extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'genre_id',
        'name',
        'unit_price',
        'note',
        'billing_address',
        'management_url',
        'display_num',
        'scraping_disabled',
        'status_flag',
    ];

    public function genres()
    {
        return $this->belongsTo('App\Models\Genre','genre_id','id');
    }

    public function promotion()
    {
        return $this->belongsTo('App\Models\Promotion','name');
    }

    public function past_codes()
    {
        return $this->hasMany(PastPromotionCode::class);
    }

}
