<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\API\ResponseController as ResponseController;
use Illuminate\Support\Facades\Auth;
use App\User;
use Validator;
use App\Profile;
use File;
use App\Match;
use URL;
use DB;
class ProfileController extends ResponseController
{
    public function create_profile(Request $request)
    {
        if($request->user_id == "" || empty($request->user_id))
        {
            $success['status'] = '0';
            $success['message'] = "User id is missing";
            return $this->sendResponse($success);
        }
        elseif($request->first_name == "" || empty($request->first_name))
        {
            $success['status'] = '0';
            $success['message'] = "First name is missing";
            return $this->sendResponse($success);   
        }
        elseif($request->last_name == "" || empty($request->last_name))
        {
            $success['status'] = '0';
            $success['message'] = "Last name is missing";
            return $this->sendResponse($success);   
        }
        elseif($request->image == "" || empty($request->image))
        {
            $success['status'] = '0';
            $success['message'] = "Image is missing";
            return $this->sendResponse($success);   
        }

        $input = $request->all();
        $imageName = time().'.'.request()->image->getClientOriginalExtension();
        request()->image->move(public_path('images/profile_images'), $imageName);
        $data = array();
        $profile = User::find($input['user_id']);
    	if($profile->role_id == 1)
    	{
    		if($input['club_address'] != '' || $input['club_address'] != false)
    		{
    			$data['club_address'] = $input['club_address'];
    		}
    		if($input['city'] != '' || $input['city'] != false)
    		{
    			$data['city'] = $input['city'];
    		}
    		if($input['state'] != '' || $input['state'] != false)
    		{
    			$data['state'] = $input['state'];
    		}
    		if($input['zip_code'] != '' || $input['zip_code'] != false)
    		{
    			$data['zip_code'] = $input['zip_code'];
    		}
    		if($input['home_field_address'] != '' || $input['home_field_address'] != false)
    		{
    			$data['home_field_address'] = $input['home_field_address'];
    		}
    		if($input['home_field_city'] != '' || $input['home_field_city'] != false)
    		{
    			$data['home_field_city'] = $input['home_field_city'];
    		}
    		if($input['home_field_state'] != '' || $input['home_field_state'] != false)
    		{
    			$data['home_field_state'] = $input['home_field_state'];
    		}
    		if($input['home_field_zipcode'] != '' || $input['home_field_zipcode'] != false)
    		{
    			$data['home_field_zipcode'] = $input['home_field_zipcode'];
    		}
    		if($input['pitch_type'] != '' || $input['pitch_type'] != false)
    		{
    			$data['pitch_type'] = $input['pitch_type'];
    		}
    		if($input['capacity'] != '' || $input['capacity'] != false)
    		{
    			$data['capacity'] = $input['capacity'];
    		}
    		if($input['website'] != '' || $input['website'] != false)
    		{
    			$data['website'] = $input['website'];
    		}
    		if($input['facebook'] != '' || $input['facebook'] != false)
    		{
    			$data['facebook'] = $input['facebook'];
    		}
    		if($input['instagram'] != '' || $input['instagram'] != false)
    		{
    			$data['instagram'] = $input['instagram'];
    		}
    		if($input['twitter'] != '' || $input['twitter'] != false)
    		{
    			$data['twitter'] = $input['twitter'];
    		}
    		if($input['coach_name'] != '' || $input['coach_name'] != false)
    		{
    			$data['coach_name'] = $input['coach_name'];
    		}
    	}
    	elseif($profile->role_id == 2)
    	{
    		if($input['dob'] != '' || $input['dob'] != false)
    		{
    			$data['dob'] = $input['dob'];
    		}
    		if($input['gender'] != '' || $input['gender'] != false)
    		{
    			$data['gender'] = $input['gender'];
    		}
    		if($input['cob'] != '' || $input['cob'] != false)
    		{
    			$data['cob'] = $input['cob'];
    		}
    		if($input['cop'] != '' || $input['cop'] != false)
    		{
    			$data['cop'] = $input['cop'];
    		}
    		if($input['height'] != '' || $input['height'] != false)
    		{
    			$data['height'] = $input['height'];
    		}
    		if($input['weight'] != '' || $input['weight'] != false)
    		{
    			$data['weight'] = $input['weight'];
    		}
    		if($input['height'] != '' || $input['height'] != false)
    		{
    			$data['height'] = $input['height'];
    		}
    		if($input['height'] != '' || $input['height'] != false)
    		{
    			$data['height'] = $input['height'];
    		}
    		if($input['position'] != '' || $input['position'] != false)
    		{
    			$data['position'] = $input['position'];
    		}
    		
    	}
    	$get_profile = User::find($input['user_id'])->profile;
    	if($get_profile->is_profile_complete == 0)
    	{
    		$data['first_name'] = $input['first_name'];
    		$data['last_name'] = $input['last_name'];
    		$data['image'] = $imageName;
    		$user = Profile::where('user_id',$input['user_id'])->update($data);
    		if($user == 1)
    		{
    			$update_profile = array('is_profile_complete' => 1);
        		$profile = Profile::where('user_id',$input['user_id'])->update($update_profile);
        		if($profile == 1)
        		{
                    $user = $request->user();
                    $user_info = User::with('profile')->where('id',$user->id)->first();
                    $user_info['profile']->image = URL::to('/public/images/profile_images/'.$user_info['profile']->image);
                    $success['status'] = '1';
                    $success['message'] = "Profile completed";
                    $success['data'] = $user_info;
                    $success['token'] =  $user->createToken('token')->accessToken;
		            return $this->sendResponse($success);		
        		}
        		else
        		{
        			$success['status'] = "0";
		        	$success['message'] = "Profile is not completed";
		            return $this->sendResponse($success);	
        		}
    		}
    		else
    		{
    			$success['status'] = "0";
		       	$success['message'] = "User Profile is not complete";
		        return $this->sendResponse($success);
    		}
        	
    	}
    	elseif($get_profile->is_profile_complete == 1)
    	{
    		$img_path = public_path('images/profile_images/'.$get_profile->image);
    		if(File::exists($img_path)) 
    		{
			    File::delete($img_path);
			}
    		if($input['first_name'] != '' || $input['first_name'] != false)
    		{
    			$data['first_name'] = $input['first_name'];
    		}
    		if($input['last_name'] != '' || $input['last_name'] != false)
    		{
    			$data['last_name'] = $input['last_name'];
    		}
    		if($imageName != '' || $imageName!= false)
    		{
    			$data['image'] = $imageName;
    		}
    		$user = Profile::where('user_id',$input['user_id'])->update($data);
    		if($user == 1)
    		{

                $user = $request->user();
                $user_info = User::with('profile')->where('id',$user->id)->first();
                // dd(URL::to('/public/images/profile_images/'.$user_info['profile']->image));
                $user_info['profile']->image = URL::to('/public/images/profile_images/'.$user_info['profile']->image);
    			$success['status'] = "1";
		       	$success['message'] = "User Profile is Updated";
                $success['data'] = $user_info;
		        return $this->sendResponse($success);	
    		}
    		else
    		{
    			$success['status'] = "0";
		       	$success['message'] = "User Profile is not Updated";
		        return $this->sendResponse($success);	
    		}
    	}
    }

