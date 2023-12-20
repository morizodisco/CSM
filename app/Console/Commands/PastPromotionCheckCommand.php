<?php

namespace App\Console\Commands;

use App\Models\PromotionCode;
use App\Models\ScrapingLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Promotion;
use App\Models\PastPromotion;

class PastPromotionCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:past_promotion_check_command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '表示プロモーションの記録';

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
            $code_ids = ScrapingLog::whereMonth('created_at',$last_month)->get()->pluck('promotion_code_id')->unique();

            $promotion_ids = [];
            foreach ($code_ids as $val){
                $promotion = PromotionCode::where('id',$val)->value('name');
                $promotion = isset($promotion) ? $promotion : 0;
                array_push($promotion_ids,$promotion);
            }
            $promotion_ids = array_unique($promotion_ids);

            foreach ($promotion_ids as $promotion) {
                PastPromotion::create([
                    'promotion_id' => $promotion,
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
