<?php
namespace App\Http\Controllers;
use App\Models\Genre;
use App\Models\PromotionCode;
use App\Models\PastGenre;
use App\Models\UserGenre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class GenreController extends Controller
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
                $db = Genre::firstOrCreate(['id' => $request->id]);

                $db->fill($data)->save();

                DB::commit();
            } catch (\PDOException $e) {
                DB::rollBack();
            }
            return redirect('genre');

        }elseif ($request->input('delete')){  //削除ボタンが押されたとき
            try {
                $db = Genre::find($data['id']);
                $db->deleted_at = date('Y-m-d H:i:s');
                $db->save();

                // 関連するコードも削除
                PromotionCode::where('genre_id', $data['id'])->delete();

                DB::commit();
            } catch (\PDOException $e) {
                DB::rollBack();
            }

            return redirect('genre');

        }

        //編集権限リスト(メンバー限定)
        if(Auth::user()->authority_id == 2){
            $user_id = Auth::user()->id;
            $edit_genres = UserGenre::where('user_id',$user_id)->where('category_type',2)->get()->pluck('genre_id')->toArray();
        }else{
            $edit_genres = '';
        }

        $all = PromotionCode::orderby('genre_id', 'asc')->get();
        $list = $all->groupBy('genre_id')->toArray();


        $past_month = [];
        for ($i = 1; $i <= 3; $i++){
            $past_month[$i]['name'] = date('Y年m月',strtotime('-'.$i.' month'));
            $past_month[$i]['year'] = date('Y',strtotime('-'.$i.' month'));
            $past_month[$i]['month'] = number_format(date('m',strtotime('-'.$i.' month')));
        }

        $code_data = [];
        foreach ($list as $key => $id) {
            $code_data[$key] = count($id);
        }

        $past_genre_all = PastGenre::all();
        $past_genre_all = $past_genre_all->groupBy('genre_id')->toArray();

        $past_genre = [];
        foreach ($past_genre_all as $key => $log_1){
            $past_genre[$key] = collect($log_1);
            $past_genre[$key] = $past_genre[$key]->groupBy('year')->toArray();

            foreach ($past_genre[$key] as $key2 => $log_2){
                $past_genre[$key][$key2] = collect($log_2);
                $past_genre[$key][$key2] = $past_genre[$key][$key2]->keyBy('month')->toArray();

                foreach ($past_genre[$key][$key2] as $key3=> $log_3){
                    $past_genre[$key][$key2][$key3] = $log_3['status_flag'];
                }
            }
        }

        return view('genre/top',[
            'past_month' => $past_month,
            'past_genre' => $past_genre,
            'code_data' => $code_data,
            'edit_genres' => $edit_genres,
            ]);
    }

}