    public function profile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());       
        }
        if($request->user()->id == $request->user_id)
        {
            $user = User::find($request->user_id);
            if($user->role_id == 1)
            {
                $profile = Profile::select('team_name','coach_name','home_field_address','home_field_city','home_field_state','home_field_zipcode','website','facebook','instagram','twitter','image')->where('user_id',$request->user_id)->first();
                $profile->image = URL::to('/images/profile_image/').$profile->image; 
                
                $success['status'] = "1";
                $success['message'] = "Team Profile";
                $success['data'] = $profile;
                return $this->sendResponse($success);
            }
            else if($user->role_id == 2)
            {
                $profile = Profile::select('team_name','coach_name','home_field_address','home_field_city','home_field_state','home_field_zipcode','website','facebook','instagram','twitter','image')->where('profiles.user_id',$request->user_id)->first();
                $profile->image = URL::to('/images/profile_image/').$profile->image; 
                
                $matches = Match::select(DB::raw('count(game_id) as game_id'),'player_id',DB::raw('SUM(yellow) as yellow'),DB::raw('SUM(red) as red'),DB::raw('SUM(goals) as goals'),DB::raw('SUM(trophies) as trophies'),DB::raw('SUM(time) as time'))->where('player_id',$request->user_id)->get();
                // dd($matches);
                // $profile->game = count($matches);
                foreach ($matches as $key => $value) 
                {
                    // dd($value->yellow);
                    $profile->games = $value->game_id;
                    $profile->yellow = $value->yellow;
                    $profile->red = $value->red;
                    $profile->goals = $value->goals;
                    $profile->trophies = $value->trophies;
                    $profile->time = $value->time;
                }
                $success['status'] = "1";
                $success['message'] = "Player Profile";
                $success['data'] = $profile;
                return $this->sendResponse($success);   
            }
            
            // Profile::with('matches')->where('')
        }
        else
        {
            $success['message'] = "Unauthorized User";
            return $this->sendResponse($success);
        }
    }
}
