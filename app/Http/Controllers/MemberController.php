<?php
namespace App\Http\Controllers;
use App\Models\Genre;
use App\Models\User;
use App\Models\UserGenre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MemberController extends Controller
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

        if(Auth::user()->authority_id == 2){
            return redirect('/report');
        }

        $data = $request->all();

        //ボタンのname値による条件分岐
        if ($request->input('update')){ //更新ボタンが押されたとき

            DB::beginTransaction();
            try {
                $user = User::firstOrCreate(['id' => $request->id]);

                $data['password'] = Hash::make($data['pre_password']);

                if($request->hasFile('image_path')){
                    $path = $request->file('image_path')->store('public/icon');
                    $data['img_path'] = str_replace('public/', '', $path);
                }

                $user->fill($data)->save();

                // 権限を更新
                UserGenre::where(['user_id' => $request->id])->delete(); // 更新前に既存の登録済みデータを削除
                if ($request->filled('charge_category')) {
                    $charge_category = [];
                    foreach ($request->charge_category AS $id) {
                        $temp = [];
                        $temp['user_id'] = $request->id;
                        $temp['genre_id'] = $id;
                        $temp['category_type'] = 3;
                        $temp['created_at'] = date('Y-m-d');
                        $charge_category[] = $temp;
                    }
                    UserGenre::insert($charge_category);
                }
                if ($request->filled('display_category')) {
                    $display_category = [];
                    foreach ($request->display_category AS $id) {
                        $temp = [];
                        $temp['user_id'] = $request->id;
                        $temp['genre_id'] = $id;
                        $temp['category_type'] = 1;
                        $temp['created_at'] = date('Y-m-d');
                        $display_category[] = $temp;
                    }
                    UserGenre::insert($display_category);
                }
                if ($request->filled('edit_category')) {
                    $edit_category = [];
                    foreach ($request->edit_category AS $id) {
                        $temp = [];
                        $temp['user_id'] = $request->id;
                        $temp['genre_id'] = $id;
                        $temp['category_type'] = 2;
                        $temp['created_at'] = date('Y-m-d');
                        $edit_category[] = $temp;
                    }
                    UserGenre::insert($edit_category);
                }
                DB::commit();
            } catch (\PDOException $e) {
                DB::rollBack();
            }

            return redirect('member');

        }elseif ($request->input('delete')){ //削除ボタンが押されたとき
            try {
                $db = User::find($data['id']);
                $db->delete();

                DB::commit();
            } catch (\PDOException $e) {
                DB::rollBack();
            }

            return redirect('member');

        }elseif ($request->input('add')){

            $data['pre_password'] = $data['password'] = substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyz', 6)), 0, 6);
            $data['password'] = Hash::make($data['password']);
            if($request->hasFile('image_path')){
                $path = $request->file('image_path')->store('public/icon');
                $data['img_path'] = str_replace('public/', '', $path);
            }

            $user = new User();
            $user->fill($data)->save();

            return redirect('member');
        }
        $users = User::where('status_flag', 1)->get();

        //var_dump(Hash::make('uhsau2'));

        $genre = Genre::where('deleted_at', null)->orderBy('display_num')
            ->with('promotion_codes')->whereHas('promotion_codes', function($query){
                $query->whereExists(function($query){
                    return $query;
                });
            })->get();

        return view('member/top',[
            'users' => $users,
            'available_genre' => $genre,
            ]);
    }


    protected function loggedOut()
    {
        Auth::logout();
        return redirect('/login');
    }
}
