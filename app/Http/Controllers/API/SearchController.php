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
use App\TryoutPlayers;
class SearchController extends ResponseController
{
    public function search_tryout(Request $request)
    {
    	$longitude = $request->longitude;
    	$latitude = $request->latitude;
    	$miles = $request->miles;

    	$search_team_name = $request->search_team_name;
        if($request->player_id == "" || empty($request->player_id))
        {
            $success['status'] = '0';
            $success['message'] = "player id is missing";
            return $this->sendResponse($success);
        }
        if($latitude == 0)
        {
            $latitude = null;   
        }
        if($longitude == 0)
        {
            $longitude = null;   
        }
        if($miles == null || $miles == 0)
        {
            $miles = 1;
        }
    	if($search_team_name && $latitude == null && $longitude == null )
    	{
    		$results = Tryout::select('team_id','tryouts.id as tryout_id','team_name','costoftryout','dateoftryout','timeoftryout','tryouts.latitude as latitude','tryouts.longitude as longitude','street')->join('profiles','profiles.user_id','=','tryouts.team_id')->where('profiles.team_name', 'LIKE', "%{$search_team_name}%")->get();
            foreach ($results as $key => $value) {
                $leman_pro_fees = DB::table('lemanpro_fees')->first();
                $player_tryout = TryoutPlayers::where('tryout_id',$value->tryout_id)->where('player_id',$request->player_id)->first();
                if($player_tryout != null)
                {
                    $results[$key]['join_tryout'] = '1';
                }
                else
                {
                    $results[$key]['join_tryout'] = '0';   
                }
                $results[$key]['lemanpro_fees'] = $leman_pro_fees->lemanpro_fee;
            }
    	}
    	elseif($search_team_name && $latitude && $longitude)
    	{
    		$results = DB::select(DB::raw('SELECT team_id,tryouts.id as tryout_id,team_name,costoftryout,dateoftryout,timeoftryout,tryouts.latitude as latitude,tryouts.longitude as longitude,street, ( 3959 * acos( cos( radians(' . $latitude . ') ) * cos( radians( tryouts.latitude ) ) * cos( radians( tryouts.longitude ) - radians(' . $longitude . ') ) + sin( radians(' . $latitude .') ) * sin( radians(tryouts.latitude) ) ) ) AS distance FROM tryouts join profiles on profiles.user_id = tryouts.team_id where profiles.team_name LIKE "%'.$search_team_name.'%" HAVING distance < ' . $miles . ' ORDER BY distance') );
            foreach ($results as $key => $value) 
            {
                $leman_pro_fees = DB::table('lemanpro_fees')->first();
                $player_tryout = TryoutPlayers::where('tryout_id',$value->tryout_id)->where('player_id',$request->player_id)->first();
                if($player_tryout != null)
                {
                    $results[$key]->join_tryout = '1';
                }
                else
                {
                    $results[$key]->join_tryout = '0';   
                }
                $results[$key]->lemanpro_fees = $leman_pro_fees->lemanpro_fee;
            }
    	}
    	else
    	{
    		$results = DB::select(DB::raw('SELECT team_id,tryouts.id as tryout_id,team_name,costoftryout,dateoftryout,timeoftryout,tryouts.latitude as latitude,tryouts.longitude as longitude,street, ( 3959 * acos( cos( radians(' . $latitude . ') ) * cos( radians( tryouts.latitude ) ) * cos( radians( tryouts.longitude ) - radians(' . $longitude . ') ) + sin( radians(' . $latitude .') ) * sin( radians(tryouts.latitude) ) ) ) AS distance FROM tryouts join profiles on profiles.user_id = tryouts.team_id HAVING distance < ' . $miles . ' ORDER BY distance') );
            foreach ($results as $key => $value) 
            {
                $leman_pro_fees = DB::table('lemanpro_fees')->first();
                $player_tryout = TryoutPlayers::where('tryout_id',$value->tryout_id)->where('player_id',$request->player_id)->first();
                if($player_tryout != null)
                {
                    $results[$key]->join_tryout = '1';
                }
                else
                {
                    $results[$key]->join_tryout = '0';   
                }
                $results[$key]->lemanpro_fees = $leman_pro_fees->lemanpro_fee;
            }

    	}
        $success['status'] = "1";
		$success['message'] = " User";
        $success['data'] = $results;
        return $this->sendResponse($success);
    }

    public function search_player(Request $request)
    {
        $longitude = $request->longitude;
        $latitude = $request->latitude;
        $miles = $request->miles;
        $gender = $request->gender;
        $age = $request->age;
        $dob  = date('Y', strtotime($age . ' years ago'));
        if($latitude == 0)
        {
            $latitude = null;   
        }
        if($longitude == 0)
        {
            $longitude = null;   
        }
        if($gender && $latitude == null && $longitude == null && $age == null )
        {
            $results = Profile::select('user_id','first_name','last_name',DB::raw("CONCAT('".URL::to('/images/profile_images/')."/',image) AS imageurl"))->where('gender', 'LIKE', "%{$gender}%")->get();
        }
        elseif($age && $gender == null && $latitude == null && $longitude == null)
        {
            $results = Profile::select('user_id','first_name','last_name',DB::raw("CONCAT('".URL::to('/images/profile_images/')."/',image) AS imageurl"))->whereyear('dob','=',$dob)->get();
        }
        elseif($gender && $latitude && $longitude && $age)
        {
            // dd(33);
            $results = DB::select(DB::raw('SELECT id,user_id as player_id,first_name,last_name,latitude,longitude,image, ( 3959 * acos( cos( radians('.$latitude.') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('.$longitude.') ) + sin( radians('.$latitude.') ) * sin( radians(latitude) ) ) ) AS distance FROM profiles where profiles.gender LIKE "%'.$gender.'%" AND year(profiles.dob)  = '.$dob.' HAVING distance < ' . $miles . ' ORDER BY distance') );
        }
        elseif($gender && $age)
        {
            
            $results = Profile::select('user_id','first_name','last_name',DB::raw("CONCAT('".URL::to('/images/profile_images/')."/',image) AS imageurl"))->where('gender', 'LIKE', "%{$gender}%")->whereyear('dob','=',$dob)->get();
        }
        else
        {
            $results = DB::select(DB::raw('SELECT id,user_id as player_id,first_name,last_name,latitude,longitude,image, ( 3959 * acos( cos( radians('.$latitude.') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('.$longitude.') ) + sin( radians('.$latitude.') ) * sin( radians(latitude) ) ) ) AS distance FROM profiles HAVING distance < 150 ORDER BY distance') );
        }
        if(count($results) > 0)
        {
            $success['status'] = "1";
            $success['message'] = "Player available";
            $success['data'] = $results;
        }
        else
        {
            $success['status'] = "1";
            $success['message'] = "No Player to show";    
        }
        return $this->sendResponse($success);
    }
}
