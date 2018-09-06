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
//Route::middleware(['Check'])->group(function(){
    // 获取用户信息
    Route::get('/user/info', 'Controller@getInfo');

    // 申请提交
    Route::post('/lend/create', 'LendController@create');
    // 获取自己未过期的所有申请
    Route::get('/lend/check', 'LendController@check');
    // 获取所有人未过期的申请
    Route::get('/lend/show', 'LendController@show');
    // 下载申请成功的表格
    Route::get('/lend/pdf', 'LendController@loadPdf');

    // 管理员审核
    Route::post('/admin/judge', 'LendController@judge');
    // 管理员撤回审核结果（重置）
    Route::post('/admin/withdraw', 'LendController@withdraw');

    // 退出当前登录
    Route::get('/logout', 'Controller@logout');
//});

