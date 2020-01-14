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
use URL;
use App\Notification;
// use Illuminate\Support\Facades\Mail;
class AuthController extends ResponseController
{
    //create user
    public function signup(Request $request)
    {
        if ($request->social_token == '' || empty($request->social_token)) 
        {
            if($request->email == "" || empty($request->email))
            {
                $success['status'] = '0';
                $success['message'] = "Emailis missing";
                return $this->sendResponse($success);
            }
            elseif($request->password == "" || empty($request->password))
            {
                $success['status'] = '0';
                $success['message'] = "Password is missing";
                return $this->sendResponse($success);   
            }
            elseif($request->confirm_password == "" || empty($request->confirm_password))
            {
                $success['status'] = '0';
                $success['message'] = "Confirm Password is missing";
                return $this->sendResponse($success);   
            }
            elseif($request->device_type == "" || empty($request->device_type))
            {
                $success['status'] = '0';
                $success['message'] = "Device type is missing";
                return $this->sendResponse($success);   
            }
            elseif($request->device_token == "" || empty($request->device_token))
            {
                $success['status'] = '0';
                $success['message'] = "Device token is missing";
                return $this->sendResponse($success);   
            }

            $check_email = User::where('email',$request->email)->first();
            if($check_email)
            {
                $success['status'] = '0';
                $success['message'] = "email already exist";
                return $this->sendResponse($success);
            }
            else
            {
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
                    $success['status'] = '1';
                    $success['data']['id'] = $user->id;
                    $success['message'] = "A verification email with instructions has been sent to your email address.";
                    return $this->sendResponse($success);
                }
                else
                {
                    $success['status'] = '0';
                    $success['data'] = '';
                    $success['message'] = "Registration is not successfull..";
                    return $this->sendResponse($success);
                }
            }
        }
        else
        {
            if($request->social_token != '' || !empty($request->social_token))
            {
                $check_social_token = User::where('social_token',$request->social_token)->first();
                
                if($check_social_token == null || empty($check_social_token))
                {
                    
                    $email          = $request->email;
                    $social_token   = $request->social_token;
                    $name           = $request->name;
                    $image          = $request->image;
                    $device_type    = $request->device_type;
                    $device_token   = $request->device_token;
                    $login_type     = $request->login_type;
                    $role_id        = $request->role_id;
                    $check_social_email = User::where('email',$email)->first();
                    if($check_social_email != null || !empty($check_social_email))
                    {
                        $success['status'] = '0';
                        // $success['data'] = $user->id;
                        $success['message'] = "Social email already exist";
                        return $this->sendResponse($success);
                    }
                    else
                    {
                        // $role_id        = $request->role_id;
                        // dd($social_token);
                        $user_data = array(
                            'social_token' => $social_token,
                            'verify_status' => 1,
                            'device_type' => $device_type,
                            'device_token' => $device_token,
                            'login_type' => $login_type,
                            'role_id' => $role_id
                        );

                        if($email != '' || !empty($email))
                        {
                            $user_data['email'] = $email;
                        }
                        if($name != '' || !empty($name))
                        {
                            $user_data['name'] = $name;
                        }
                        if($image != '' || !empty($image))
                        {
                            $user_data['image'] = $image;
                        }
                        // dd($user_data);
                        $user = User::create($user_data);
                        $profile_data = array(
                            'user_id' => $user->id,
                        );
                        if($name != '' || !empty($name))
                        {
                            $profile_data['first_name'] = $name;
                        }
                        if($image != '' || !empty($image))
                        {
                            $profile_data['image'] = $image;
                        }
                        $profile = Profile::create($profile_data);
                        $success['token'] =  $user->createToken('token')->accessToken;
                        $success['status'] = '1';
                        $success['data']['id'] = $user->id;
                        $success['message'] = "Social user created";
                        return $this->sendResponse($success);
                    }
                }
                else
                {
                    $user_info = User::with('profile')->where('social_token',$request->social_token)->first();
                    $data =array('device_token'=> $request->device_token, 'device_type' => $request->device_type);
                    $update_device_token = User::where('social_token', $request->social_token)->update($data);
                    if($user_info['profile']->image != "" || !empty($user_info['profile']->image))
                    {
                        // dd($user_info['profile']->image);
                        if (filter_var($user_info['profile']->image, FILTER_VALIDATE_URL))
                        { 
                            $user_info['profile']->image = $user_info['profile']->image;
                        }
                        else
                        {
                            $user_info['profile']->image = URL::to('public/images/profile_images/'.$user_info['profile']->image);
                        }
                    }
                    $success['status'] = '1';
                    $success['message'] = "Socail Login Sucessfully";
                    $success['data'] = $user_info;
                    $success['token'] =  $user_info->createToken('token')->accessToken;
                    return $this->sendResponse($success);
                }
            }
        }
    }
    
    //login
    public function login(Request $request)
    {
        if ($request->social_token == '' || empty($request->social_token)) 
        {
            if($request->email == "" || empty($request->email))
            {
                $success['status'] = '0';
                $success['message'] = "Email is missing";
                return $this->sendResponse($success);
            }
            elseif($request->password == "" || empty($request->password))
            {
                $success['status'] = '0';
                $success['message'] = "Password is missing";
                return $this->sendResponse($success);   
            }
            elseif($request->device_token == "" || empty($request->device_token))
            {
                $success['status'] = '0';
                $success['message'] = "Device token is missing";
                return $this->sendResponse($success);   
            }
            elseif($request->device_type == "" || empty($request->device_type))
            {
                $success['status'] = '0';
                $success['message'] = "Device type is missing";
                return $this->sendResponse($success);   
            }
            else
            {
                $check_email = User::where('email',$request->email)->first();
                if($check_email)
                {
                    $credentials = request(['email', 'password']);
                    // dd($credentials);
                    if(!Auth::attempt($credentials))
                    {
                        $success['status'] = '0';
                        $success['message'] = "incorrect email or password";
                        return $this->sendResponse($success);
                    }
                    $user = $request->user();
                    $user_info = User::with('profile')->where('id',$user->id)->first();
                    $data =array('device_token'=> $request->device_token, 'device_type' => $request->device_type);
                    $update_device_token = User::where('id', $user->id)->update($data);
                    $notification = Notification::where('to',$user->id)->where('is_read','0')->get();
                    $notifications_count = count($notification);
                    $user_info['notifications_count'] = $notifications_count;
                    if($user_info['profile']->image != "" || !empty($user_info['profile']->image))
                    {
                        $user_info['profile']->image = URL::to('public/images/profile_images/'.$user_info['profile']->image);
                    }
                    $success['status'] = '1';
                    $success['message'] = "Login Sucessfully";
                    $success['data'] = $user_info;
                    $success['token'] =  $user->createToken('token')->accessToken;
                    return $this->sendResponse($success);
                }
                else
                {
                    $success['status'] = '0';
                    $success['message'] = "email not exist";
                    return $this->sendResponse($success);
                }
            }
        }
        else
        {
            if($request->social_token != '' || !empty($request->social_token))
            {
                // dd($request->social_token);
                $check_social_token = User::where('social_token',$request->social_token)->first();
                // dd($check_social_token);
                if($check_social_token == null || empty($check_social_token))
                {
                    
                    $success['status'] = '1';
                    $success['data']['is_social'] = '0';
                    $success['message'] = "Social user not exist";
                    return $this->sendResponse($success);
                }
                else
                {
                    $user_info = User::with('profile')->where('social_token',$request->social_token)->first();
                    $data =array('device_token'=> $request->device_token, 'device_type' => $request->device_type);
                    $update_device_token = User::where('social_token', $request->social_token)->update($data);
                    if($user_info['profile']->image != "" || !empty($user_info['profile']->image))
                    {
                        if (filter_var($user_info['profile']->image, FILTER_VALIDATE_URL))
                        { 
                            $user_info['profile']->image = $user_info['profile']->image;
                        }
                        else
                        {
                            $user_info['profile']->image = URL::to('public/images/profile_images/'.$user_info['profile']->image);
                        }
                    }
                    $success['status'] = '1';
                    $success['message'] = "Socail Login Sucessfully";
                    $success['data'] = $user_info;
                    $success['token'] =  $user_info->createToken('token')->accessToken;
                    return $this->sendResponse($success);
                }
            }
        }
    }

    //logout
    public function logout(Request $request)
    {
        // dd($request->id);
        $data =array('device_token'=> '', 'device_type' => '');
        $update_device_token = User::where('id', $request->user_id)->update($data);
        // dd($update_device_token);
        $isUser = $request->user()->token()->revoke();
        if($isUser){
            $success['status'] = '1';
            $success['message'] = "Successfully logged out.";
            return $this->sendResponse($success);
        }
        else
        {
            $success['status'] = '0';
            $success['message'] = "Something went wrong.";
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
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'oldPassword' => 'required',
            'newPassword' => 'required',
        ]);

        if($validator->fails())
        {
            $success['status'] = '0';
            $success['message'] = 'value is missing';
            return $this->sendResponse($success);     
        }
        if($request->user()->id == $request->user_id)
        {
            $user = Auth::guard('api')->user();
            // dd($user);
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
                    $user->otp = '0';

                    //Changing the type
                    $a = $user->save();
                    $user = $request->user();
                    $user_info = User::with('profile')->where('id',$user->id)->first();
                    $data =array('device_token'=> $user->device_token, 'device_type' => $user->device_type);
                    
                    if($user_info['profile']->image != "" || !empty($user_info['profile']->image))
                    {
                        $user_info['profile']->image = URL::to('public/images/profile_images/'.$user_info['profile']->image);
                    }
                    $success['status'] = '1';
                    $success['message'] = "'password changed sucessfully";
                    $success['data'] = $user_info;
                    return $this->sendResponse($success);
                }
                else 
                {
                    
                    $success['status'] = '0';
                    $success['message'] =  'Old Password not matched';
                    
                    return $this->sendResponse($success); 
                }
            }
            $success['status'] = '0';
            $success['message'] =  'Wrong password information';
            return $this->sendResponse($success); 
        }
        else
        {
            $success['status'] = "0";
            $success['message'] = "Unauthorized User";
            return $this->sendResponse($success);
        }
	}

	public function otp(Request $request)
	{
		$user = User::where('email', '=', $request->email)->first();
		if(!empty($user) || $user !== null)
		{
            //Sample@123
            $len = 9;
            $sets = array();
            $sets[] = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
            $sets[] = 'abcdefghjkmnpqrstuvwxyz';
            $sets[] = '23456789';
            $sets[]  = '~!@#$%^&*(){}[],./?';
            $password = '';
            //append a character from each set - gets first 4 characters
            foreach ($sets as $set) {
                $password .= $set[array_rand(str_split($set))];
            }
            //use all characters to fill up to $len
            while(strlen($password) < $len) {
                //get a random set
                $randomSet = $sets[array_rand($sets)];
                
                //add a random char from the random set
                $password .= $randomSet[array_rand(str_split($randomSet))]; 
            }
            //shuffle the password string before returning!
            $new_pwd = str_shuffle($password);
			$token_key = bcrypt($new_pwd);
			$update_password = array('password' => $token_key,'otp' => 1);
			$user_update = User::where('email',$request->email)->update($update_password);
			
            if($user_update == 1)
			{
				$user = User::where('email', '=', $request->email)->first();
				Mail::to($request->email)->send(new NewOtp($new_pwd,$user));
                
                $success['status'] = '1';
                $success['message'] =  "One time password is sent to your email";
                return $this->sendResponse($success);
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
			$success['status'] = '0';
	        $success['message'] =  "Email doesn't exist";
	        return $this->sendResponse($success);
		}
		// dd();
	}
}