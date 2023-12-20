<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\ScrapingLog;
use App\Models\CodeTotal;

class DuplicationCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:duplication_check_command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '重複チェック';

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

        $deleted_at = date('Y-m-d H:i:s'); // 削除日

        try {
            // 数字が全てゼロのデータを削除
            $scraping_logs = ScrapingLog::where([
                'imp' => 0,
                'access' => 0,
                'ctr' => 0,
                'occur_num' => 0,
                'occur_price' => 0,
                'confirm_num' => 0,
                'confirm_price' => 0,
                'cvr' => 0,
                'total' => 0,
                'yesterday_check' => 0,
                ])
                ->whereNull('manual_posted_at')
                ->whereNull('deleted_at')
                ->orderBy('id', 'desc')
                ->limit(10000)
                ->get();

            foreach ($scraping_logs AS $log) {
                $log->deleted_at = $deleted_at;
                $log->save();
            }

            d(count($scraping_logs));


            // スクレイピングログの重複データを削除（一番初めに登録されたもののみを残す）
            $check_date = date('Y-m-d', strtotime('-7day'));

            $scraping_logs = ScrapingLog::where(function ($query) {
                    $query->whereTime('created_at', '=', '23:59:59')
                        ->orwhereTime('created_at', '=', '17:59:59')
                        ->orwhereTime('created_at', '=', '09:59:59');
                })
                ->whereDate('created_at', '>=', $check_date)
                ->whereNull('deleted_at')
                ->orderBy('promotion_code_id')
                ->orderBy('created_at')
                ->orderBy('id')
                ->get();

            $result = [];
            $duplication_list = []; // 削除データ格納用の配列
            foreach ($scraping_logs AS $log) {
                if (empty($result[$log['promotion_code_id']][$log['created_at']])) $result[$log['promotion_code_id']][$log['created_at']] = true;
                else $duplication_list[] = $log;
            }

            foreach ($duplication_list AS $log) {
                $log->deleted_at = $deleted_at;
                $log->save();
            }

            d(count($duplication_list));

            // コストログの重複データを削除（一番初めに登録されたもののみを残す）
            $code_totals = CodeTotal::where(function ($query) {
                $query->whereTime('created_at', '=', '23:59:59')
                    ->orwhereTime('created_at', '=', '17:59:59')
                    ->orwhereTime('created_at', '=', '09:59:59');
            })
                ->whereDate('created_at', '>=', $check_date)
                ->whereNull('deleted_at')
                ->orderBy('genre_id')
                ->orderBy('created_at')
                ->orderBy('id')
                ->get();

            $result = [];
            $duplication_list = []; // 削除データ格納用の配列
            foreach ($code_totals AS $log) {
                if (empty($result[$log['genre_id']][$log['created_at']])) $result[$log['genre_id']][$log['created_at']] = true;
                else $duplication_list[] = $log;
            }

            foreach ($duplication_list AS $log) {
                $log->deleted_at = $deleted_at;
                $log->save();
            }

            d(count($duplication_list));

            DB::commit();
            echo "commit\n";
        } catch (\Exception $e) {
            DB::rollback();
            echo $e->getMessage() . "\n";
        }
    }
}
