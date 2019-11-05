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
	Route::get('profile','API\ProfileController@profile');

	Route::post('changePassword', 'API\AuthController@changePassword');
	Route::post('create_tryout', 'API\TryoutController@create_tryout');
	Route::post('update_tryout', 'API\TryoutController@update_tryout');
	Route::get('tryout_listing','API\TryoutController@tryout_listing');
	Route::get('del_tryout','API\TryoutController@del_tryout');
	Route::post('join_tryout','API\TryoutController@join_tryout');
	Route::get('tryout_players_list','API\TryoutController@tryout_players_list');
	Route::post('send_request','API\RosterController@send_request');
	Route::get('roster_requests','API\RosterController@roster_requests');
	Route::get('action_request','API\RosterController@action_request');
	Route::get('roster_listing','API\RosterController@roster_listing');
	Route::get('delete_player','API\RosterController@delete_player');




	Route::post('create_game','API\GameController@create_game');
	Route::post('add_score_sheet','API\GameController@add_score_sheet');
	Route::get('delete_game','API\GameController@delete_game');
	Route::get('report_to_manager','API\GameController@report_to_manager');


	Route::post('create_news','API\NewsController@create_news');

});
// Route::middleware(['auth:api','client.credentials'])->get('/user', function (Request $request) {

// 	Route::post('create_profile', 'API\ProfileController@create_profile');
// });
