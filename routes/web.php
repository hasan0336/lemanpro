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

Route::get('/', function () {
    return view('welcome');
});
// Route::get('admin', function () {
// 	dd('i am admin');
// })->middleware('admin');
// Route::get('admin','AdminController@login_admin')->name('login_admin')->middleware('admin');
Route::group(['middleware' => ['admin']], function () {
    Route::get('admin','AdminController@login_admin')->name('login_admin');
    Route::get('admin/dashboard','AdminController@index')->name('dashboard');
    Route::post('admin/update_admin','AdminController@update_admin')->name('update_admin');
    Route::get('admin/palyer_management','AdminController@palyer_management')->name('palyer_management');
    Route::get('logout', 'Auth\LoginController@logout')->name('logout');
    // Route::post('admin/dashboard','AdminController@signin_admin')->name('signin_admin');
});
Route::get('register/verify/{token}', 'API\AuthController@verify')->name('verified_email');
Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
