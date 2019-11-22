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
use App\Tryout;
class SearchController extends ResponseController
{
    public function search_tryout(Request $request)
    {
    	$longitude = $request->longtude;
    	$latitude = $request->latitude;
    	$miles = $request->miles;
    	$gender = $request->gender;
    	$age = $request->age;

    	// $longitude = "";
    	// $latitude = ""; 
    	// $miles = 150;
    	// $gender = "male";
    	// $age = 24;
    	$search_team_name = $request->search_team_name;
    	if($search_team_name && $latitude == null && $longitude == null )
    	{
    		$results = Tryout::select('*')->join('profiles','profiles.user_id','=','tryouts.team_id')->where('profiles.team_name', 'LIKE', "%{$search_team_name}%")->get();
    	}
    	elseif($search_team_name && $latitude && $longitude)
    	{
    		$results = DB::select(DB::raw('SELECT team_id,tryouts.id as tryout_id,team_name,costoftryout,dateoftryout,timeoftryout,latitude,longitude,street, ( 3959 * acos( cos( radians(' . $latitude . ') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $longitude . ') ) + sin( radians(' . $latitude .') ) * sin( radians(latitude) ) ) ) AS distance FROM tryouts join profiles on profiles.user_id = tryouts.team_id where profiles.team_name LIKE "%'.$search_team_name.'%" HAVING distance < ' . $miles . ' ORDER BY distance') );
    	}
    	else
    	{
    		$results = DB::select(DB::raw('SELECT team_id,tryouts.id as tryout_id,team_name,costoftryout,dateoftryout,timeoftryout,latitude,longitude,street, ( 3959 * acos( cos( radians(' . $latitude . ') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $longitude . ') ) + sin( radians(' . $latitude .') ) * sin( radians(latitude) ) ) ) AS distance FROM tryouts join profiles on profiles.user_id = tryouts.team_id HAVING distance < ' . $miles . ' ORDER BY distance') );

    	}
        $success['status'] = "1";
		$success['message'] = " User";
        $success['data'] = $results;
        return $this->sendResponse($success);
    }
}
