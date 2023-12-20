<?php

namespace App\Http\Controllers;

use App\Models\ScrapingLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ScrapingController extends Controller
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

    public function log(Request $request)
    {
        return view('scraping/log', [
            'logs' => ScrapingLog::limit(200)->orderby('created_at','DESC')->get(),
            ]);
    }

}
