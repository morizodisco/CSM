<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\ScrapingLog;
use App\Models\CodeTotal;
use App\Models\PromotionCode;
use App\Models\Genre;

class ForceDeleteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:force_delete_command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '論理削除のデータを物理削除する';

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

        $limit = 10000;

        try {
            // ScrapingLog
            $scraping_log_count = ScrapingLog::withTrashed()
                ->select('id')
                ->whereNotNull('deleted_at')
                ->count();

            $scraping_logs = ScrapingLog::withTrashed()
                ->select('id')
                ->whereNotNull('deleted_at')
                ->limit($limit)
                ->orderBy('created_at', 'desc')
                ->get();

            d(count($scraping_logs).'/'.$scraping_log_count);

            foreach ($scraping_logs AS $log) {
                $log->forceDelete();
            }

            // CodeTotal
            $code_total_count = CodeTotal::withTrashed()
                ->select('id')
                ->whereNotNull('deleted_at')
                ->count();

            $code_totals = CodeTotal::withTrashed()
                ->select('id')
                ->whereNotNull('deleted_at')
                ->limit($limit)
                ->orderBy('created_at', 'desc')
                ->get();

            d(count($code_totals).'/'.$code_total_count);

            foreach ($code_totals AS $log) {
                $log->forceDelete();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            echo $e->getMessage() . "\n";
        }
    }
}
