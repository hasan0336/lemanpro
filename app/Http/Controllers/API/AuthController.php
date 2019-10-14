<?php

namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use App\Http\Controllers\API\ResponseController as ResponseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\User;
use Validator;
use Mail;
use App\Mail\UserNotification;
use App\Mail\NewOtp;
use App\Profile;
use Hash;
// use Illuminate\Support\Facades\Mail;
class AuthController extends ResponseController
{
    //create user
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|unique:users',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
            'device_type' => 'required',
            'device_token' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors());       
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $input['login_type'] = "email";
        $input['email_token'] = md5(uniqid());

        $user = User::create($input);
        if($user)
        {
        	$data = array('user_id' => $user->id);
        	$pro = Profile::create($data);

        	Mail::to($request->email)->send(new UserNotification($user));
            $success['token'] =  $user->createToken('token')->accessToken;
            $success['message'] = "Registration successfull..";
            return $this->sendResponse($success);
        }
        else
        {
            $error = "Sorry! Registration is not successfull.";
            return $this->sendError($error, 401); 
        }
        
    }
    
    //login
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors());       
        }

        $credentials = request(['email', 'password']);
        if(!Auth::attempt($credentials)){
            $error = "Unauthorized";
            return $this->sendError($error, 401);
        }
        $user = $request->user();
        $user_info = User::with('profile')->where('id',$user->id)->first();

        // $user_info = User::find($user->id);
        // $profile = $user->profile()->first();
        // $user_info->last_name = $profile->last_name;
        // dd($user_info);
        
        $success['data'] = $user_info;
        $success['token'] =  $user->createToken('token')->accessToken;
        return $this->sendResponse($success);
    }

    //logout
    public function logout(Request $request)
    {
        
        $isUser = $request->user()->token()->revoke();
        if($isUser){
            $success['message'] = "Successfully logged out.";
            return $this->sendResponse($success);
        }
        else{
            $error = "Something went wrong.";
            return $this->sendResponse($error);
        }  
    }

    //getuser
    public function getUser(Request $request)
    {
        //$id = $request->user()->id;
        $user = $request->user();
        if($user){
            return $this->sendResponse($user);
        }
        else{
            $error = "user not found";
            return $this->sendResponse($error);
        }
    }

    public function verify($token)
    {
        // The verified method has been added to the user model and chained here
        // for better readability


        $user = User::where('email_token','=',$token)->get();

        if(count($user)>0)
        {
            $user2 = User::where('email_token','=',$token)->first();

            $result = User::where('email_token',$token)->update(['verify_status'=> '1', 'email_token'=> null]);
            if($result)
            {
            	echo 'Your account is successfully verified';	
            }
            else
			{
				echo "Failed to Verify. Kindly contact with out support team";
			}
        }
        else
        {
            echo 'Your account is already verified';

        }

        // return redirect('login');
    }

    function changePassword(Request $request)
    {
		$data = $request->all();
		$user = Auth::guard('api')->user();

		 //Changing the password only if is different of null
		if( isset($data['oldPassword']) && !empty($data['oldPassword']) && $data['oldPassword'] !== "" && $data['oldPassword'] !=='undefined') 
		{
		    //checking the old password first
		    $check  = Auth::guard('web')->attempt([
		        'email' => $user->email,
		        'password' => $data['oldPassword']
		    ]);
		    if($check && isset($data['newPassword']) && !empty($data['newPassword']) && $data['newPassword'] !== "" && $data['newPassword'] !=='undefined') 
		    {
		        $user->password = bcrypt($data['newPassword']);
		        // $user->isFirstTime = false; //variable created by me to know if is the dummy password or generated by user.
		        // $user->token()->revoke();
		        // $token = $user->createToken('newToken')->accessToken;

		        //Changing the type
		        $a = $user->save();
		        // dd($a);
		        $success['status'] = '1';
		        $success['message'] =  'password changed sucessfully';
		        return $this->sendResponse($success); //sending the new token
		    }
		    else 
		    {
		        return "Wrong password information";
		    }
		}
		return "Wrong password information";
	}

	public function otp(Request $request)
	{
		$user = User::where('email', '=', $request->email)->first();
		if(!empty($user) || $user !== null)
		{
			$new_pwd = str_random(10);
			$token_key = bcrypt($new_pwd);
			$update_password = array('password' => $token_key);
			$user_update = User::where('email',$request->email)->update($update_password);
			if($user_update == 1)
			{
				$user = User::where('email', '=', $request->email)->first();
				Mail::to($request->email)->send(new NewOtp($new_pwd,$user));
			}
			else
			{
				$success['status'] = '0';
	        	$success['message'] =  "Password doesn't updated";
	        	return $this->sendResponse($success);	
			}
		}
		else
		{
			$success['status'] = '1';
	        $success['message'] =  "Email doesn't exist";
	        return $this->sendResponse($success);
		}
		// dd();
	}
}