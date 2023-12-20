<?php

namespace App\Console\Commands;

use App\Models\PromotionCode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\ScrapingLog;
use App\Models\CodeTotal;

class ConfirmPriceCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:confirm_price_check_command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '合計値入力';

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

            //合計が自動算出されているログ取得
            $test = ScrapingLog::where(function($query){
                $query->orWhere('confirm_price_check',0)
                    ->orWhere('confirm_price_check', null);
            })->where(function($query){
                $query->orWhere('confirm_price',0)
                    ->orWhere('confirm_price', null);
            })->where(function($query){
                $query->Where('confirm_num','!=',0)
                    ->Where('confirm_num','!=',null);
            })->orderBy('created_at','desc')->limit(200)->get();

            $promotion_codes = PromotionCode::all()->keyBy('id')->toArray();

            if(!empty($test)){
                foreach ($test as $t){
                    $unit_price = $promotion_codes[$t->promotion_code_id]['unit_price'] ?? 0;
                    $total = $unit_price * $t->confirm_num;
                    $t->confirm_price = $total;
                    $t->confirm_price_check = 1;
                    $t->save();
                }
            }

            DB::commit();
            echo "commit\n";
        } catch (\Exception $e) {
            DB::rollback();
            echo $e->getMessage() . "\n";
        }
    }
}
