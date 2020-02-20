<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\API\ResponseController as ResponseController;
use Illuminate\Support\Facades\Auth;
use App\User;
use Validator;
use App\Rosters;
use DB;
use URL;
use App\Notification;
use App\Tryout;
use App\Profile;
use App\TryoutPlayers;
use App\Match;
use Carbon\Carbon;
class SearchController extends ResponseController
{
    public function search_tryout(Request $request)
    {
    	$longitude = $request->latitude;
    	$latitude = $request->longitude;
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
        
        $mytime = Carbon::today();
        $current_time = $mytime->toDateTimeString();
        // dd($current_time);
    	if($search_team_name && $latitude == null && $longitude == null )
    	{
    		$results = Tryout::select('team_id','tryouts.id as tryout_id','team_name','costoftryout','dateoftryout','timeoftryout','tryouts.latitude as latitude','tryouts.longitude as longitude','street')->join('profiles','profiles.user_id','=','tryouts.team_id')->where('profiles.team_name', 'LIKE', "%{$search_team_name}%")->where('tryouts.created_at','>=',$current_time)->get();
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
        $dob = '';
        if($request->gender == null)
        {
            $request->gender = '';
            $gender = $request->gender;
        }
        else
        {
            $gender = $request->gender;   
        }
        
        if(isset($request->age) && $request->age != '' || $request->age != null)
        {
            $age = $request->age;
            $dob  = date('Y', strtotime($age . ' years ago'));
        }
        if($miles == null || $miles == 0)
        {
            $miles = 1;
        }
        if($request->team_id == "" || empty($request->team_id))
        {
            $success['status'] = '0';
            $success['message'] = "team id is missing";
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
        if($gender && $latitude == null && $longitude == null && $dob == null )
        {
            $results = Profile::select('user_id as player_id',DB::raw('CONCAT('."first_name".'," ",'."last_name".') AS display_name'),DB::raw("CONCAT('".URL::to('public/images/profile_images/')."/',image) AS image"))->where('gender', 'LIKE', "%{$gender}%")->get();
            foreach ($results as $key => $value) 
            {
                $player_roster = Rosters::where('team_id',$request->team_id)->where('player_id',$value->player_id)->first();
                if($player_roster == null)
                {
                    $results[$key]->team_member = '0';   
                } 
                if($player_roster['request'] == 0)
                {
                    $results[$key]->team_member = '0';
                }
                if($player_roster['request'] == 1)
                {
                    $results[$key]->team_member = '1';
                }
                elseif($player_roster['request'] == 2)
                {
                    $results[$key]->team_member = '2';
                }
            }
        }
        elseif($dob && $gender == null && $latitude == null && $longitude == null)
        {
            $results = Profile::select('user_id as player_id',DB::raw('CONCAT('."first_name".'," ",'."last_name".') AS display_name'),DB::raw("CONCAT('".URL::to('public/images/profile_images/')."/',image) AS image"))->whereyear('dob','=',$dob)->get();
            foreach ($results as $key => $value) 
            {
                $player_roster = Rosters::where('team_id',$request->team_id)->where('player_id',$value->player_id)->first();
                if($player_roster == null)
                {
                    $results[$key]->team_member = '0';   
                }
                if($player_roster['request'] == 0)
                {
                    $results[$key]->team_member = '0';
                }
                if($player_roster['request'] == 1)
                {
                    $results[$key]->team_member = '1';
                }
                elseif($player_roster['request'] == 2)
                {
                    $results[$key]->team_member = '2';
                }
            }
        }
        elseif($gender && $latitude && $longitude && $dob)
        {
            $results = DB::select(DB::raw('SELECT id,user_id as player_id,CONCAT(first_name," ",last_name) as display_name ,latitude,longitude,image, ( 3959 * acos( cos( radians('.$latitude.') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('.$longitude.') ) + sin( radians('.$latitude.') ) * sin( radians(latitude) ) ) ) AS distance FROM profiles where profiles.gender LIKE "%'.$gender.'%" AND year(profiles.dob)  = '.$dob.' HAVING distance < ' . $miles . ' ORDER BY distance') );
            foreach ($results as $key => $value) 
            {
                dd($dob);
                $player_roster = Rosters::where('team_id',$request->team_id)->where('player_id',$value->player_id)->first();
                $result[$key]['display_name'] = $value->display_name;
                $value->image = URL::to('public/images/profile_images/').'/'.$value->image;
                if($player_roster == null)
                {
                    $results[$key]->team_member = '0';   
                }  
                if($player_roster['request'] == 0)
                {
                    $results[$key]->team_member = '0';
                }
                if($player_roster['request'] == 1)
                {
                    $results[$key]->team_member = '1';
                }
                elseif($player_roster['request'] == 2)
                {
                    $results[$key]->team_member = '2';
                }
            }
        }
        elseif($gender == null && $latitude && $longitude && $dob)
        {
            // dd(445);
            $results = DB::select(DB::raw('SELECT id,user_id as player_id,CONCAT(first_name," ",last_name) as display_name ,latitude,longitude,image, ( 3959 * acos( cos( radians('.$latitude.') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('.$longitude.') ) + sin( radians('.$latitude.') ) * sin( radians(latitude) ) ) ) AS distance FROM profiles where year(profiles.dob)  = '.$dob.' HAVING distance < ' . $miles . ' ORDER BY distance') );
            foreach ($results as $key => $value) 
            {
                // dd($value);
                $player_roster = Rosters::where('team_id',$request->team_id)->where('player_id',$value->player_id)->first();
                $result[$key]['display_name'] = $value->display_name;
                $value->image = URL::to('public/images/profile_images/').'/'.$value->image;
                if($player_roster == null)
                {
                    $results[$key]->team_member = '0';   
                }
                if($player_roster['request'] == 0)
                {
                    $results[$key]->team_member = '0';
                }
                if($player_roster['request'] == 1)
                {
                    $results[$key]->team_member = '1';
                }
                elseif($player_roster['request'] == 2)
                {
                    $results[$key]->team_member = '2';
                }
            }
        }
        elseif($gender && $latitude && $longitude && $dob == null)
        {
            // dd(445);
            $results = DB::select(DB::raw('SELECT id,user_id as player_id,CONCAT(first_name," ",last_name) as display_name ,latitude,longitude,image, ( 3959 * acos( cos( radians('.$latitude.') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('.$longitude.') ) + sin( radians('.$latitude.') ) * sin( radians(latitude) ) ) ) AS distance FROM profiles where profiles.gender LIKE "%'.$gender.'%" HAVING distance < ' . $miles . ' ORDER BY distance') );
            
            foreach ($results as $key => $value) 
            {
                // dd($value);
                $player_roster = Rosters::where('team_id',$request->team_id)->where('player_id',$value->player_id)->first();
                $result[$key]['display_name'] = $value->display_name;
                $value->image = URL::to('public/images/profile_images/').'/'.$value->image;
                if($player_roster == null)
                {
                    $results[$key]->team_member = '0';   
                }
                if($player_roster['request'] == 0)
                {
                    $results[$key]->team_member = '0';
                }
                if($player_roster['request'] == 1)
                {
                    $results[$key]->team_member = '1';
                }
                elseif($player_roster['request'] == 2)
                {
                    $results[$key]->team_member = '2';
                }
            }
        }
        elseif($gender && $dob)
        {
            // dd($dob);
            $results = Profile::select('user_id as player_id',DB::raw('CONCAT('."first_name".'," ",'."last_name".') AS display_name'),DB::raw("CONCAT('".URL::to('public/images/profile_images/')."/',image) AS image"))->where('gender', 'LIKE', "%{$gender}%")->whereyear('dob','=',$dob)->get();
            foreach ($results as $key => $value) 
            {
                $player_roster = Rosters::where('team_id',$request->team_id)->where('player_id',$value->player_id)->first();
                if($player_roster == null)
                {
                    $results[$key]->team_member = '0';   
                }  
                if($player_roster['request'] == 0)
                {
                    $results[$key]->team_member = '0';
                }
                if($player_roster['request'] == 1)
                {
                    $results[$key]->team_member = '1';
                }
                elseif($player_roster['request'] == 2)
                {
                    $results[$key]->team_member = '2';
                }
            }
        }
        elseif($gender =='' && $dob == '' && $latitude == '' && $longitude == '')
        {
            
            $results = Profile::select('user_id as player_id',DB::raw('CONCAT('."first_name".'," ",'."last_name".') AS display_name'),DB::raw("CONCAT('".URL::to('public/images/profile_images/')."/',image) AS image"))->get();
            foreach ($results as $key => $value) 
            {
                $player_roster = Rosters::where('team_id',$request->team_id)->where('player_id',$value->player_id)->first();
                if($player_roster == null)
                {
                    $results[$key]->team_member = '0';   
                }  
                if($player_roster['request'] == 0)
                {
                    $results[$key]->team_member = '0';
                }
                if($player_roster['request'] == 1)
                {
                    $results[$key]->team_member = '1';
                }
                elseif($player_roster['request'] == 2)
                {
                    $results[$key]->team_member = '2';
                }
            }
        }
        else
        {
            // dd($request->input());
            $results = DB::select(DB::raw('SELECT id,user_id as player_id,CONCAT(first_name," ",last_name) as display_name,latitude,longitude,image, ( 3959 * acos( cos( radians('.$latitude.') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('.$longitude.') ) + sin( radians('.$latitude.') ) * sin( radians(latitude) ) ) ) AS distance FROM profiles HAVING distance < ' . $miles . ' ORDER BY distance') );

            foreach ($results as $key => $value) 
            {
                $player_roster = Rosters::where('team_id',$request->team_id)->where('player_id',$value->player_id)->first();
                $results[$key]->image = URL::to('public/images/profile_images/').'/'.$value->image;
                // $result[$key]['display_name'] = $value->first_name.' '.$value->last_name;
                if($player_roster == null)
                {
                    $results[$key]->team_member = '0';   
                }
                if($player_roster['request'] == 0)
                {
                    $results[$key]->team_member = '0';
                }
                if($player_roster['request'] == 1)
                {
                    $results[$key]->team_member = '1';
                }
                elseif($player_roster['request'] == 2)
                {
                    $results[$key]->team_member = '2';
                }
            }
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

    public function search_player_profile(Request $request)
    {
        if($request->player_id == "" || empty($request->player_id))
        {
            $success['status'] = '0';
            $success['message'] = "player id is missing";
            return $this->sendResponse($success);
        }
        else
        {
            
            $team_id = auth()->guard('api')->user()->id;
            $profile = Profile::select('user_id','first_name','last_name','dob','gender','cob','cop','height','weight','position','twitter','image')->where('profiles.user_id',$request->player_id)->first();
            $profile->image = URL::to('public/images/profile_images/').'/'.$profile->image; 
            
            $matches = Match::select(DB::raw('count(game_id) as game_id'),'player_id',DB::raw('SUM(yellow) as yellow'),DB::raw('SUM(red) as red'),DB::raw('SUM(goals) as goals'),DB::raw('SUM(own_goal) as own_goal'),DB::raw('SUM(trophies) as trophies'),DB::raw('SUM(time) as time'))->where('player_id',$request->player_id)->get();
            
            $team_joined = Rosters::join('profiles','profiles.user_id','=','rosters.team_id')->where('player_id',$request->player_id)->where('request',1)->select('team_name','team_id')->first();
            if($team_joined == null)
            {
               $profile->team_name = ''; 
            }
            else
            {
               $profile->team_name = $team_joined->team_name;
               $profile->team_id = $team_joined->team_id;
               
            }
            $check_player_belongs_to_team =  Rosters::join('profiles','profiles.user_id','=','rosters.team_id')->where('team_id',$team_id)->where('player_id',$request->player_id)->select('team_name','request')->first();
            if($check_player_belongs_to_team == null || $check_player_belongs_to_team->request == '0')
            {
                $profile->team_member = '0';
            }
            elseif($check_player_belongs_to_team->request == 2)
            {
                $profile->team_member = '2';
            }
            else
            {
                $profile->team_member = '1';
            }
            foreach ($matches as $key => $value) 
            {
                // dd($value->yellow);
                $profile->games = strval($value->game_id);
                $profile->yellow = strval($value->yellow);
                $profile->red = strval($value->red);
                $profile->goals = strval($value->goals);
                $profile->own_goal = strval($value->own_goal);
                $profile->trophies = strval($value->trophies);
                $profile->time = strval($value->time);
            }
            $success['status'] = "1";
            $success['message'] = "Player Profile";
            $success['data'] = $profile;
            return $this->sendResponse($success);  
        } 
    }
}
