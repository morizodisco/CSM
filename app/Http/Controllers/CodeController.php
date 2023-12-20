<?php

namespace App\Http\Controllers;

use App\Models\PromotionCode;
use App\Models\Genre;
use App\Models\Promotion;
use App\Models\PastPromotionCode;
use App\Models\UserGenre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use DateTime;

class CodeController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {

        $data = $request->all();

        //ボタンのname値による条件分岐
        if ($request->input('update')){

            DB::beginTransaction();
            try {
                $data['status_flag'] = (!empty($request->status_flag)) ? '1' : '0';

                $genre = Genre::find($request->genre_id);
                $promotion = Promotion::find($request->name);
                $data['code'] = ($promotion->code ?? '') .' & '. ($genre->media_id ?? '');

                $db = PromotionCode::firstOrCreate(['id' => $request->id]);

                if (!empty($promotion->code) && !empty($genre->media_id)) $db->fill($data)->save();

                DB::commit();
            } catch (\PDOException $e) {
                DB::rollBack();
            }
            return redirect()->back();

        }

        $select_genre = null;
        if (isset($_GET['select_genre']) && $_GET['select_genre'] != '0') {
            $select_genre = $_GET['select_genre'];
        }

        //ボタンのname値による条件分岐
        if ($request->has('action') && $request->action == 'code_update') {

            DB::beginTransaction();
            try {
                $data['status_flag'] = (!empty($request->status_flag)) ? '1' : '0';
                $data['scraping_disabled'] = (isset($request->scraping_disabled)) ? '1' : '0';

                $db = PromotionCode::find($data['id']);
                $db->fill($data)->save();

                DB::commit();
            } catch (\PDOException $e) {
                DB::rollBack();
            }
            return redirect()->back();

        }

        $genre_pre = Genre::all();
        $genre_all = $genre_pre->keyBy('media_id')->toArray();
        $promotion_pre = Promotion::all();
        $promotion_all = $promotion_pre->keyBy('code')->toArray();

        $not_resist_code = PromotionCode::orWhereNull('genre_id')->orWhereNull('name')->get();
        if (!empty($not_resist_code)) {
            $explode = $not_resist_code->keyBy('code')->toArray();
            foreach ($explode as $key => $val) {
                if (is_null($val['code'])) continue;
                $array = explode(" & ", $val['code']);

                $db = PromotionCode::find($val['id']);
                $db->genre_id = (isset($genre_all[$array[1]]['id'])) ? $genre_all[$array[1]]['id'] : null;
                $db->name = (isset($promotion_all[$array[0]]['id'])) ? $promotion_all[$array[0]]['id'] : null;

                $db->save();
            }
        }

        if(Auth::user()->authority_id == 2){
            $user_id = Auth::user()->id;

            $available_genre = Genre::where('deleted_at', null)->when($user_id, function ($query, $user_id) {
                return $query->whereHas('genres', function (Builder $query) use ($user_id) {
                    $query->where('user_id', $user_id)->where(function ($query) {
                        $query->where('category_type', 2)->orWhere('category_type', 1);
                    });
                });
            })->with('promotion_codes')->whereHas('promotion_codes', function($query){
                $query->whereExists(function($query){
                    return $query;
                });
            })
                ->orderBy('display_num')->get();
        }else{
            $available_genre = Genre::where('deleted_at', null)->orderBy('display_num')
                ->with('promotion_codes')->whereHas('promotion_codes', function($query){
                    $query->whereExists(function($query){
                        return $query;
                    });
                })->get();
        }

        if (isset($select_genre)) {
            $view_codes = PromotionCode::where('genre_id',$select_genre)->orderBy('status_flag', 'desc')->orderBy('display_num')->get();
            $genre = $select_genre;
        } else {
            $view_codes = PromotionCode::where('genre_id',($available_genre[0]->id ?? 0))->orderBy('status_flag', 'desc')->orderBy('display_num')->get();
            $genre = $available_genre[0]->id ?? 0;
        }

        $past_month = [];
        for ($i = 1; $i <= 3; $i++){
            $past_month[$i]['name'] = date('Y年m月',strtotime('-'.$i.' month'));
            $past_month[$i]['year'] = date('Y',strtotime('-'.$i.' month'));
            $past_month[$i]['month'] = number_format(date('m',strtotime('-'.$i.' month')));
        }

        $past_code_all = PastPromotionCode::whereHas('promotion_code', function($q) use($genre){
            $q->where('genre_id',$genre);
        })->get();
        $past_code_all = $past_code_all->groupBy('promotion_code_id')->toArray();

        $past_code = [];
        foreach ($past_code_all as $key => $log_1){
            $past_code[$key] = collect($log_1);
            $past_code[$key] = $past_code[$key]->groupBy('year')->toArray();

            foreach ($past_code[$key] as $key2 => $log_2){
                $past_code[$key][$key2] = collect($log_2);
                $past_code[$key][$key2] = $past_code[$key][$key2]->keyBy('month')->toArray();

                foreach ($past_code[$key][$key2] as $key3=> $log_3){
                    $past_code[$key][$key2][$key3] = $log_3['status_flag'];
                }
            }
        }

        $promotions = $promotion_pre->keyBy('id')->toArray();

        //編集権限リスト(メンバー限定)
        if(Auth::user()->authority_id == 2){
            $user_id = Auth::user()->id;
            $edit_genres = UserGenre::where('user_id',$user_id)->where('category_type',2)->get()->pluck('genre_id')->toArray();
        }else{
            $edit_genres = '';
        }

        return view('code/top', [
            'past_code' => $past_code,
            'past_month' => $past_month,
            'codes' => $view_codes,
            'promotions' => $promotions,
            'select_genre' => $select_genre,
            'available_genre' => $available_genre,
            'available_promotion' => Promotion::where('status_flag', 1)->get(),
            'edit_genres' => $edit_genres,
            ]);
    }

}
