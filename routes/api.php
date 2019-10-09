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
Route::group(['middleware' => ['auth:api','client.credentials']], function(){
	Route::post('create_profile', 'API\ProfileController@create_profile');
});
// Route::middleware(['auth:api','client.credentials'])->get('/user', function (Request $request) {

// 	Route::post('create_profile', 'API\ProfileController@create_profile');
// });
