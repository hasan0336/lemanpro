<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Validator;
use App\Rosters;
use DB;
use URL;
use App\Notification;
class SearchController extends ResponseController
{
    public function search_player(Request $request)
    {
    	$longitude = $request->longtude;
    	$latitude = $request->latitude;
    	$miles = $request->miles;
    	$gender = $request->gender;
    	$age = $request->age;

    	$longitude = "67.4";
    	$latitude = "27.3"; 
    	$miles = 10;
    	$gender = "male";
    	$age = 24;


    	$awaka = "SELECT *, ( 3959 * acos( cos( radians($latitude) ) * cos( radians( latitude ) ) * 
            					cos( radians( longitude ) - radians($longitude) ) + sin( radians($latitude) ) * 
            					sin( radians( latitude ) ) ) ) AS distance FROM tryouts HAVING
            					distance < ".$miles." ORDER BY distance LIMIT 0 , 20;";
        $result = DB::select($awaka);

        $success['status'] = "1";
		$success['message'] = " User";
        $success['data'] = $result;
        return $this->sendResponse($success);
    }
}
