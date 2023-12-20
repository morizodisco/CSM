<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\ScrapingLog;
use App\Models\CodeTotal;
use App\Models\PromotionCode;
use App\Models\Genre;

class HourCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:hour_check_command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '時間毎の最新データのみ残す';

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

        // $deleted_at = date('Y-m-d H:i:s'); // 削除日

        $deleted_at = '2020-10-05 00:00:00'; // 削除日

        $limit = NULL;

        try {
            $promotion_codes = ScrapingLog::select('promotion_code_id')->groupBy('promotion_code_id')->pluck('promotion_code_id');

            $delete_data = [];
            foreach ($promotion_codes AS $code_id) {
                $scraping_logs = ScrapingLog::select('id', 'created_at')->where('promotion_code_id', $code_id)
                    ->whereTime('created_at', '!=', '23:59:59')
                    ->whereTime('created_at', '!=', '17:59:59')
                    ->whereTime('created_at', '!=', '09:59:59')
                    ->limit($limit)
                    ->orderBy('created_at', 'desc')
                    ->get();

                $true_data = [];
                $false_data = [];
                foreach ($scraping_logs AS $log) {
                    $check_date = date('Y-m-d H', strtotime($log->created_at));
                    if (!isset($true_data[$check_date])) $true_data[$check_date] = $log->created_at;
                    else $false_data[] = $log->id;
                }

                // d($true_data);

                // d(count($false_data));

                $delete_data = array_merge($delete_data, $false_data);
            }

            // d(count($delete_data));

            if (!empty($delete_data)) {
                $update_limit = 10000;

                $delete_data = array_slice($delete_data, 0, $update_limit);

                // d(count($delete_data));

                $result = ScrapingLog::whereIn('id', $delete_data)->update(['deleted_at' => $deleted_at]);

                d($result.'/'.count($delete_data));
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            echo $e->getMessage() . "\n";
        }
    }
}
