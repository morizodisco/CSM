<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\PastGenre;
use App\Models\PastPromotionCode;
use App\Models\PastPromotion;
use App\Models\Promotion;
use App\Models\PromotionCode;
use App\Models\ScrapingLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    public function check(Request $request)
    {
            $year = 2021;
            $month = 02;

        $last_month = date('m', strtotime('-1 month'));
        $code_ids = ScrapingLog::whereMonth('created_at',$last_month)->get()->pluck('promotion_code_id')->unique();

        $promotion_ids = [];
        foreach ($code_ids as $val){
            $promotion = PromotionCode::where('id',$val)->value('name');
            $promotion = isset($promotion) ? $promotion : 0;
            array_push($promotion_ids,$promotion);
        }
        $promotion_ids = array_unique($promotion_ids);

        dd($promotion_ids);

        $past_promotion = PastPromotion::where('status_flag', 1)->where('year', $year)->where('month', $month)->pluck('promotion_id');

        $code_list = PromotionCode::withTrashed()->where('genre_id', 11)->whereIn('name', $past_promotion)
            ->whereHas('past_codes', function ($q) use ($year, $month) {
                $q->where('past_promotion_codes.status_flag', 1)->where('past_promotion_codes.year', $year)->where('past_promotion_codes.month', $month);
            })->orderBy('display_num')->get();

        return view('test/check');
    }

}
