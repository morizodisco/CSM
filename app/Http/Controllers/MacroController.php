<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\Promotion;
use App\Models\PromotionCode;
use App\Models\HealthCheckList;
use App\Models\HealthCheckLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class MacroController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    public function show_media(Request $request)
    {
        return view('macro/show_media', [
            'genres' => Genre::where('status_flag', 1)->orderby('created_at','DESC')->get(),
            ]);
    }

    public function show_promotion(Request $request)
    {
        return view('macro/show_promotion', [
            'promotions' => Promotion::where('status_flag', 1)->orderby('created_at','DESC')->get(),
        ]);
    }

    public function show_code_url(Request $request)
    {
        $promotion_codes = PromotionCode::with(['genres' => function ($query) {
            $query->where('status_flag', 1);
        }])->where('status_flag', 1)->where('scraping_disabled', 0)->orderby('created_at','DESC')->get();

        $codes = [];
        foreach ($promotion_codes AS $promotion_code) {
            $code = explode(' & ', $promotion_code['code']);
            if (isset($code[0], $code[1])) $codes[] = ['media' => $code[1], 'promotion' => $code[0]];
        }

        return view('macro/show_code_url', [
            'codes' => $codes,
        ]);
    }

    public function health_check_url(Request $request)
    {
        $health_check_list = HealthCheckList::orderby('last_check_at', 'ASC')->first();

        DB::beginTransaction();
        try {
            $date = date('Y-m-d H:i:s');

            $health_check_list->last_check_at = $date;
            $health_check_list->save();

            $health_check_log = new HealthCheckLog();
            $health_check_log->health_check_list_id = $health_check_list->id;
            $health_check_log->open_time = $date;
            $health_check_log->save();

            DB::commit();
        } catch (\PDOException $e) {
            echo $e->getMessage();
            DB::rollBack();
        }

        return view('macro/health_check_url', [
            'health_check_list' => $health_check_list,
        ]);
    }

    public function health_check_register(Request $request)
    {
        if ($request->has('status_flag')) {
            $health_check_list = HealthCheckList::where('check_url', ($request->check_url ?? ''))->first();

            if (!empty($health_check_list)) {
                DB::beginTransaction();
                try {
                    // flag が「 0 」のドメインを「 OK 」した場合
                    if ($health_check_list->status_flag == 0 && $request->status_flag == 1) {
                        $email_message = '「 '.$health_check_list->check_url.' 」の正常な表示が確認されました。広告を再稼働してください。';
                    }
                    // flag が「 1 」のドメインを「 NG 」した場合
                    elseif ($health_check_list->status_flag == 1 && $request->status_flag == 0) {
                        $email_message = '「 '.$health_check_list->check_url.' 」が表示できませんでした。広告を停止してください。';
                    }

                    $date = date('Y-m-d H:i:s');

                    $health_check_list->status_flag = $request->status_flag;
                    $health_check_list->last_check_at = $date;
                    $health_check_list->save();

                    $health_check_log = HealthCheckLog::where('health_check_list_id', $health_check_list->id)->orderby('open_time', 'DESC')->first();
                    $health_check_log->close_time = $date;
                    $health_check_log->drawing_time = strtotime($health_check_log->close_time) - strtotime($health_check_log->open_time);
                    $health_check_log->status_flag = $request->status_flag;
                    $health_check_log->save();

                    DB::commit();

                    /*if (!empty($email_message)) {
                        Mail::send(['text' => 'emails.alert.health_check'], [
                            "email_message" => $email_message,

                        ], function ($message) use ($health_check_list) {
                            $message
                                //->to('moncson@gmail.com')
                                //->to('mafune@comb-s.jp')
                                //->to('mshrfeda@gmail.com')
                                //->to('yucson@gmail.com')
                                ->subject("HEALTH CHECK ALERT | CSM");

                            $alert_emails = explode(',', ($health_check_list->alert_emails ?? ''));
                            if (!empty($alert_emails)) $message->to($alert_emails);
                        });

                        if (count(Mail::failures()) > 0) {
                            echo Mail::failures();
                        };
                    }*/

                    return back()->with('message', '登録成功');
                } catch (\PDOException $e) {
                    echo $e->getMessage();
                    DB::rollBack();

                    return back()->with('message', '登録失敗');
                }
            }
        }

        return view('macro/health_check_register');
    }
}
