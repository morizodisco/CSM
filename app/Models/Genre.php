<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \DateTimeImmutable;
use \DateInterval;
use \DatePeriod;

class Genre extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'note',
        'media_id',
        'name',
        'display_color',
        'display_num',
        'google_ads_customer_id',
        'status_flag',
    ];

    // 集計結果保存用変数
    protected $_aggregated;
    protected $_total_aggregated;
    protected $_aggregated_last_month;
    protected $_total_aggregated_last_month;
    protected $_promotion_codes;

    public function member_Genres()
    {
        return $this->hasMany('App\Models\Member_genre');
    }

    public function promotion_codes()
    {
        return $this->hasMany(PromotionCode::class);
    }

    public function genres()
    {
        return $this->hasMany(UserGenre::class);
    }

    public function code_totals()
    {
        return $this->hasMany(CodeTotal::class);
    }

    public function scraping_logs()
    {
        return $this->hasManyThrough(ScrapingLog::class, PromotionCode::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function adjustments()
    {
        return $this->hasMany(Adjustment::class);
    }

    public function past_genres()
    {
        return $this->hasMany(PastGenre::class);
    }

    public function past_promotion_code()
    {
        return $this->hasManyThrough(PastPromotionCode::class,PromotionCode::class);
    }

    public function past_promotion()
    {
        return $this->belongsToMany(PastPromotion::class,PromotionCode::class,'genre_id','name','id','promotion_id');
    }

    public function get_header_aggregated($year, $month)
    {
        // 集計済みデータの存在チェック及び、データの抽出
        if (is_null($this->_aggregated)) $this->get_aggregated($year, $month);
        if (is_null($this->_total_aggregated)) $this->get_total_aggregated($year, $month);

        $today_price = 0;
        $today_add_cost = 0;
        $today = 0;
        if($month == date('m') && $year == date('Y')){
            $today_price = ($this->_aggregated[date('d')]['confirm_price']) ?? 0;
            $today_add_cost = ($this->_total_aggregated[date('d')]['add_cost']) ?? 0;
            $today = 1;
        }

        if($month == date('m')){
            $days = date('j');
        }else{
            $days = date('t', strtotime($year.'-'.$month));
        }

        // 前月データの取得
        $last_year = date('Y', strtotime($year.'-'.$month.'-01 -1 month'));
        $last_month = date('m', strtotime($year.'-'.$month.'-01 -1 month'));
        if (is_null($this->_aggregated_last_month)) $this->get_aggregated($last_year, $last_month, true);
        if (is_null($this->_total_aggregated_last_month)) $this->get_total_aggregated($last_year, $last_month, true);

        // ヘッダー集計
        $data = [];
        $data['profit'] = ($this->_aggregated->sum('confirm_price') - $today_price) - (($this->_total_aggregated->sum('add_cost') - $today_add_cost) * 1.1);
        $data['avg_profit'] = $data['profit'] / (number_format($days) - $today);
        $data['expected_profit'] = $data['avg_profit'] * date('t', strtotime($year.'-'.$month));
        $data['profit_last_month'] = $this->_aggregated_last_month->sum('confirm_price') - ($this->_total_aggregated_last_month->sum('add_cost') * 1.1);
        $data['profit_rate'] = !empty($data['profit_last_month']) ? ($data['expected_profit'] / $data['profit_last_month']) * 100 : 0;

        return collect($data);
    }

    // scraping_logs の集計
    public function get_aggregated($year, $month, bool $last_month = false)
    {
        // 既に集計済みデータがセットされてる場合はそのまま返却
        if (isset($this->_aggregated) && !$last_month) return $this->_aggregated;
        if (isset($this->_aggregated_last_month) && $last_month) return $this->_aggregated_last_month;

        // 月のデータを取得し、コード ID でグループ化

        if($year.$month == date('Ym')){
            $scraping_logs = $this->scraping_logs()
                ->whereHas('promotion', function ($q) {
                    $q->where('promotion_codes.status_flag', 1);
                })
                ->whereYear('scraping_logs.created_at', '=', $year)
                ->whereMonth('scraping_logs.created_at', '=', $month)
                ->orderBy('scraping_logs.created_at', 'desc')->get()->groupBy('promotion_code_id');
        }else{
            $ids = $this->past_promotion()->where('year', $year)->where('month', $month)->where('past_promotions.status_flag',1)->pluck('promotion_id');
            $past_promotion_code = $this->past_promotion_code()->where('year',$year)->where('month',$month)->whereIn('name',$ids)
                ->where('past_promotion_codes.status_flag',1)->pluck('promotion_code_id');
            $scraping_logs = $this->scraping_logs()->whereIn('promotion_code_id',$past_promotion_code)
                ->whereYear('scraping_logs.created_at', '=', $year)
                ->whereMonth('scraping_logs.created_at', '=', $month)
                ->orderBy('scraping_logs.created_at', 'desc')->get()->groupBy('promotion_code_id');
        }
        $day_count = date('t', strtotime($year . '-' . $month . '-01'));


        if (!isset($this->_promotion_codes)) $this->_promotion_codes = $this->promotion_codes()->where('status_flag', 1)->get()->keyBy('id')->toArray();

        // コード事の日別データに分解し、最新のデータのみ抽出し、合算する
        $result = [];
        foreach ($scraping_logs AS $logs) {
            for ($i = 1; $i <= $day_count; $i++) {
                if (!isset($result[sprintf('%02d', $i)])) $result[sprintf('%02d', $i)] = ['confirm_num' => 0, 'confirm_price' => 0];

                // 正規表現で最新データを取得
                $log = $logs->filter(function ($row) use ($year, $month, $i) {
                    return preg_match('/' . preg_quote($year . '-' . $month . '-' . sprintf('%02d', $i)) . '/', $row['created_at']);
                })->first();

                // コード単価が設定済みの場合（ゼロで無い場合）は、単価*登録数で売上を再設定する
                if (is_null($log['confirm_price'])){
                    $unit_price = $this->_promotion_codes[$log['promotion_code_id']]['unit_price'] ?? 0;
                    if (!empty($unit_price)) $log['confirm_price'] = $unit_price * $log['confirm_num'];
                }

                $result[sprintf('%02d', $i)]['confirm_num'] += $log['confirm_num'];
                $result[sprintf('%02d', $i)]['confirm_price'] += $log['confirm_price'];
            }
        }

        if ($last_month) return $this->_aggregated_last_month = collect($result);

        return $this->_aggregated = collect($result);
    }

    // code_totals の集計
    public function get_total_aggregated($year, $month, bool $last_month = false)
    {
        // 既に集計済みデータがセットされてる場合はそのまま返却
        if (isset($this->_total_aggregated) && !$last_month) return $this->_total_aggregated;
        if (isset($this->_total_aggregated_last_month) && $last_month) return $this->_total_aggregated_last_month;

        // データを集計し、オブジェクト変数にデータをセットしつつ返却
        $day_count = date('t', strtotime($year . '-' . $month . '-01'));

        // 月のデータを取得
        $code_totals = $this->code_totals()
            ->whereYear('code_totals.created_at', '=', $year)
            ->whereMonth('code_totals.created_at', '=', $month)
            ->orderBy('code_totals.created_at', 'desc')->get();

        // 日別データに分解し、最新のデータのみ抽出
        $result = [];
        for ($i = 1; $i <= $day_count; $i++) {
            // 正規表現で最新データを取得
            $code_total = $code_totals->filter(function ($log) use ($year, $month, $i) {
                return preg_match('/' . preg_quote($year . '-' . $month . '-' . sprintf('%02d', $i)) . '/', $log['created_at']);
            })->first();

            // データが存在しなければ、新規オブジェクトを作成（テンプレート側の場合分けを減らす為）
            if(empty($code_total)) $code_total = new CodeTotal();

            $result[sprintf('%02d', $i)] = $code_total;
        }

        if ($last_month) return $this->_total_aggregated_last_month = collect($result);

        return $this->_total_aggregated = collect($result);
    }

    // 期間集計
    public function get_aggregated_period($start_date = NULL, $end_date= NULL)
    {
        if (empty($start_date)) $start_date = date('Y-m-d');
        if (empty($end_date)) $end_date = date('Y-m-d');

        $start = new DateTimeImmutable($start_date);
        $end = new DateTimeImmutable(date('Y-m-d', strtotime($end_date .'+ 1 day')));
        $end->add(new DateInterval('P2D'));
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($start, $interval, $end);

        // 月のデータを取得
        $scraping_logs = $this->scraping_logs()
            ->whereHas('promotion', function ($q) {
                $q->where('promotion_codes.status_flag', 1);
            })
            ->whereDate('scraping_logs.created_at', '>=', $start_date)
            ->whereDate('scraping_logs.created_at', '<=', $end_date)
            // ->where('confirm_num', '!=', 0)
            // ->whereNull('scraping_logs.deleted_at')
            ->orderBy('scraping_logs.created_at', 'desc')->get()->groupBy('promotion_code_id');

        // 月のデータを取得
        $code_totals = $this->code_totals()
            ->whereDate('code_totals.created_at', '>=', $start_date)
            ->whereDate('code_totals.created_at', '<=', $end_date)
            // ->whereNotNull('cpc')
            // ->whereNull('code_totals.deleted_at')
            ->orderBy('code_totals.created_at', 'desc')->get();

        if (!isset($this->_promotion_codes)) $this->_promotion_codes = $this->promotion_codes()->where('status_flag', 1)->get()->keyBy('id')->toArray();

        // 必要データの初期化
        $result = ['confirm_num' => 0, 'confirm_price' => 0, 'add_cost' => 0];

        foreach ($scraping_logs AS $logs) {
            foreach ($period AS $datetime) {
                // 正規表現で最新データを取得
                $log = $logs->filter(function ($row) use ($datetime) {
                    return preg_match('/' . preg_quote($datetime->format('Y-m-d')) . '/', $row['created_at']);
                })->first();

                // コード単価が設定済みの場合（ゼロで無い場合）は、単価*登録数で売上を再設定する
                $unit_price = $this->_promotion_codes[$log['promotion_code_id']]['unit_price'] ?? 0;
                if (!empty($unit_price)) $log['confirm_price'] = $unit_price * $log['confirm_num'];

                $result['confirm_num'] += $log['confirm_num'];
                $result['confirm_price'] += $log['confirm_price'];
            }
        }

        foreach ($period AS $datetime) {
            // 正規表現で最新データを取得
            $code_total = $code_totals->filter(function ($log) use ($datetime) {
                return preg_match('/' . preg_quote($datetime->format('Y-m-d')) . '/', $log['created_at']);
            })->first();

            // データが存在しなければ、新規オブジェクトを作成（テンプレート側の場合分けを減らす為）
            if(empty($code_total)) $code_total = new CodeTotal();

            $result['add_cost'] += $code_total->add_cost;
        }

        $result['profit'] = $result['confirm_price'] - ($result['add_cost'] * 1.1);

        return collect($result);
    }

    public function get_aggregated_period_rate($report_date, $report_num)
    {
        $report = $this->get_report($report_date, $report_num);

        $result = $this->get_aggregated_period($report->start_date, $report->end_date);
        $result_rate = $this->get_aggregated_period($report->rate_start_date, $report->rate_end_date);

        $result['confirm_num_diff'] = $result['confirm_num'] - $result_rate['confirm_num'];
        $result['confirm_price_diff'] = $result['confirm_price'] - $result_rate['confirm_price'];
        $result['add_cost_diff'] = $result['add_cost'] - $result_rate['add_cost'];
        $result['profit_diff'] = $result['profit'] - $result_rate['profit'];

        return collect($result);
    }

    public function get_report($report_date, $report_num)
    {
        $report_date = date('Y-m-01', strtotime($report_date));

        $report = $this->reports()->where('report_date', $report_date)->where('report_num', $report_num)->first();

        if (empty($report)) {
            $report = new Report();
            $report->genre_id = $this->attributes['id'];
            $report->report_num = $report_num;
            //$report->start_date = $report_date;
            //$report->end_date = $report_date;
            //$report->rate_start_date = $report_date;
            //$report->rate_end_date = $report_date;
            $report->report_date = $report_date;
            $report->save();
        }

        return $report;
    }

    public function get_report_test($report_date, $report_num)
    {
        $report_date = date('Y-m-01', strtotime($report_date));

        $report = $this->reports()->where('report_date', $report_date)->where('report_num', $report_num)->first();

        if (empty($report)) {
            $report = new Report();
            $report->genre_id = $this->attributes['id'];
            $report->report_num = $report_num;
            //$report->start_date = $report_date;
            //$report->end_date = $report_date;
            //$report->rate_start_date = $report_date;
            //$report->rate_end_date = $report_date;
            $report->report_date = $report_date;
            $report->save();
        }

        return $report;
    }

}
