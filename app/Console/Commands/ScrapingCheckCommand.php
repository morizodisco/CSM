<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\ScrapingLog;
use App\Models\MacroLog;
use Illuminate\Support\Facades\Mail;

class ScrapingCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:scraping_check_command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'スクレイピングチェック';

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

        $alert_minutes = 3; // アラートメールを通知するまでのリミット（分）

        try {
            // 最新のログデータを取得
            $latest_macro_log = MacroLog::orderBy('created_at', 'desc')->first();

            DB::commit();

            if (!empty($latest_macro_log)) {
                // 現在時刻との差を算出
                $current_at = date('Y-m-d H:i:s');
                $created_at = $latest_macro_log->created_at;
                $diff_minutes = floor((strtotime($current_at) - strtotime($created_at)) / 60);

                // 最新のログ日時が、設定した時間を超えていた場合にはアラートを送信
                if ($diff_minutes >= $alert_minutes) {
                    Mail::send(['text' => 'emails.alert.scraping'], [
                        "minutes" => $diff_minutes,

                    ], function ($message) {
                        $message
                            ->to('moncson@gmail.com')
                            ->subject("CSM SCRAPING が停止しています");
                    });

                    echo "システムが約 ".$diff_minutes." 分停止しています\n";

                    /*if (count(Mail::failures()) > 0) {
                        d(Mail::failures());
                    };*/
                } else {
                    echo "システムは正常稼働中です\n";
                }
            } else {
                echo "有効なスクレイピングデータを取得出来ませんでした\n";
            }

            // echo "commit\n";
        } catch (\Exception $e) {
            DB::rollback();
            echo $e->getMessage() . "\n";
        }
    }
}
