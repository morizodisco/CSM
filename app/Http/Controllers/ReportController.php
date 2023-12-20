<?php

namespace App\Http\Controllers;

use App\Models\Adjustment;
use App\Models\Genre;
use App\Models\Report;
use App\Models\TargetProfit;
use App\Models\UserGenre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use App\Models\PromotionCode;
use App\Models\ScrapingLog;
use App\Models\CodeTotal;
use App\Models\CodeNote;
use App\Models\ConfirmTotal;
use App\Models\User;
use App\Models\MasterTotals;
use App\Models\PastGenre;
use App\Models\PastPromotion;
use App\Models\PastPromotionCode;
use \DateTimeImmutable;
use \DateInterval;
use \DatePeriod;

class ReportController extends Controller
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

        if (isset($request->select_month)) {
            $date = explode("-", $request->select_month);
            $year = $date[0];
            $month = $date[1];
        } else {
            $year = date('Y');
            $month = date('m');
        }

        if ($request->has('table_update')) {
            DB::beginTransaction();
            try {
                foreach ($request->target_profit as $key => $target_profit) {
                    $profit_data = TargetProfit::firstOrCreate(['genre_id' => $key, 'year' => $year, 'month' => $month]);
                    $profit_data->target_profit = (!empty($target_profit)) ? str_replace([','], '', $target_profit) : null;
                    $profit_data->save();
                }
                DB::commit();
            } catch (\PDOException $e) {
                DB::rollBack();
            }
            return redirect()->back();
        }

        if($request->user_id != null){
            $user_id = $request->user_id;
        }else{
            if(Auth::user()->authority_id == 2){
                $user_id = Auth::user()->id;
            }else{
                $user_id = null;
            }
        }

        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : null;

        //選択した月の日数を取得
        $start = new DateTimeImmutable(date($year . '-' . $month . '-1') . 'T00:00'); // 開始日時
        $end = new DateTimeImmutable('last day of ' . $year . '-' . $month . '23:59:59'); // 終了日時
        $interval = new DateInterval('P1D'); // 1日間隔であることを表す（Pは期間（period）を、Dは日（day）を表す）
        $period = new DatePeriod($start, $interval, $end);

        $day_count = $period->end->format('d');

        $select_month = [];
        //今日から1年前までのselect( /月 )
        for ($i = 0; $i < 13; $i++) {
            $select_month[$i] = date('Y-m', strtotime(date('Y-m-01') . " -" . $i . " month"));
        }

        if ($year . $month == date('Ym')) {
            $genre_list = Genre::where('status_flag', 1)->when($user_id, function ($query, $user_id) {
                return $query->whereHas('genres', function (Builder $query) use ($user_id) {
                    $query->where('user_id', $user_id)->where(function ($query) {
                        $query->where('category_type', 2)->orWhere('category_type', 1);
                    });
                });
            })->when($keyword, function ($query, $keyword) {
                return $query->where('name', 'LIKE', '%'.$keyword.'%');
            })->with('promotion_codes')->whereHas('promotion_codes', function($query){
                $query->whereExists(function($query){
                    return $query;
                });
            })
                ->orderBy('display_num')->get();

        } else {

            //クロン作動しなかった時用の回避
            $past_date = PastGenre::where('year',$year)->where('month',$month)->get();
            if($past_date->isEmpty()){
                $genre_list = Genre::where('status_flag', 1)->when($user_id, function ($query, $user_id) {
                    return $query->whereHas('genres', function (Builder $query) use ($user_id) {
                        $query->where('user_id', $user_id)->where(function ($query) {
                            $query->where('category_type', 2)->orWhere('category_type', 1);
                        });
                    });
                })->with('promotion_codes')->whereHas('promotion_codes', function($query){
                    $query->whereExists(function($query){
                        return $query;
                    });
                })->orderBy('display_num')->get();
            }else{
                $genre_list = Genre::withTrashed()->whereHas('past_genres', function ($q) use ($year, $month) {
                    $q->where('past_genres.status_flag', 1)->where('past_genres.year', $year)->where('past_genres.month', $month);
                })->when($user_id, function ($query, $user_id) {
                    return $query->whereHas('genres', function (Builder $query) use ($user_id) {
                        $query->where('user_id', $user_id)->where(function ($query) {
                            $query->where('category_type', 2)->orWhere('category_type', 1);
                        });
                    });
                })->when($keyword, function ($query, $keyword) {
                    return $query->where('name', 'LIKE', '%'.$keyword.'%');
                })->with('promotion_codes')->whereHas('promotion_codes', function($query){
                    $query->whereExists(function($query){
                        return $query;
                    });
                })
                    ->orderBy('display_num')->get();
            }
        }

        //閲覧可能ジャンル
        $user_genre_list = [];
        foreach(Auth::user()->genres as $user_genre){
            $user_genre_list[] = $user_genre->genre_id;
        }

        //右表固定部分
        $all_totals = MasterTotals::where('genre_id', '!=', 0)->where('year', $year)->where('month', $month)->get();
        $genre_total = $all_totals->keyBy('genre_id');

        // 左表の集計
        $code_total = [];
        $aggregated = [];
        $confirm_total = [];
        $all_adjustments = [];
        foreach ($genre_list as &$genre) {
            //ジャンルごとの集計
            $code_total[$genre->id] = ['confirm_num' => 0, 'confirm_price' => 0, 'add_cost' => 0, 'profit' => 0];
            // 日別集計
            foreach ($period as $day) {
                if (!isset($aggregated[$day->format('d')])) $aggregated[$day->format('d')] = ['sales' => 0, 'cost' => 0, 'profit' => 0];
                $aggregated[$day->format('d')]['sales'] += $sales = $genre->get_aggregated($year, $month)[$day->format('d')]['confirm_price'] ?? 0;
                $aggregated[$day->format('d')]['cost'] += $cost = (($genre->get_total_aggregated($year, $month)[$day->format('d')]['add_cost'] ?? 0));
                $aggregated[$day->format('d')]['profit'] += $profit = $sales - ($cost * 1.1);

                $code_total[$genre->id]['confirm_num'] += $genre->get_aggregated($year, $month)[$day->format('d')]['confirm_num'] ?? 0;
                $code_total[$genre->id]['confirm_price'] += $genre->get_aggregated($year, $month)[$day->format('d')]['confirm_price'] ?? 0;
                $code_total[$genre->id]['add_cost'] += $genre->get_total_aggregated($year, $month)[$day->format('d')]->add_cost ?? 0;
            }

            //今月の場合今日の数値を除外する
            $today = ['confirm_num' => 0, 'confirm_price' => 0, 'add_cost' => 0];
            if ($year == date('Y') && $month == date('m')) {
                $today['confirm_num'] = $genre->get_aggregated($year, $month)[date('d')]['confirm_num'] ?? 0;
                $today['confirm_price'] = $genre->get_aggregated($year, $month)[date('d')]['confirm_price'] ?? 0;
                $today['add_cost'] = $genre->get_total_aggregated($year, $month)[date('d')]->add_cost ?? 0;
                $code_total[$genre->id]['confirm_num'] = $code_total[$genre->id]['confirm_num'] - $today['confirm_num'];
                $code_total[$genre->id]['confirm_price'] = $code_total[$genre->id]['confirm_price'] - $today['confirm_price'];
                $code_total[$genre->id]['add_cost'] = $code_total[$genre->id]['add_cost'] - $today['add_cost'];
            }

            if ($code_total[$genre->id]['add_cost']) {
                $code_total[$genre->id]['profit'] = $code_total[$genre->id]['confirm_price'] - ($code_total[$genre->id]['add_cost'] * 1.1);
            } else {
                $code_total[$genre->id]['profit'] = $code_total[$genre->id]['confirm_price'];
            }

            $adjustments = Adjustment::where('year', $year)->where('month', $month)->where('genre_id', $genre->id)
                ->where('code_id', '!=', 0)->get()->keyBy('code_id');

            //右表の集計結果
            $all_adjustments[$genre->id] = ['confirm_price' => 0, 'confirm_num' => 0, 'add_cost' => 0, 'profit' => 0];
            $confirm_total[$genre->id] = ConfirmTotal::where('year', $year)->where('month', $month)
                ->where('genre_id', $genre->id)->first();

            if (isset($adjustments)) {
                foreach ($adjustments as $log_1) {
                    $all_adjustments[$genre->id]['confirm_num'] += $log_1->confirm_num;
                    $all_adjustments[$genre->id]['confirm_price'] += $log_1->confirm_price;
                }
            }
            $all_adjustments[$genre->id]['add_cost'] = $confirm_total[$genre->id]['add_cost'];
            $all_adjustments[$genre->id]['profit'] = $all_adjustments[$genre->id]['confirm_price'] - ($confirm_total[$genre->id]['add_cost'] * 1.1);
        }

        //左表の集計結果
        $all_aggregated = ['sales' => 0, 'cost' => 0, 'profit' => 0, 'profit_rate' => 0, 'roas' => 0];
        foreach ($aggregated as $key => $log_1) {
            if ($year == date('Y') && $month == date('m')) {
                if ($key == date('d')) {
                    continue;
                }
            }
            $all_aggregated['sales'] += $log_1['sales'];
            $all_aggregated['cost'] += $log_1['cost'];
            $all_aggregated['profit'] += $log_1['profit'];
        }

        //上部固定部分の数値取得
        if ($month == 1) {
            $last_year = $year - 1;
            $last_month = 12;
        } else {
            $last_year = $year;
            $last_month = $month - 1;
        }
        $select_genre = $genre_list->keyBy('id')->keys();

        //ユーザー絞り込みの有無で分岐
        if($user_id != null){
            $user_genre = UserGenre::where('user_id', $user_id)->where('category_type', 3)->get()->keyBy('genre_id')->keys();
            $past_genre_status = PastGenre::where('year',$last_year)->where('month',$last_month)->where('status_flag',1)
                ->whereIn('genre_id',$user_genre)->get()->keyBy('genre_id')->keys();

            $select_total = MasterTotals::whereIn('genre_id', $select_genre)->where('year', $year)->where('month', $month)->get();
            $main_total = (object)['profit_last_month'=>0, 'profit_rate'=>0, 'avg_profit'=>0, 'expected_profit'=>0, 'profit'=>0];
            foreach ($select_total as $total){
                $main_total->profit = $main_total->profit + $total->profit;
                $main_total->avg_profit = $main_total->avg_profit + $total->avg_profit;
                $main_total->expected_profit = $main_total->expected_profit + $total->expected_profit;
            }
            $last_total_data = MasterTotals::whereIn('genre_id', $past_genre_status)->where('year', $last_year)->where('month', $last_month)->get();
            $last_total = ['profit'=>0, 'profit_rate'=>0];
            foreach ($last_total_data as $data){
                $last_total['profit'] = $last_total['profit'] + $data->profit;
            }
            $last_total['profit_rate'] = (!empty($last_total['profit'])) ? ($main_total->expected_profit ?? 0) / $last_total['profit'] * 100 : 0;

        }else{
            $main_total = MasterTotals::where('genre_id', 0)->where('year', $year)->where('month', $month)->first();

            $last_total_data = MasterTotals::where('genre_id', 0)->where('year', $last_year)->where('month', $last_month)->first();
            $last_total['profit'] = $last_total_data->profit ?? 0;
            $last_total['profit_rate'] = (!empty($last_total['profit'])) ? ($main_total->expected_profit ?? 0) / $last_total['profit'] * 100 : 0;
        }

        //右表の目標利益
        $target_profits = TargetProfit::where('year',$year)->where('month',$month)->whereIn('genre_id',$select_genre)
            ->get()->keyBy('genre_id');
        $all_target = 0;
        foreach ($target_profits as $profit){
            $all_target += $profit->target_profit;
        }

        //ユーザーリスト
        $users = User::whereHas('genres', function (Builder $query) {
            $query->where('category_type', 3);
        })->get();

        return view('report/top', [
            'keyword' => $keyword,
            'period' => $period,
            'select_month' => $select_month,
            'users' => $users,
            'genre_list' => $genre_list,
            'aggregated' => $aggregated,
            'year' => $year,
            'month' => $month,
            'day_count' => $day_count,
            'main_total' => $main_total,
            'genre_total' => $genre_total,
            'last_total' => $last_total,
            'code_total' => $code_total,
            'confirm_total' => $confirm_total,
            'all_adjustments' => $all_adjustments,
            'all_aggregated' => $all_aggregated,
            'target_profits' => $target_profits,
            'all_target' => $all_target,
            'user_genre_list' => $user_genre_list,
        ]);
    }


    public function detail(Request $request, $genre_id = null)
    {
        //権限がなかったらリダイレクト

        if(Auth::user()->authority_id == 2){
            $authority_type = UserGenre::where('user_id',Auth::user()->id)->where('genre_id',$genre_id)->first('category_type');

            if($authority_type == null){
                return redirect('/report');
            }else{
                $authority_type = $authority_type->category_type;
            }
        }else{
            $authority_type = 2;
        }

        // 表を更新
        if ($request->has('table_update')) {
            DB::beginTransaction();
            try {

                //左表時間別ログ
                if (isset($request->fill_out)) {
                    foreach ($request->fill_out as $day => $log_1) {
                        foreach ($log_1 as $hour => $log_2) {
                            $target_date = $request->year . '-' . $request->month . '-' . $day;

                            $code_total = CodeTotal::where('genre_id', $genre_id)->whereDate('created_at', $target_date)
                                ->where('time', $hour)->first();

                            if (!empty($log_2['add_cost']) || !empty($log_2['cpc']) || !empty($log_2['mcpa']) || !empty($log_2['is_num'])
                                || !empty($log_2['top_part']) || !empty($log_2['best_part'])) {


                                if (empty($code_total)) {
                                    $code_total = new CodeTotal();
                                    $code_total->genre_id = $genre_id;
                                    $code_total->date = $day;
                                    $code_total->time = $hour;
                                    $code_total->manual_posted_at = date('Y-m-d H:i:s');
                                    $code_total->created_at = $target_date . ' ' . $hour . ':59:59';
                                    $code_total->add_cost = (isset($log_2['add_cost'])) ? str_replace([','], '', $log_2['add_cost']) : null;
                                    $code_total->cpc = (isset($log_2['cpc'])) ? str_replace([','], '', $log_2['cpc']) : null;
                                    $code_total->mcpa = (isset($log_2['mcpa'])) ? str_replace([','], '', $log_2['mcpa']) : null;
                                    $code_total->is_num = $log_2['is_num'] ?? NULL;
                                    $code_total->top_part = $log_2['top_part'] ?? NULL;
                                    $code_total->best_part = $log_2['best_part'] ?? NULL;
                                } else {
                                    $code_total->add_cost = (isset($log_2['add_cost'])) ? str_replace([','], '', $log_2['add_cost']) : $code_total->add_cost;
                                    $code_total->cpc = (isset($log_2['cpc'])) ? str_replace([','], '', $log_2['cpc']) : $code_total->cpc;
                                    $code_total->mcpa = (isset($log_2['mcpa'])) ? str_replace([','], '', $log_2['mcpa']) : $code_total->mcpa;
                                    $code_total->is_num = $log_2['is_num'] ?? $code_total->is_num;
                                    $code_total->top_part = $log_2['top_part'] ?? $code_total->top_part;
                                    $code_total->best_part = $log_2['best_part'] ?? $code_total->best_part;
                                }

                                $code_total->save();

                            }
                        }
                    }
                }

                //左表メモ部分
                foreach ($request->textarea as $day => $log_1) {
                    $target_date = $request->year . '-' . $request->month . '-' . $day;
                    $code_note = CodeNote::where('genre_id', $genre_id)->where('date', $day)
                        ->where('year_month', $target_date)->first();

                    if (empty($code_note)) {
                        $code_note = new CodeNote();
                        $code_note->genre_id = $genre_id;
                        $code_note->date = $day;
                    }
                    $code_note->year_month = $target_date;
                    $code_note->change_point = $log_1['change_point'] ?? NULL;
                    $code_note->consideration = $log_1['consideration'] ?? NULL;

                    $code_note->save();
                }

                //右表時間別ログ
                if (isset($request->media_figures)) {
                    foreach ($request->media_figures as $code => $log_1) {
                        foreach ($log_1 as $day => $log_2) {
                            foreach ($log_2 as $hour => $log_3) {

                                if (isset($log_3['confirm_num']) || isset($log_3['access']) || isset($log_3['confirm_price'])) {

                                    $target_date = $request->year . '-' . $request->month . '-' . $day . ' ' . $hour . ':59:59';
                                    $scraping_log = ScrapingLog::where('created_at', $target_date)->where('promotion_code_id', $code)->orderBy('id', 'DESC')->first();
                                    if (empty($scraping_log)) {
                                        $scraping_log = new ScrapingLog();
                                        $scraping_log->promotion_code_id = $code;
                                        $scraping_log->manual_posted_at = date('Y-m-d H:i:s');
                                        $scraping_log->created_at = $target_date;
                                        $scraping_log->confirm_num = (isset($log_3['confirm_num'])) ? str_replace([','], '', $log_3['confirm_num']) : null;
                                        $scraping_log->access = (isset($log_3['access'])) ? str_replace([','], '', $log_3['access']) : null;
                                        $scraping_log->confirm_price = (isset($log_3['confirm_price'])) ? str_replace([','], '', $log_3['confirm_price']) : null;
                                    } else {
                                        $scraping_log->confirm_num = (isset($log_3['confirm_num'])) ? str_replace([','], '', $log_3['confirm_num']) : $scraping_log->confirm_num;
                                        $scraping_log->access = (isset($log_3['access'])) ? str_replace([','], '', $log_3['access']) : $scraping_log->access;
                                        $scraping_log->confirm_price = (isset($log_3['confirm_price'])) ? str_replace([','], '', $log_3['confirm_price']) : $scraping_log->confirm_price;
                                    }
                                    $scraping_log->save();

                                }
                            }
                        }
                    }
                }

                //右表theadのメモ
                if (isset($request->promotion_note)) {
                    foreach ($request->promotion_note as $id => $log_1) {
                        $promotion_note = PromotionCode::find($id);
                        $promotion_note->note = $log_1['note'];
                        $promotion_note->save();
                    }
                }

                DB::commit();
            } catch (\PDOException $e) {
                DB::rollBack();
            }

            DB::beginTransaction();
            try {

                //右表の確定数値
                if ($request->adjustment) {
                    foreach ($request->adjustment as $code_id => $log) {
                        if (!empty($log['confirm_num']) || !empty($log['access']) || !empty($log['confirm_price'])) {
                            $adjustment = Adjustment::firstOrCreate(['genre_id' => $genre_id, 'code_id' => $code_id,
                                'year' => $request->year, 'month' => $request->month]);
                            $adjustment->confirm_num = $log['confirm_num'];
                            $adjustment->access = $log['access'];
                            $adjustment->confirm_price = (isset($log['confirm_price'])) ? str_replace(',', '', $log['confirm_price']) : null;

                            $adjustment->save();
                        }
                    }
                }

                //左表の確定数値
                if ($request->confirm_total) {
                    $c_t = $request->confirm_total;
                    if (!empty($c_t['add_cost']) || !empty($c_t['cpc']) || !empty($c_t['mcpa']) || $c_t['is_num'] != '0.00'
                        || $c_t['top_part'] != '0.00' || $c_t['best_part'] != '0.00') {
                        $confirm_total = ConfirmTotal::firstOrCreate(['genre_id' => $genre_id, 'year' => $request->year, 'month' => $request->month]);
                        $confirm_total->add_cost = str_replace(',', '', $c_t['add_cost']);
                        $confirm_total->cpc = str_replace(',', '', $c_t['cpc']);
                        $confirm_total->mcpa = str_replace(',', '', $c_t['mcpa']);
                        $confirm_total->is_num = $c_t['is_num'];
                        $confirm_total->top_part = $c_t['top_part'];
                        $confirm_total->best_part = $c_t['best_part'];
                        $confirm_total->save();
                    }
                }

                DB::commit();
            } catch (\PDOException $e) {
                DB::rollBack();
            }

            return redirect()->back();


        }

        // 議事録の期間設定を更新
        if ($request->has('report_id')) {

            DB::beginTransaction();
            try {
                $report = Report::find($request->report_id);

                $report->start_date = $request->start_date ?? NULL;
                $report->end_date = $request->end_date ?? NULL;
                $report->rate_start_date = $request->rate_start_date ?? NULL;
                $report->rate_end_date = $request->rate_end_date ?? NULL;

                $report->save();

                DB::commit();
            } catch (\PDOException $e) {
                DB::rollBack();
            }
            return redirect()->back();

        }


        //// 情報取得 ////


        if (isset($request->select_month)) {
            $date = explode("-", $request->select_month);
            $year = $date[0];
            $month = $date[1];
        } else {
            $year = date('Y');
            $month = date('m');
        }

        //選択した月の日数を取得
        $start = new DateTimeImmutable(date($year . '-' . $month . '-1') . 'T00:00'); // 開始日時
        $end = new DateTimeImmutable('last day of ' . $year . '-' . $month . '23:59:59'); // 終了日時
        $interval = new DateInterval('P1D'); // 1日間隔であることを表す（Pは期間（period）を、Dは日（day）を表す）
        $period = new DatePeriod($start, $interval, $end);

        //表示コードの取得
        $ids = [];
        if ($year . $month == date('Ym')) { //今月かの判定
            $code_list = PromotionCode::where('genre_id', $genre_id)->where('status_flag', 1)
                ->whereHas('promotion', function ($q) {
                    $q->where('promotions.status_flag', 1);
                })->orderBy('display_num')->get();
        } else {
            //過去のステータスログがあるかチェック
            $past_log = PastPromotionCode::where('year', $year)->where('month', $month)->get();

            if ($past_log->isEmpty()) {
                $code_list = PromotionCode::where('genre_id', $genre_id)->where('status_flag', 1)
                    ->whereHas('promotion', function ($q) {
                        $q->where('promotions.status_flag', 1);
                    })->orderBy('display_num')->get();
            } else {
                $past_promotion = PastPromotion::where('status_flag', 1)->where('year', $year)->where('month', $month)->pluck('promotion_id');
                $code_list = PromotionCode::withTrashed()->where('genre_id', $genre_id)->whereIn('name', $past_promotion)
                    ->whereHas('past_codes', function ($q) use ($year, $month) {
                        $q->where('past_promotion_codes.status_flag', 1)->where('past_promotion_codes.year', $year)->where('past_promotion_codes.month', $month);
                    })->orderBy('display_num')->get();
            }
        }
        $code_id = $code_list->pluck('id');

        //選択ジャンルの選択月のログ取得
        $all_logs = ScrapingLog::whereIn('promotion_code_id', $code_id)->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)
            ->get();
        $code_logs = $all_logs->groupBy('promotion_code_id')->toArray();
        $promotion_codes = PromotionCode::where('genre_id', $genre_id)->where('status_flag', 1)->get()->keyBy('id')->toArray();

        //dd($code_logs[1073]);

        //日付補正、日と時間のカラム追加
        foreach ($code_logs as $key => $code_log) {
            foreach ($code_log as $key2 => $val) {
                // コード単価が設定済みの場合（ゼロで無い場合）は、単価*登録数で売上を再設定する
                $unit_price = $promotion_codes[$val['promotion_code_id']]['unit_price'] ?? 0;
                if (!empty($val['confirm_price'] || $val['confirm_price_check'] == 1)) {
                    $code_logs[$key][$key2]['confirm_price'] = $val['confirm_price'];
                } else {
                    if (!empty($unit_price)) $code_logs[$key][$key2]['confirm_price'] = $unit_price * $val['confirm_num'];
                }

                $code_logs[$key][$key2]['created_at'] = date('Y-m-d H:i:s', strtotime($val['created_at']));
                $code_logs[$key][$key2]['day'] = date('d', strtotime($val['created_at']));
                $code_logs[$key][$key2]['hour'] = date('H', strtotime($val['created_at']));
            }
        }



        //日付でグループ化
        foreach ($code_logs as $key => $code_log) {
            $code_logs[$key] = collect($code_log);
            $code_logs[$key] = $code_logs[$key]->groupBy('day')->toArray();
        }
        //時間でグループ化
        foreach ($code_logs as $key => $code_log) {
            foreach ($code_log as $key2 => $log) {
                $code_logs[$key][$key2] = collect($log);
                $code_logs[$key][$key2] = $code_logs[$key][$key2]->groupBy('hour')->toArray();
            }
        }
        //各時間最新の値のみ抽出 (右表の情報出し完了)
        foreach ($code_logs as $key => $code_log) {
            foreach ($code_log as $key2 => $log) {
                foreach ($log as $key3 => $time) {
                    $code_logs[$key][$key2][$key3] = array_multisort(array_column($time, 'created_at'), SORT_DESC, $time);
                    $code_logs[$key][$key2][$key3] = $time[0];
                    if ($time[0]['confirm_num'] != 0 && $time[0]['access'] != 0) {
                        $code_logs[$key][$key2][$key3]['cvr'] = round($time[0]['confirm_num'] / $time[0]['access'], 3) * 100;
                    } else {
                        $code_logs[$key][$key2][$key3]['cvr'] = 0;
                    }

                }
            }
        }

        //右表の各レコードの一番下にその月の合計を出す
        //23は23時のログ(その日の最後)という意味

        $code_total = [];

        if ($year . '-' . $month == date('Y-m')) {
            $days = date('d');
        } else {
            $days = date('t', strtotime(date('Y-m')));
        }
        foreach ($code_logs as $code => $log_1) {
            $code_total[$code] = ['confirm_num' => 0, 'access' => 0, 'cvr' => 0, 'confirm_price' => 0];
            foreach ($log_1 as $day => $log_2) {
                if (isset($log_2[23]['confirm_num'])) {
                    $code_total[$code]['confirm_num'] += $log_2[23]['confirm_num'];
                }
                if (isset($log_2[23]['access'])) {
                    $code_total[$code]['access'] += $log_2[23]['access'];
                }
                if (isset($log_2[23]['confirm_price'])) {
                    $code_total[$code]['confirm_price'] += $log_2[23]['confirm_price'];
                }
            }
            if ($code_total[$code]['confirm_num'] && $code_total[$code]['access']) {
                $code_total[$code]['cvr'] = round($code_total[$code]['confirm_num'] / $code_total[$code]['access'], 3) * 100;
            } else {
                $code_total[$code]['cvr'] = 0;
            }
        }

        //右表の合計(左表に表示)
        $total = [];
        foreach ($code_logs as $key => $code_log) {
            foreach ($code_log as $key2 => $log) {
                foreach ($log as $key3 => $time) {
                    if (isset($total[$key2][$key3]) == false) {
                        $total[$key2][$key3] = ['access' => 0, 'confirm_num' => 0, 'confirm_price' => 0, 'cvr' => 0];
                    }
                    $c_l = $code_logs[$key][$key2][$key3];
                    if ($c_l['access']) {
                        $total[$key2][$key3]['access'] += $c_l['access'];
                    }
                    if ($c_l['confirm_num']) {
                        $total[$key2][$key3]['confirm_num'] += $c_l['confirm_num'];
                    }
                    if ($c_l['confirm_price']) {
                        $total[$key2][$key3]['confirm_price'] += $c_l['confirm_price'];
                    }
                    if ($total[$key2][$key3]['confirm_num'] && $total[$key2][$key3]['access']) {
                        $total[$key2][$key3]['cvr'] = round($total[$key2][$key3]['confirm_num'] / $total[$key2][$key3]['access'], 3) * 100;
                    } else {
                        $total[$key2][$key3]['cvr'] = 0;
                    }

                }
            }
        }


        //左表打ち込みした数値を表示
        $all_write_logs = CodeTotal::where('genre_id', $genre_id)->whereYear('created_at', '=', $year)->whereMonth('created_at', '=', $month)->get();
        $write_logs = $all_write_logs->groupBy('date')->toArray();

        //時間でグループ化
        foreach ($write_logs as $key => $logs) {
            $write_logs[$key] = collect($logs);
            $write_logs[$key] = $write_logs[$key]->groupBy('time')->toArray();
        }
        foreach ($write_logs as $key => $logs) {
            foreach ($logs as $key2 => $log) {
                $write_logs[$key][$key2] = $log[0];
                $t = (isset($total[$key][$key2])) ? $total[$key][$key2] : null;
                $add_cost = $write_logs[$key][$key2]['add_cost'];
                $write_logs[$key][$key2]['profit'] = (isset($t['confirm_price'])) ? $t['confirm_price'] - ($add_cost * 1.1) : null;
                $write_logs[$key][$key2]['roi'] = (isset($write_logs[$key][$key2]['profit']) && !empty($add_cost)) ? ($write_logs[$key][$key2]['profit'] / ($add_cost * 1.1)) * 100 : null;
                $write_logs[$key][$key2]['cpa'] = (isset($add_cost) && !empty($t['confirm_num'])) ? ($add_cost * 1.1) / ($t['confirm_num']) : null;
            }
        }

        $all_total = ['confirm_num' => 0, 'access' => 0, 'cvr' => 0, 'confirm_price' => 0, 'add_cost' => 0,
            'cpc' => 0, 'mcpa' => 0, 'is_num' => 0, 'top_part' => 0, 'best_part' => 0, 'profit' => 0, 'roi' => 0, 'cpa' => 0];
        foreach ($total as $day => $log_1) {
            if (isset($log_1[23]['confirm_num'])) {
                $all_total['confirm_num'] += $log_1[23]['confirm_num'];
            }
            if (isset($log_1[23]['access'])) {
                $all_total['access'] += $log_1[23]['access'];
            }
            if (isset($log_1[23]['cvr'])) {
                $all_total['cvr'] += $log_1[23]['cvr'];
            }
            if (isset($log_1[23]['confirm_price'])) {
                $all_total['confirm_price'] += $log_1[23]['confirm_price'];
            }
        }
        if ($all_total['confirm_num'] && $all_total['access']) {
            $all_total['cvr'] = round($all_total['confirm_num'] / $all_total['access'], 3) * 100;
        }

        foreach ($write_logs as $day => $log_1) {
            if (isset($log_1[23]['add_cost'])) {
                $all_total['add_cost'] += $log_1[23]['add_cost'];
            }
            if (isset($log_1[23]['cpc'])) {
                $all_total['cpc'] += $log_1[23]['cpc'];
            }
            if (isset($log_1[23]['mcpa'])) {
                $all_total['mcpa'] += $log_1[23]['mcpa'];
            }
            if (isset($log_1[23]['is_num'])) {
                $all_total['is_num'] += $log_1[23]['is_num'];
            }
            if (isset($log_1[23]['top_part'])) {
                $all_total['top_part'] += $log_1[23]['top_part'];
            }
            if (isset($log_1[23]['best_part'])) {
                $all_total['best_part'] += $log_1[23]['best_part'];
            }
            if (isset($log_1[23]['profit'])) {
                $all_total['profit'] += $log_1[23]['profit'];
            }
            if (isset($log_1[23]['roi'])) {
                $all_total['roi'] += $log_1[23]['roi'];
            }
            if (isset($log_1[23]['cpa'])) {
                $all_total['cpa'] += $log_1[23]['cpa'];
            }
        }

        $all_total['roi'] = number_format((isset($all_total['profit']) && !empty($all_total['add_cost'])) ? ($all_total['profit'] / ($all_total['add_cost'] * 1.1)) * 100 : 0);
        $all_total['cpa'] = number_format((isset($all_total['add_cost']) && !empty($all_total['confirm_num'])) ? ($all_total['add_cost'] * 1.1) / ($all_total['confirm_num']) : null);
        $all_total['cpc'] = number_format($all_total['cpc'] / $days);
        $all_total['mcpa'] = number_format($all_total['mcpa'] / $days);
        $all_total['is_num'] = number_format($all_total['is_num'] / $days, 2);
        $all_total['top_part'] = number_format($all_total['top_part'] / $days, 2);
        $all_total['best_part'] = number_format($all_total['best_part'] / $days, 2);

        //確定数値(右表)
        $adjustments = Adjustment::whereIn('code_id', $code_id)->where('year', $year)->where('month', $month)->where('genre_id', $genre_id)
            ->where('code_id', '!=', 0)->get()->keyBy('code_id');

        //確定数値(左表)
        $all_adjustments = ['confirm_price' => 0, 'confirm_num' => 0, 'add_cost' => 0, 'profit' => 0, 'cpc' => 0, 'mcpa' => 0,
            'roi' => 0, 'cpa' => 0, 'is_num' => 0, 'top_part' => 0, 'best_part' => 0, 'access' => 0, 'cvr' => 0];
        $confirm_total = ConfirmTotal::where('year', $year)->where('month', $month)
            ->where('genre_id', $genre_id)->first();

        if (isset($adjustments)) {
            foreach ($adjustments as $log_1) {
                $all_adjustments['confirm_num'] += $log_1->confirm_num;
                $all_adjustments['confirm_price'] += $log_1->confirm_price;
                $all_adjustments['access'] += $log_1->access;
            }
        }

        $pre_profit = $all_adjustments['confirm_price'] - ($confirm_total['add_cost'] * 1.1);
        $all_adjustments['roi'] = number_format((isset($pre_profit) && !empty($confirm_total['add_cost'])) ? ($pre_profit / ($confirm_total['add_cost'] * 1.1)) * 100 : 0);
        $all_adjustments['cpa'] = number_format((isset($confirm_total['add_cost']) && !empty($all_adjustments['confirm_num'])) ? ($confirm_total['add_cost'] * 1.1) / ($all_adjustments['confirm_num']) : null);

        $all_adjustments['profit'] = number_format($pre_profit);

        $all_adjustments['confirm_price'] = number_format($all_adjustments['confirm_price']);
        $all_adjustments['add_cost'] = number_format($confirm_total['add_cost']);

        $all_adjustments['cpc'] = number_format($confirm_total['cpc']);
        $all_adjustments['mcpa'] = number_format($confirm_total['mcpa']);

        $all_adjustments['is_num'] = number_format($confirm_total['is_num'], 2);
        $all_adjustments['top_part'] = number_format($confirm_total['top_part'], 2);
        $all_adjustments['best_part'] = number_format($confirm_total['best_part'], 2);

        if (!empty($all_adjustments['confirm_num']) && !empty($all_adjustments['access'])) {
            $all_adjustments['cvr'] = round($all_adjustments['confirm_num'] / $all_adjustments['access'], 3) * 100;
        }

        $all_adjustments['confirm_num'] = number_format($all_adjustments['confirm_num']);
        $all_adjustments['access'] = number_format($all_adjustments['access']);

        //左表打ち込みしたテキストを表示(リニューアル前情報)
        $all_write_texts = CodeNote::where('genre_id', $genre_id)->whereYear('created_at', '=', $year)
            ->whereMonth('created_at', '=', $month)->get();
        $write_texts = $all_write_texts->groupBy('date')->toArray();
        foreach ($write_texts as $key => $text) {
            $write_texts[$key] = $text[0];
        }
        //左表打ち込みしたテキストを表示(リニューアル後)
        $new_write_texts = CodeNote::where('genre_id', $genre_id)->whereYear('year_month', '=', $year)
            ->whereMonth('year_month', '=', $month)->get();
        $new_texts = $new_write_texts->groupBy('date')->toArray();
        foreach ($new_texts as $key => $text) {
            $new_texts[$key] = $text[0];
        }

        $select_month = [];
        //今日から1年前までのselect( /月 )
        for ($i = 0; $i < 13; $i++) {
            $select_month[$i] = date('Y-m', strtotime("-" . $i . " month"));
        }

        return view('report/detail', [
            'genre_id' => $genre_id,
            'period' => $period,
            'code_list' => $code_list,
            'code_logs' => $code_logs,
            'code_total' => $code_total,
            'write_logs' => $write_logs,
            'write_texts' => $write_texts,
            'new_texts' => $new_texts,
            'select_month' => $select_month,
            'selected_month' => $request->select_month,
            'total' => $total,
            'current_year' => $year,
            'current_month' => $month,
            'genre_data' => Genre::find($genre_id),
            'all_total' => $all_total,
            'all_adjustments' => $all_adjustments,
            'adjustments' => $adjustments,
            'authority_type' => $authority_type,
        ]);
    }

}
