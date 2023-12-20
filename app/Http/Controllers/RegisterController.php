<?php
namespace App\Http\Controllers;
use App\Models\PromotionCode;
use App\Models\ScrapingLog;
use App\Models\MacroLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
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

    public function index(Request $request){

        $macro_log = New MacroLog;
        $macro_log->created_at = date("Y-m-d H:i:s");
        $macro_log->save();

        $data = $request->all();
        unset($data['code']);

        //ボタンのname値による条件分岐
        if ($request->input('add')){ //更新ボタンが押されたとき

            DB::beginTransaction();
            try {

                //登録確認
                $register_code = PromotionCode::where('code', 'like', '%' . $request->code . '%')->first();

                if ($register_code == null) {
                    $code = New PromotionCode;
                    $code_data['code'] = $request->code;
                    $code_data['created_at'] = date("Y-m-d H:i:s");
                    $code->fill($code_data)->save();

                    $code_id = $code->id;
                }else{
                    $code_id = $register_code['id'];
                }

                $replace_per = ['-' => '0','%' => '','％' => '',',' => ''];
                $replace_imp = ['imp' => '',',' => ''];
                $replace_price = ['円' => '',',' => ''];
                $replace_case = ['件' => '',',' => ''];

                /* 重複チェック */
                $created_at = date("Y-m-d H:i:s");
                $log = ScrapingLog::where([
                    'promotion_code_id' => $code_id,
                    'created_at' => $created_at,
                ])->first();

                if (empty($log)) {
                    $log = New ScrapingLog;
                    $data['promotion_code_id'] = $code_id;
                    $data['created_at'] = $created_at;
                }

                $data['imp'] = strtr($data['imp'],$replace_imp);
                $data['access'] = strtr($data['access'],$replace_case);
                $data['ctr'] = strtr($data['ctr'],$replace_per);
                $data['occur_num'] = strtr($data['occur_num'],$replace_case);
                $data['occur_price'] = strtr($data['occur_price'],$replace_price);
                $data['confirm_num'] = strtr($data['confirm_num'],$replace_case);
                $data['confirm_price'] = strtr($data['confirm_price'],$replace_price);
                $data['cvr'] = strtr($data['cvr'],$replace_per);
                $data['total'] = strtr($data['total'],$replace_price);
                $data['scraping_at'] = $created_at;

                $log->fill($data)->save();

                DB::commit();
            } catch (\PDOException $e) {
                DB::rollBack();
            }
            return redirect('register');
        }

        return view('register/top');
    }

    public function yesterday(Request $request){

        $macro_log = New MacroLog;
        $macro_log->created_at = date("Y-m-d H:i:s");
        $macro_log->save();

        $data = $request->all();
        unset($data['code']);

        //ボタンのname値による条件分岐
        if ($request->input('add')){ //更新ボタンが押されたとき

            DB::beginTransaction();
            try {

                //登録確認
                $register_code = PromotionCode::where('code', 'like', '%' . $request->code . '%')->first();

                if ($register_code == null) {
                    $code = New PromotionCode;
                    $code_data['code'] = $request->code;
                    $code_data['created_at'] = date("Y-m-d H:i:s");
                    $code->fill($code_data)->save();

                    $code_id = $code->id;
                }else{
                    $code_id = $register_code['id'];
                }

                $replace_per = ['-' => '0','%' => '','％' => '',',' => ''];
                $replace_imp = ['imp' => '',',' => ''];
                $replace_price = ['円' => '',',' => ''];
                $replace_case = ['件' => '',',' => ''];

                /* 重複チェック */
                $day_ago = $request->query('day_ago') ?? 1;
                $created_at = date("Y-m-d", strtotime('-'.$day_ago.' day')) . ' 23:59:59';
                $log = ScrapingLog::where([
                    'promotion_code_id' => $code_id,
                    'created_at' => $created_at,
                ])->first();

                if (empty($log)) {
                    $log = New ScrapingLog;
                    $data['promotion_code_id'] = $code_id;
                    $data['created_at'] = $created_at;
                }

                $data['imp'] = strtr($data['imp'],$replace_imp);
                $data['access'] = strtr($data['access'],$replace_case);
                $data['ctr'] = strtr($data['ctr'],$replace_per);
                $data['occur_num'] = strtr($data['occur_num'],$replace_case);
                $data['occur_price'] = strtr($data['occur_price'],$replace_price);
                $data['confirm_num'] = strtr($data['confirm_num'],$replace_case);
                $data['confirm_price'] = strtr($data['confirm_price'],$replace_price);
                $data['cvr'] = strtr($data['cvr'],$replace_per);
                $data['total'] = strtr($data['total'],$replace_price);
                $data['scraping_at'] = date("Y-m-d H:i:s");
                $data['yesterday_check'] = 1;

                $log->fill($data)->save();

                DB::commit();
            } catch (\PDOException $e) {
                DB::rollBack();
            }
            return redirect()->back();
        }

        return view('register/yesterday');
    }

}
