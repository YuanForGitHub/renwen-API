<?php

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

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/error', 'Controller@error');

//Route::get('/test', 'LendController@test');


Route::get('/login', 'Controller@login');
Route::any('/token', 'Controller@getToken');

/**
 * 验证码
 */
//Route::get('/lend/captcha', 'LendController@sentCaptcha');
//Route::post('/lend/captcha', 'LendController@checkCaptcha');

/**
 * TODO:以下路由进行验证
 */
Route::middleware(['Check'])->group(function(){
    Route::get('/user/info', 'Controller@getInfo');

    Route::post('/lend/create', 'LendController@create');
    Route::get('/lend/check', 'LendController@check');
    Route::get('/lend/show', 'LendController@show');
    Route::get('/lend/pdf', 'LendController@loadPdf');
//    Route::get('/lend/getpdf', 'LendController@')

    Route::post('/admin/judge', 'LendController@judge');

    Route::get('/logout', 'Controller@logout');
});
