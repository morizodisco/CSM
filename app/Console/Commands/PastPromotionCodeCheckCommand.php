<?php

namespace App\Console\Commands;

use App\Models\ScrapingLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\PastPromotionCode;

class PastPromotionCodeCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:past_promotion_code_check_command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '表示プロモーションコードの記録';

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

            $last_month = date('m', strtotime('-1 month'));
            $all_promotion_codes = ScrapingLog::whereMonth('created_at',$last_month)->get()->pluck('promotion_code_id')->unique();

            foreach ($all_promotion_codes as $promotion) {
                PastPromotionCode::create([
                    'promotion_code_id' => $promotion,
                    'status_flag' => 1,
                    'year' => date('Y', strtotime('-1 month')),
                    'month' => $last_month,
                ]);
            }

            DB::commit();
            echo "commit\n";
        } catch (\Exception $e) {
            DB::rollback();
            echo $e->getMessage() . "\n";
        }
    }
}
