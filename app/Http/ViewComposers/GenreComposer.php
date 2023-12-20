<?php

namespace App\Http\ViewComposers;

use App\Models\Genre;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class GenreComposer
{

    /**
     * Bind data to the view.
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {

        if(Auth::check()){
            if(Auth::user()->authority_id == 2){
            $user_id = Auth::user()->id;

            $pre_data = Genre::where('deleted_at', null)->when($user_id, function ($query, $user_id) {
                return $query->whereHas('genres', function (Builder $query) use ($user_id) {
                    $query->where('user_id', $user_id)->where(function ($query) {
                        $query->where('category_type', 2)->orWhere('category_type', 1);
                    });
                });
            })->with('promotion_codes')->whereHas('promotion_codes', function($query){
                $query->whereExists(function($query){
                    return $query;
                });
            })
                ->orderBy('display_num')->get();
            $data = $pre_data->keyBy('id')->all();
            $view->with('genres', $data);
        }else{
            $pre_data = Genre::where('deleted_at', null)->orderBy('display_num')
                ->with('promotion_codes')->whereHas('promotion_codes', function($query){
                    $query->whereExists(function($query){
                        return $query;
                    });
                })->get();
            $data = $pre_data->keyBy('id')->all();
            $view->with('genres', $data);
        }
        }
    }
}
