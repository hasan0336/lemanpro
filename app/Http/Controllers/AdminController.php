<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\Subscription;
use App\Mail\ForgotPassword;
use Illuminate\Auth\Events\Registered;
use Auth;
use Mail;
use DB;
use Session;
use App\User;
use Crypt;
use Hash;
use Intervention\Image\ImageManagerStatic as Image;
use Validator;
use App\Rosters;
use App\Profile;
use App\News;
use App\Tryout;
use App\Notification;
use App\TryoutPlayers;
// use Hash;
// use Crypt;
class AdminController extends Controller
{
    public function login_admin()
    {	
    	if(Auth::user() != null)
    	{
    		return redirect()->back();
    	}
    	else
    	{
    		return view('admin.login');
    	}
    }

    public function index()
    {
    	if(Auth::user() != null)
    	{
    		if(Auth::user()->role_id = '3')
    		{
    			$admin_profile = DB::table('profiles')->where('user_id',Auth::user()->id)->first();
    			// dd($admin_profile);
    			Session::put('admin_profile', $admin_profile);
	    		$current_year_users = DB::table('users')
	                ->whereRaw('year(`created_at`) = ? ', array(date('Y') ));
	            $abc = $current_year_users;
	            $count_user_by_month = $current_year_users
	                ->select(DB::raw('DATE_FORMAT(created_at, "%m") as month'), DB::raw('count(*) as month_count'))
	                ->groupBy('month')->get()->toArray();
	            $month_arr = array();
	            foreach($count_user_by_month as $k => $v)
	                $month_arr[$v->month] = $v->month_count;
	            $months = array(
	                '01' => 'jan',
	                '02' => 'feb',
	                '03' => 'mar',
	                '04' => 'apr',
	                '05' => 'may',
	                '06' => 'jun',
	                '07' => 'jul',
	                '08' => 'aug',
	                '09' => 'sep',
	                '10' => 'oct',
	                '11' => 'nov',
	                '12' => 'dec',
	            );
	            $result_arr = array();
	            foreach($months as $key => $month)
	            {
	                $month_count = in_array($key,array_keys($month_arr)) ? $month_arr[$key] : 0;
	                $result_arr[] = $month_count;
	            }
	            $user_type = DB::table('users')->select(DB::raw('role_id'), DB::raw('count(*) as role_id'))->groupBy('role_id')->get()->toArray();
	            $usertype_arr = array();
	            foreach($user_type as $key => $value)
	            {
	                $user_type_count = $value->role_id;
	                $usertype_arr[] = $user_type_count;
	            }
	            $signup_type = DB::table('users')->select(DB::raw('login_type'), DB::raw('count(*) as login_type'))->groupBy('login_type')->get()->toArray();
	            $signup_type_arr = array();
	            foreach($signup_type as $key => $value)
	            {
	                $signup_type_type_count = $value->login_type;
	                $signup_type_arr[] = $signup_type_type_count;
	            }
	            $device_type = DB::table('users')->select(DB::raw('device_type'), DB::raw('count(*) as device_type'))->groupBy('device_type')->get()->toArray();
	            $device_type_arr = array();
	            foreach($device_type as $key => $value)
	            {
	                $device_type_type_count = $value->device_type;
	                $device_type_arr[] = $device_type_type_count;
	            }
	            return view ('admin.index',compact('result_arr','usertype_arr','signup_type_arr','device_type_arr'));
    		}
    		else
	        {
	            return redirect()->route('login_admin');
	        }
    	}
    	else
        {
            return redirect()->route('login_admin');
        }
    }

