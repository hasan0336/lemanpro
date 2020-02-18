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
	Route::get('single_tryout_info','API\TryoutController@single_tryout_info');
	Route::get('tryout_participants','API\TryoutController@tryout_participants');
	
	Route::post('send_request','API\RosterController@send_request');
	Route::get('roster_requests','API\RosterController@roster_requests');
	Route::get('action_request','API\RosterController@action_request');
	Route::get('roster_listing','API\RosterController@roster_listing');
	Route::get('delete_player','API\RosterController@delete_player');
	Route::get('remove_player_from_team','API\RosterController@remove_player_from_team');
	Route::get('get_notification_list','API\RosterController@get_notification_list');

	




	Route::post('create_game','API\GameController@create_game');
	Route::post('add_score_sheet','API\GameController@add_score_sheet');
	Route::post('update_score_sheet','API\GameController@update_score_sheet');
	Route::get('delete_game','API\GameController@delete_game');
	Route::get('report_to_manager','API\GameController@report_to_manager');
	Route::get('players_team_list','API\GameController@players_team_list');
	Route::get('start_match','API\GameController@start_match');
	Route::get('pause_game','API\GameController@pause_game');
	Route::get('substitute_player','API\GameController@substitute_player');
	Route::get('sub_players_list','API\GameController@sub_players_list');
	Route::get('end_match','API\GameController@end_match');
	Route::get('check_game','API\GameController@check_game');
	Route::get('get_player_match_data','API\GameController@get_player_match_data');
	Route::get('del_player_match_data','API\GameController@del_player_match_data');
	


	Route::post('create_news','API\NewsController@create_news');
	Route::post('edit_news','API\NewsController@edit_news');
	Route::post('help_feedback','API\NewsController@help_feedback');
	Route::get('delete_news','API\NewsController@delete_news');
	Route::get('news_listing','API\NewsController@news_listing');
	Route::get('player_news_listing','API\NewsController@player_news_listing');
	Route::get('content','API\NewsController@content');


	
	




	Route::post('logout','API\AuthController@logout');

});
Route::get('search_tryout','API\SearchController@search_tryout');
Route::get('search_player','API\SearchController@search_player');
Route::get('search_player_profile','API\SearchController@search_player_profile');
// Route::middleware(['auth:api','client.credentials'])->get('/user', function (Request $request) {

// 	Route::post('create_profile', 'API\ProfileController@create_profile');
// });
