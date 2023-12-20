<?php

namespace App\Console\Commands;

use App\Models\Genre;
use App\Models\PromotionCode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\ScrapingLog;
use App\Models\MasterTotals;

class TotalFiguresCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:total_figures_check_command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'マスターレポートの統計更新';

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
            $year = date('Y');
            $month = date('m');

            $genre_list = Genre::where('deleted_at', null)->orderBy('display_num')
                ->with('promotion_codes')->whereHas('promotion_codes', function($query){
                    $query->whereExists(function($query){
                        return $query;
                    });
                })->get();

            //ジャンルごとの合計を更新
            foreach ($genre_list as $genre) {

                $genre_total = MasterTotals::firstOrCreate(['genre_id' => $genre->id, 'year' => $year, 'month' => $month]);

                $genre_total->profit = $genre->get_header_aggregated($year, $month)['profit'];
                $genre_total->avg_profit = round($genre->get_header_aggregated($year, $month)['avg_profit'], 2);
                $genre_total->expected_profit = round($genre->get_header_aggregated($year, $month)['expected_profit'], 2);
                $genre_total->profit_last_month = $genre->get_header_aggregated($year, $month)['profit_last_month'];
                $genre_total->profit_rate = round($genre->get_header_aggregated($year, $month)['profit_rate'], 2);

                $genre_total->save();
            }

            //全ジャンルの合計を更新
            $aggregated = ['profit' => 0, 'avg_profit' => 0, 'expected_profit' => 0, 'profit_last_month' => 0, 'profit_rate' => 0];
            foreach ($genre_list as &$genre) {

                // ヘッダー集計
                $aggregated['profit'] += $genre->get_header_aggregated($year, $month)['profit']; //今日のデータ除去済み
                $aggregated['avg_profit'] += $genre->get_header_aggregated($year, $month)['avg_profit']; //今日のデータ除去済み
                $aggregated['expected_profit'] += $genre->get_header_aggregated($year, $month)['expected_profit'];
                $aggregated['profit_last_month'] += $genre->get_header_aggregated($year, $month)['profit_last_month'];
            }

            $aggregated['profit_rate'] = ($aggregated['expected_profit'] / $aggregated['profit_last_month'])*100;

            $all_total = MasterTotals::firstOrCreate(['genre_id' => 0, 'year' => $year, 'month' => $month]);

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
