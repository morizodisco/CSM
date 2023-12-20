<?php

namespace App\Http\Controllers;

use App\Models\CodeTotal;
use App\Models\CodeNote;
use App\Models\Report;
use App\Models\ScrapingLog;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AjaxController extends Controller
{
    //レコード記録 数値(report/detail)
    public function code_total(Request $request)
    {
        DB::beginTransaction();
        try {
            $target_date = $request->year.'-'.$request->month.'-'.$request->date.' '.$request->time.':59:59';

            $code_total = CodeTotal::where([
                'genre_id' => $request->genre_id,
                'created_at' => $target_date,
            ])->first();

            if (empty($code_total)) {
                $code_total = new CodeTotal();
                $code_total->genre_id = $request->genre_id;
                $code_total->date = $request->date;
                $code_total->time = $request->time;
                $code_total->manual_posted_at = date('Y-m-d H:i:s');
                $code_total->created_at = $target_date;
            }
            $code_total->{$request->name} = str_replace([',', '-'], '', $request->num);

            $code_total->save();

            /*$replace = [',' => ''];

            $total = CodeTotal::firstOrCreate(['id' => $request->id]);
            $data[$request->name] = $request->num;
            if(isset($data['add_cost'])) $data['add_cost'] = strtr($data['add_cost'],$replace);
            if(isset($data['cpc'])) $data['cpc'] = strtr($data['cpc'],$replace);
            if(isset($data['mcpa'])) $data['mcpa'] = strtr($data['mcpa'],$replace);
            $data['created_at'] = $target_date;
            $result = $total->fill($data)->save();*/

            DB::commit(); // コミット

            return $code_total;
        } catch (\Exception $e) {
            DB::rollback(); // ロールバック
            return $e->getMessage();
        }

    }

    //レコード記録(report/detail)
    public function note_total(Request $request)
    {
        $result = '';
        $data = $request->all();
        DB::beginTransaction();
        try {
            $total = CodeNote::firstOrCreate(['id' => $request->id]);
            $data[$request->name] = $request->note;
            $result = $total->fill($data)->save();

            DB::commit(); // コミット
        } catch (\Exception $e) {
            DB::rollback(); // ロールバック
            echo $e->getMessage();
        }

        return $result;

    }

    //レコード記録 数値(report/detail)
    public function code_item(Request $request)
    {

        DB::beginTransaction();
        try {
            $target_date = $request->year.'-'.$request->month.'-'.$request->date.' '.$request->time.':59:59';

            $scraping_log = ScrapingLog::where([
                'promotion_code_id' => $request->code_id,
                'created_at' => $target_date,
                ])->first();

            if (empty($scraping_log)) {
                $scraping_log = new ScrapingLog();
                $scraping_log->promotion_code_id = $request->code_id;
                $scraping_log->manual_posted_at = date('Y-m-d H:i:s');
                $scraping_log->created_at = $target_date;
            }
            $scraping_log->{$request->name} = str_replace([',', '-'], '', ($request->num ?? 0));

            $scraping_log->save();

            DB::commit(); // コミット

            return $scraping_log;
        } catch (\Exception $e) {
            DB::rollback(); // ロールバック
            return $e->getMessage();
        }

    }

    //レコード記録(report)
    public function genre_note(Request $request)
    {
        DB::beginTransaction();
        try {
            $genre = Genre::find($request->genre_id);
            $genre->note = $request->note;
            $result = $genre->save();

            DB::commit(); // コミット

            return $result;
        } catch (\Exception $e) {
            DB::rollback(); // ロールバック
            return $e->getMessage();
        }

    }

    //議事録記録(report/detail)
    public function report_minutes(Request $request)
    {
        DB::beginTransaction();
        try {
            $report = Report::find($request->report_id);
            $report->minutes = $request->minutes;
            $result = $report->save();

            DB::commit(); // コミット

            return $result;
        } catch (\Exception $e) {
            DB::rollback(); // ロールバック
            return $e->getMessage();
        }

    }

}
