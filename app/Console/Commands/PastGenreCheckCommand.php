<?php

namespace App\Console\Commands;

use App\Models\PromotionCode;
use App\Models\ScrapingLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Genre;
use App\Models\PastGenre;

class PastGenreCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:past_genre_check_command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '表示メディアの記録';

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
            $all_genres = PromotionCode::whereIn('id',$code_ids)->get()->pluck('genre_id')->unique();

            foreach ($all_genres as $genre) {
                PastGenre::create([
                    'genre_id' => $genre,
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