    public function update_admin(Request $request)
    {
        $user_array = array();
        $user_pass_array = array();
        // $value = Session::get('admin_profile');
        $first_name = $request->input('first_name');
        $last_name = $request->input('last_name');
        $email = $request->input('email');
        $old_password = $request->input('old_password');
        $new_password = $request->input('new_password');
        $confirm_password = $request->input('confirm_password');
        $update_admin = DB::table('profiles')->where('user_id',Auth::user()->id)->first();
        if($first_name != false || $first_name != null)
        {
            $user_array['first_name'] = $first_name;
        }
        if($last_name != false || $last_name != null)
        {
            $user_array['last_name'] = $last_name;
        }
        if($old_password != false || $old_password != null)
        {

            if(Hash::check($old_password, Auth::user()->password))
            {
                if($new_password != false || $new_password != null && $confirm_password != false || $confirm_password != null )
                {
                    $user_pass_array['password'] = Hash::make($new_password);
                }
                else
                {
                    Session::flash('error_match_password', 'New password and old password do not match');
                    return redirect()->route('dashboard');
                }
            }
            else
            {
            	// dump($old_password);
            	// $encrypted = Auth::user()->password;
            	// $val = Crypt::decrypt($encrypted);
            	// dump($val);
            	// die();
                Session::flash('error_message', 'Credentials does not match');
                return redirect()->route('dashboard');
            }
        }
        if($request->file('profile_picture'))
        {
               // dd($user_array);
            $avatar = $request->file('profile_picture');
            $filename = time() . '.' . $avatar->getClientOriginalExtension();
            $user_array['image'] = $filename;
            $upload_path = public_path('images/profile_images');
            $attach = Image::make($avatar)->resize(390, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $attach->save($upload_path . '/' . $filename);
        }
        $update = DB::table('profiles')->where('user_id',Auth::user()->id)->update($user_array);
        $update = DB::table('users')->where('id',Auth::user()->id)->update($user_pass_array);
        // $update_admin = DB::table('admin')->where('email',$request->input('email'))->first();
        $admin_profile = DB::table('profiles')->where('user_id',Auth::user()->id)->first();
        Session::put('admin_profile', $admin_profile);
        return redirect()->route('dashboard');
    }

    public function palyer_management(Request $request)
    {
        $info['get_players'] = User::select('profiles.id','users.email','users.is_blocked','users.is_featured','users.verify_status','profiles.user_id','profiles.first_name','profiles.last_name','profiles.image','profiles.gender','profiles.position','profiles.is_profile_complete')->join('profiles','users.id','=','profiles.user_id')->where('users.role_id','2')->get();
        foreach ($info['get_players'] as $key => $value) {
            // dd($value->user_id);
            $team_info = Rosters::join('profiles','rosters.team_id','=','profiles.user_id')->where('rosters.player_id',$value->user_id)->first();
            // dd($info[$key]['get_players']);
            $info['get_players'][$key]['player_team_name'] = $team_info['team_name'];
        }
        // dd($info['get_players']);
    	return view('admin.players_management')->with($info);;
    }

    public function team_management(Request $request)
    {
        $info['get_teams'] = User::select('profiles.id','users.email','users.is_blocked','users.is_featured','users.verify_status','profiles.user_id','profiles.team_name','profiles.city','profiles.pitch_type','profiles.capacity','profiles.coach_name','profiles.is_profile_complete')->join('profiles','users.id','=','profiles.user_id')->where('users.role_id','1')->get();
        foreach ($info['get_teams'] as $key => $value) {
            // dd($value->user_id);
            $team_info = Rosters::where('rosters.team_id',$value->user_id)->get();
            // dd(count($team_info));
            $info['get_teams'][$key]['no_of_players'] = count($team_info);
        }
        // dd($info['get_teams']);
        return view('admin.teams_management')->with($info);;
    }
    public function tryout_management(Request $request)
    {
        $info['get_tryouts'] = Tryout::select('tryouts.id','tryouts.team_id','tryouts.timeoftryout','tryouts.dateoftryout','tryouts.costoftryout')->get();
        $leman_pro_fees = DB::table('lemanpro_fees')->first();
        $tryout_info['lemanpro_fees'] = $leman_pro_fees->lemanpro_fee;
        foreach ($info['get_tryouts'] as $key => $value) {
            // dd($value->team_id);
            $tryout_info = TryoutPlayers::where('tryout_players.tryout_id',$value->id)->get();
            // dd(count($tryout_info));
            $info['get_tryouts'][$key]['participants'] = count($tryout_info);
            $info['get_tryouts'][$key]['collections'] =(count($tryout_info)*$value->costoftryout)+$leman_pro_fees->lemanpro_fee;
        }
        return view('admin.tryouts_management')->with($info);;
    }

    public function feature(Request $request)
    {
        // dd($request->input('user_id'));
        $result = User::where('id',$request->input('user_id'))->update(array('is_featured'=>$request->input('check')));
       // dd($result);
        return response($result);
    }

    public function block_team(Request $request)
    {
        $result = DB::table('users')->where('id',$request->input('user_id'))->update(array('is_blocked'=>$request->input('check')));
        // dd($result);
        return response($result);
    }


    public function Admin_news(Request $request)
    {
        return view('admin.news_pannel'); 
    }

    public function create_news(Request $request)
    {
        $input = $request->all();
        // dd($input);
        $data = array('team_id'=> Auth::user()->id, 'title' => $input['title'], 'description'=> $input['description'],'is_admin'=>1);
        $images=array();
        if($request->file('news_image') != '' || !empty($request->file('news_image')))
        {   
            $allowedfileExtension = ['jpeg','jpg','png','gif','svg'];
            $files = $request->file('news_image');
            $news = DB::table('news')->insertGetId($data);
            foreach($files as $file)
            {
                $extension = $file->getClientOriginalExtension();
                $check=in_array($extension,$allowedfileExtension);
                if($check)
                {
                    
                    $name=str_random(5)."-".date('his')."-".str_random(3).".".$file->getClientOriginalExtension();
                    $file->move('public/news_image',$name);
                    $images[]=$name;
                    /*Insert your data*/
                    $news_image = DB::table('news_images')->insert([
                                    'news_image' => $name,
                                    'news_id' => $news,
                                    ]);
                }
            /*Insert your data*/
                else
                {
                    News::where('id',$news)->delete();
                    $success['status'] = "1";
                    $success['message'] = "Sorry Only Upload png, jpg, gif, jpeg, svg";
                    $success['data'] = '';
                    return $this->sendResponse($success);
                }
            }
            $get_players = User::where('id', '!=', auth()->id())->get();
            foreach ($get_players as $key => $player) 
            {
                $notify = array(
                'news_id'=>$news,
                'to'=>$player['player_id'],
                'from'=>$request->team_id,
                'type'=>env('NOTIFICATION_TYPE_SEND_NEWS_ALERT_ADMIN_REQUEST'),
                'title'=>'News',
                'message'=>'News from Team',
            );
                // dd($notify);
            $res_notify = Notification::create($notify);

            $token[] = $request->user()->device_token;
            $data = array(
                'title' => $notify['title'],
                'message' => $notify['message'],
                'notification_type' => env('NOTIFICATION_TYPE_SEND_NEWS_ALERT_ADMIN_REQUEST')
            );
            $data['device_tokens'] = $token;
            $data['device_type'] = $request->user()->device_type;
            push_notification($data);
            }
            $success['status'] = "1";
            $success['message'] = "News Posted";
            // $success['data'] = $get_players;
            return $this->sendResponse($success);
        }
        else
        {
            $news = DB::table('news')->insertGetId($data);
            $get_players = User::where('id', '!=', auth()->id())->get();
            foreach ($get_players as $key => $player) 
            {
                $notify = array(
                'news_id'=>$news,
                'to'=>$player['id'],
                'from'=>auth()->id(),
                'type'=>env('NOTIFICATION_TYPE_SEND_NEWS_ALERT_ADMIN_REQUEST'),
                'title'=>'Admin News',
                'message'=>'News from Admin',
            );
                // dd($notify);
            $res_notify = Notification::create($notify);

            $token[] = $request->user()->device_token;
            $data = array(
                'title' => $notify['title'],
                'message' => $notify['message'],
                'notification_type' => env('NOTIFICATION_TYPE_SEND_NEWS_ALERT_ADMIN_REQUEST')
            );
            $data['device_tokens'] = $token;
            $data['device_type'] = $request->user()->device_type;
            push_notification($data);
            }
            return redirect()->back();
        }
    }


    public function cm_term()
    {
        if(Auth::user()->role_id == 3)
        {
            $results['results'] = DB::table('contents')->where('content_type','tc')->first(); 
            // dd($results['results']->description);
            if($results['results']->description != null || !empty($results['results']->description))
            {
                return view('admin.cm_term',$results);  
            }
        }
    }
    public function update_term(Request $request)
    {
        $update = DB::table('contents')->where('content_type','tc')->update(array('description'=>$request->input('editor')));
        return redirect()->back();
    }
     public function cm_privacy()
    {
        if(Auth::user()->role_id == 3)
        {
            $results['results'] = DB::table('contents')->where('content_type','pp')->first(); 
            if($results['results']->description != null || !empty($results['results']->description))
            {
                return view('admin.cm_privacy',$results);  
            }
        }
    }
    public function update_privacy(Request $request)
    {
        $update = DB::table('contents')->where('content_type','pp')->update(array('description'=>$request->input('editor')));
        return redirect()->back();
    }
}
