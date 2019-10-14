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
class ProfileController extends ResponseController
{
    public function create_profile(Request $request)
    {
    	$validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());       
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
        			$success['status'] = "1";
		        	$success['message'] = "Profile completed";
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
    			$success['status'] = "1";
		       	$success['message'] = "User Profile is Updated";
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
}
