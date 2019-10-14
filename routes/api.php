<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/





Route::post('login', 'API\AuthController@login');
Route::post('signup', 'API\AuthController@signup');
Route::post('otp', 'API\AuthController@otp');
Route::group(['middleware' => ['auth:api','client.credentials']], function(){

	Route::post('create_profile', 'API\ProfileController@create_profile');
	Route::post('changePassword', 'API\AuthController@changePassword');
	Route::post('create_tryout', 'API\TryoutController@create_tryout');
	Route::post('update_tryout', 'API\TryoutController@update_tryout');
	Route::get('tryout_listing','API\TryoutController@tryout_listing');
	Route::get('del_tryout','API\TryoutController@del_tryout');
	Route::post('join_tryout','API\TryoutController@join_tryout');
	Route::get('tryout_players_list','API\TryoutController@tryout_players_list');

});
// Route::middleware(['auth:api','client.credentials'])->get('/user', function (Request $request) {

// 	Route::post('create_profile', 'API\ProfileController@create_profile');
// });
