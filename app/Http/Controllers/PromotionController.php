<?php
namespace App\Http\Controllers;
use App\Models\Promotion;
use App\Models\PromotionCode;
use App\Models\PastPromotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use DateTime;

class PromotionController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request){

        $data = $request->all();

        //ボタンのname値による条件分岐
        if ($request->input('update')){

            DB::beginTransaction();
            try {

                $data['status_flag'] = (!empty($request->status_flag)) ? '1' : '0';
                $db = Promotion::firstOrCreate(['id' => $request->id]);

                $db->fill($data)->save();

                DB::commit();
            } catch (\PDOException $e) {
                DB::rollBack();
            }
            return redirect('promotion');

        }elseif ($request->input('delete')){  //削除ボタンが押されたとき
            try {
                $db = Promotion::find($data['id']);
                $db->deleted_at = date('Y-m-d H:i:s');
                $db->save();

                // 関連するコードも削除
                PromotionCode::where('name', $data['id'])->delete();

                DB::commit();
            } catch (\PDOException $e) {
                DB::rollBack();
            }

            return redirect('promotion');
        }

        $past_month = [];
        for ($i = 1; $i <= 3; $i++){
            $past_month[$i]['name'] = date('Y年m月',strtotime('-'.$i.' month'));
            $past_month[$i]['year'] = date('Y',strtotime('-'.$i.' month'));
            $past_month[$i]['month'] = number_format(date('m',strtotime('-'.$i.' month')));
        }

        $past_promotion_all = PastPromotion::all();
        $past_promotion_all = $past_promotion_all->groupBy('promotion_id')->toArray();

        $past_promotion = [];
        foreach ($past_promotion_all as $key => $log_1){
            $past_promotion[$key] = collect($log_1);
            $past_promotion[$key] = $past_promotion[$key]->groupBy('year')->toArray();

            foreach ($past_promotion[$key] as $key2 => $log_2){
                $past_promotion[$key][$key2] = collect($log_2);
                $past_promotion[$key][$key2] = $past_promotion[$key][$key2]->keyBy('month')->toArray();

                foreach ($past_promotion[$key][$key2] as $key3=> $log_3){
                    $past_promotion[$key][$key2][$key3] = $log_3['status_flag'];
                }
            }
        }

        $promotion_list = Promotion::where('deleted_at',null)->get();

        return view('promotion/top', [
            'past_month' => $past_month,
            'promotion_list' => $promotion_list,
            'past_promotion' => $past_promotion
            ]);
    }

}
