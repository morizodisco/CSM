<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;

class WeekComposer
{

    /**
     * Bind data to the view.
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
        $week = [
            array('day' =>'日','class' => 'sunday'),
            array('day' =>'月','class' => ''),
            array('day' =>'火','class' => ''),
            array('day' =>'水','class' => ''),
            array('day' =>'木','class' => ''),
            array('day' =>'金','class' => ''),
            array('day' =>'土','class' => 'saturday'),
        ];
        $view->with('week', $week);
    }
}
