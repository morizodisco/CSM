<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/login');
});

Auth::routes();

// MemberPage
Route::get('/member', 'MemberController@index');
Route::post('/member', 'MemberController@index');

// RegisterPage
Route::get('/register', 'RegisterController@index');
Route::post('/register', 'RegisterController@index');
Route::get('/register/yesterday', 'RegisterController@yesterday');
Route::post('/register/yesterday', 'RegisterController@yesterday');

// CodePage
Route::get('/code', 'CodeController@index');
Route::post('/code', 'CodeController@index');

// PromotionPage
Route::get('/promotion', 'PromotionController@index');
Route::post('/promotion', 'PromotionController@index');

// GenrePage
Route::get('/genre', 'GenreController@index');
Route::post('/genre', 'GenreController@index');

// GenrePage
Route::get('/report', 'ReportController@index');
Route::post('/report', 'ReportController@index');
Route::get('/report/test', 'ReportController@test');
Route::post('/report/test', 'ReportController@test');
Route::get('/report/detail/{genre_id}', 'ReportController@detail');
Route::post('/report/detail/{genre_id}', 'ReportController@detail');
Route::get('/report/detail_test/{genre_id}', 'ReportController@detail_test');
Route::post('/report/detail_test/{genre_id}', 'ReportController@detail_test');

//Ajax
Route::post('/ajax/code_total', 'AjaxController@code_total');
Route::post('/ajax/note_total', 'AjaxController@note_total');
Route::post('/ajax/code_item', 'AjaxController@code_item');
Route::post('/ajax/genre_note', 'AjaxController@genre_note');
Route::post('/ajax/report_minutes', 'AjaxController@report_minutes');

//Scraping
Route::get('/scraping/log', 'ScrapingController@log');

//Macro
Route::get('/macro/show_media', 'MacroController@show_media');
Route::get('/macro/show_promotion', 'MacroController@show_promotion');
Route::get('/macro/show_code_url', 'MacroController@show_code_url');
Route::get('/macro/health_check_url', 'MacroController@health_check_url');
Route::get('/macro/health_check_register', 'MacroController@health_check_register');
Route::post('/macro/health_check_register', 'MacroController@health_check_register');

Route::get('/test/check', 'TestController@check');