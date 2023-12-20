<?php

namespace App\Console\Commands;

use App\Models\Adjustment;
use App\Models\Genre;
use App\Models\PromotionCode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\ConfirmTotal;
use App\Models\MasterTotals;

class TotalFigures_2CheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:total_figures_2_check_command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'マスターレポートの統計更新2';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        DB::beginTransaction();
        try {

            $l_year = date('Y', strtotime('-1 month'));
            $l_month = date('m', strtotime('-1 month'));
            $days = date('t', strtotime('-1 month'));

            $genre_list = Genre::where('deleted_at', null)->orderBy('display_num')
                ->with('promotion_codes')->whereHas('promotion_codes', function($query){
                    $query->whereExists(function($query){
                        return $query;
                    });
                })->get();

            foreach ($genre_list as $genre) {
                $genre_total = MasterTotals::firstOrCreate(['genre_id' => $genre->id, 'year' => $l_year, 'month' => $l_month]);


                    $genre_total->profit = $genre->get_header_aggregated($l_year, $l_month)['profit'];
                    $genre_total->avg_profit = round($genre->get_header_aggregated($l_year, $l_month)['avg_profit'], 2);
                    $genre_total->expected_profit = round($genre->get_header_aggregated($l_year, $l_month)['expected_profit'], 2);
                    $genre_total->profit_last_month = $genre->get_header_aggregated($l_year, $l_month)['profit_last_month'];
                    $genre_total->profit_rate = round($genre->get_header_aggregated($l_year, $l_month)['profit_rate'], 2);
                    $genre_total->save();

            }

            $all_total = MasterTotals::where('year',$l_year)->where('month',$l_month)->where('genre_id','!=',0)->get();

            //全ジャンルの合計を更新
            $aggregated = ['profit' => 0, 'avg_profit' => 0, 'expected_profit' => 0, 'profit_last_month' => 0, 'profit_rate' => 0];
            foreach ($all_total as $total) {

                // ヘッダー集計
                $aggregated['profit'] += $total->profit;
                $aggregated['avg_profit'] += $total->profit / $days;
                $aggregated['expected_profit'] += $total->profit;
                $aggregated['profit_last_month'] += $total->profit_last_month;
            }

            $aggregated['profit_rate'] = ($aggregated['expected_profit'] / $aggregated['profit_last_month']) * 100;

            $all_total = MasterTotals::firstOrCreate(['genre_id' => 0, 'year' => $l_year, 'month' => $l_month]);

            $all_total->profit = $aggregated['profit'];
            $all_total->avg_profit = round($aggregated['avg_profit'], 2);
            $all_total->expected_profit = round($aggregated['expected_profit'], 2);
            $all_total->profit_last_month = $aggregated['profit_last_month'];
            $all_total->profit_rate = round($aggregated['profit_rate'], 2);
            $all_total->save();

            DB::commit();
            echo "commit\n";
        } catch (\Exception $e) {
            DB::rollback();
            echo $e->getMessage() . "\n";
        }
    }
}
