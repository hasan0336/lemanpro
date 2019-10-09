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
use App\Profile;
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
        if($user){
        	$data = array('user_id' => $user->id);
        	$pro = Profile::create($data);

        	Mail::to($request->email)->send(new UserNotification($user));
            $success['token'] =  $user->createToken('token')->accessToken;
            $success['message'] = "Registration successfull..";
            return $this->sendResponse($success);
        }
        else{
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
        $success['data'] = $user;
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
}