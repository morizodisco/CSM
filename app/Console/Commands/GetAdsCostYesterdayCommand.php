<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Genre;
use App\Models\CodeTotal;

class GetAdsCostYesterdayCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:get_ads_cost_yesterday_command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '広告費の取得（昨日）';

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
        // 0時～5時は実行しない
        if (date('H') == '00' || date('H') == '01' || date('H') == '02' || date('H') == '03' || date('H') == '04' || date('H') == '05') return false;

        // ライブラリ読み込み
        require_once(base_path().'/google-ads-php/src/Google/Ads/GoogleAds/Lib/OAuth2TokenBuilder.php');
        require_once(base_path().'/google-ads-php/src/Google/Ads/GoogleAds/Lib/V5/GoogleAdsClientBuilder.php');

        // MCCアカウントの顧客ID（その他の設定は「/google-ads-php/google_ads_php.ini」に記載）
        $mccCustomerIdId = 1197669690;

        // Google APIの認証情報を作成
        $oAuth2Credential = (new \Google\Ads\GoogleAds\Lib\OAuth2TokenBuilder())
            ->fromFile(base_path().'/google-ads-php/google_ads_php.ini')
            ->build();


        // 認証情報を利用してAds APIとの接続を確立
        $googleAdsClient = (new \Google\Ads\GoogleAds\Lib\V5\GoogleAdsClientBuilder())
            ->fromFile(base_path().'/google-ads-php/google_ads_php.ini')
            ->withOAuth2Credential($oAuth2Credential)
            ->withLoginCustomerId($mccCustomerIdId)
            ->build();

        // 顧客IDリストをデータベースから取得
        $genres = Genre::where('status_flag', 1)->whereNotNull('google_ads_customer_id')->pluck('google_ads_customer_id', 'id');

        $genres = ['381-764-4078'];

        // メディア（顧客ID）単位でループ処理を開始
        foreach ($genres AS $genre_id => $google_ads_customer_id) {
            // 顧客IDからハイフンを削除
            $customerId = str_replace('-', '', $google_ads_customer_id);
            // APIを実行
            $response = self::runApiTest($googleAdsClient, $customerId);

            d($response);
            exit;

            // 重複チェック（APIのレスポンスが返ってこない事があるので、期間内に複数回CRONを実行し、データが歯抜けにならないようにする）
            $code_total = CodeTotal::where('genre_id', $genre_id)->whereDate('created_at', date('Y-m-d', strtotime('-1 day')))->whereTime('created_at', '23:59:59')->first();

            // 前日の確定データが存在していなければ、専用のログテーブルに格納（前日の23:59:59に確定データとして格納）
            if (empty($code_total)) {
                $code_total = new CodeTotal();
                $code_total->genre_id = $genre_id;
                $code_total->date = date('d', strtotime('-1 day'));
                $code_total->time = '23';
                $code_total->add_cost = (!empty($response) ? $response : 0);
                $code_total->cpc = NULL;
                $code_total->mcpa = NULL;
                $code_total->is_num = NULL;
                $code_total->top_part = NULL;
                $code_total->best_part = NULL;
                $code_total->created_at = date('Y-m-d', strtotime('-1 day')).' 23:59:59';
                $code_total->save();
            } else {
                // データが存在していてかつ、広告費が 0 OR NULL だった場合はデータを更新
                if (empty($code_total->add_cost)) {
                    $code_total->add_cost = (!empty($response) ? $response : 0);
                    $code_total->save();
                }
            }
        }
    }

    public static function runApi($googleAdsClient, int $customerId)
    {
        // APIクライアントを生成
        $googleAdsServiceClient = $googleAdsClient->getGoogleAdsServiceClient();

        // クエリの作成
        $query =
            "SELECT campaign.id, "
            . "campaign.name, "
            . "ad_group.id, "
            . "ad_group.name, "
            . "ad_group_criterion.criterion_id, "
            . "ad_group_criterion.keyword.text, "
            . "ad_group_criterion.keyword.match_type, "
            . "metrics.impressions, "
            . "metrics.clicks, "
            . "metrics.cost_micros, "
            . "metrics.average_cpc, "
            . "metrics.average_cpm, "
            . "metrics.search_absolute_top_impression_share, "
            . "metrics.search_top_impression_share, "
            . "metrics.search_impression_share "
            . "FROM keyword_view "
            . "WHERE segments.date DURING YESTERDAY "
            . "AND campaign.advertising_channel_type = 'SEARCH' "
            . "AND ad_group.status = 'ENABLED' "
            . "AND ad_group_criterion.status IN ('ENABLED', 'PAUSED') "
            . "ORDER BY metrics.impressions DESC";

        // 指定した顧客IDにクエリを発行
        $stream = $googleAdsServiceClient->searchStream($customerId, $query, ['pageSize' => 10000, 'timeoutMillis' => 360000000]);

        // 全キャンペーンのトータル
        $counter = 0;

        $total_cost = 0;
        $total_average_cpc = 0;
        $total_average_cpm = 0;
        $total_search_absolute_top_impression_share = 0;
        $total_search_top_impression_share = 0;
        $total_search_impression_share = 0;
        // キャンペーンリストをループ
        foreach ($stream->iterateAllElements() as $googleAdsRow) {
            /** @var GoogleAdsRow $googleAdsRow */
            /*$campaign = $googleAdsRow->getCampaign();
            $adGroup = $googleAdsRow->getAdGroup();
            $adGroupCriterion = $googleAdsRow->getAdGroupCriterion();*/
            // メトリクスを取得
            $metrics = $googleAdsRow->getMetrics();

            // マイクロ円から円に変換
            $cost_micros = $metrics->getCostMicros() / (1000 * 1000);
            // コストがゼロの場合はこのループをスキップ
            if (empty($cost_micros)) continue;

            $counter ++;

            // トータルコストに加算
            $total_cost += $cost_micros;

            /*$total_average_cpc += $metrics->getAverageCpc() / (1000 * 1000);

            $total_average_cpm += $metrics->getAverageCpm() / (1000 * 1000);

            $search_absolute_top_impression_share = $metrics->getSearchAbsoluteTopImpressionShare();
            if ($search_absolute_top_impression_share == '0.0999') $search_absolute_top_impression_share = 0;
            $total_search_absolute_top_impression_share += $search_absolute_top_impression_share * 100;

            $search_top_impression_share = $metrics->getSearchTopImpressionShare();
            if ($search_top_impression_share == '0.0999') $search_top_impression_share = 0;
            $total_search_top_impression_share += $search_top_impression_share * 100;

            $search_impression_share = $metrics->getSearchImpressionShare();
            if ($search_impression_share == '0.0999') $search_impression_share = 0;
            $total_search_impression_share += $search_impression_share * 100;*/
        }

        /*if (!empty($counter)) $total_average_cpc = $total_average_cpc / $counter;
        if (!empty($counter)) $total_average_cpm = $total_average_cpm / $counter;
        if (!empty($counter)) $total_search_absolute_top_impression_share = $total_search_absolute_top_impression_share / $counter;
        if (!empty($counter)) $total_search_top_impression_share = $total_search_top_impression_share / $counter;
        if (!empty($counter)) $total_search_impression_share = $total_search_impression_share / $counter;

        d('----------------------------------');
        d($total_average_cpc);
        d($total_average_cpm);
        d($total_search_absolute_top_impression_share);
        d($total_search_top_impression_share);
        d($total_search_impression_share);*/

        // トータルコストを返却
        return $total_cost;
    }

    public static function runApiTest($googleAdsClient, int $customerId)
    {
        // APIクライアントを生成
        $googleAdsServiceClient = $googleAdsClient->getGoogleAdsServiceClient();

        // クエリの作成
        $query =
            "SELECT campaign.id, "
            . "campaign.name, "
            . "FROM campaign "
            . "ORDER BY campaign.id";

        // 指定した顧客IDにクエリを発行
        $stream = $googleAdsServiceClient->searchStream($customerId, $query, ['pageSize' => 10000]);

        // キャンペーンリストをループ
        foreach ($stream->iterateAllElements() as $googleAdsRow) {
            $campaign_name = $googleAdsRow->getCampaign()->getName();
            d($campaign_name);
        }

        // トータルコストを返却
        return true;
    }
}
