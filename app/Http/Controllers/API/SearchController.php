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
use App\Profile;
class SearchController extends ResponseController
{
    public function search_tryout(Request $request)
    {
    	$longitude = $request->longtude;
    	$latitude = $request->latitude;
    	$miles = $request->miles;
    	// $gender = $request->gender;
    	// $age = $request->age;

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

    public function search_player(Request $request)
    {
        $longitude = $request->longtude;
        $latitude = $request->latitude;
        $miles = $request->miles;
        $gender = $request->gender;
        $age = $request->age;
        $bd  = date('Y', strtotime($age . ' years ago'));
        dd($bd);
        // $longitude = "";
        // $latitude = ""; 
        // $miles = 150;
        // $gender = "male";
        // $age = 24;

        if($gender && $latitude == null && $longitude == null && $age == null )
        {
            $results = Profile::select('user_id','first_name','last_name',DB::raw("CONCAT('".URL::to('/images/profile_images/')."/',image) AS imageurl"))->where('gender', 'LIKE', "%{$gender}%")->get();
        }
        elseif($age && $gender == null && $latitude == null && $longitude == null)
        {
            $results = Profile::select('user_id','first_name','last_name',DB::raw("CONCAT('".URL::to('/images/profile_images/')."/',image) AS imageurl"))->whereyear('dob','=',$age})->get();
        }
        elseif($gender && $latitude && $longitude && $age)
        {
            $results = DB::select(DB::raw('SELECT id,user_id.id as player_id,first_name,last_name,latitude,longitude,image, ( 3959 * acos( cos( radians(' . $latitude . ') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $longitude . ') ) + sin( radians(' . $latitude .') ) * sin( radians(latitude) ) ) ) AS distance FROM profiles where profiles.gender LIKE "%'.$gender.'%" && where profiles.dob LIKE "%'.$age.'%" HAVING distance < ' . $miles . ' ORDER BY distance') );
        }
        else
        {
            $results = DB::select(DB::raw('SELECT id,user_id.id as player_id,first_name,last_name,latitude,longitude,image, ( 3959 * acos( cos( radians(' . $latitude . ') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $longitude . ') ) + sin( radians(' . $latitude .') ) * sin( radians(latitude) ) ) ) AS distance FROM profiles HAVING distance < ' . $miles . ' ORDER BY distance') );

        }
        $success['status'] = "1";
        $success['message'] = " User";
        $success['data'] = $results;
        return $this->sendResponse($success);
    }
}
