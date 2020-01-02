<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Controllers\API\ResponseController as ResponseController;
use App\User;
use Validator;
use App\Rosters;
use DB;
use URL;
use App\Notification;
class RosterController extends ResponseController
{
    //team sends request to player
    public function send_request(Request $request)
    {
    	if($request->team_id == "" || empty($request->team_id))
        {
            $success['status'] = '0';
            $success['message'] = "team_id is missing";
            return $this->sendResponse($success);
        }
        if($request->player_id == "" || empty($request->player_id))
        {
            $success['status'] = '0';
            $success['message'] = "player_id is missing";
            return $this->sendResponse($success);
        }
        $team_id = $request->input('team_id');
        $player_id = $request->input('player_id');
        $check_roster = Rosters::where('team_id',$team_id)->where('player_id',$player_id)->first();
        if($request->user()->id == $team_id)
        {
            if($check_roster == null || $check_roster->request == '2')
            {
                $rosters = Rosters::create($request->all());
                if($rosters->id)
                {
                    $notify = array(
                        'roster_id'=>$rosters->id,
                        'to'=>$player_id,
                        'from'=>$team_id,
                        'type'=>env('NOTIFICATION_TYPE_SEND_ROSTER_REQUEST'),
                        'title'=>'Rosters',
                        'message'=>'Add to Rosters Request',
                    );
                    $res_notify = Notification::create($notify);
    
                    $token[] = $request->user()->device_token;
                    $data = array(
                        'title' => $notify['title'],
                        'message' => $notify['message'],
                        'notification_type' => env('NOTIFICATION_TYPE_SEND_ROSTER_REQUEST')
                    );
                    $data['device_tokens'] = $token;
                    $data['device_type'] = $request->user()->device_type;
                    push_notification($data);
                    $success['status'] = "1";
                    $success['message'] = "Request send to player";
                }
                
                return $this->sendResponse($success);
            }
            elseif($check_roster->request == '0')
            {
                $success['status'] = "1";
                $success['message'] = "Request is pending";
                return $this->sendResponse($success);
            }
            elseif($check_roster->request == '1')
            {
                $success['status'] = "1";
                $success['message'] = "Request is already accepted";
                return $this->sendResponse($success);
            }
        }
        else
        {
            $success['status'] = "0";
            $success['message'] = "Unauthorized User";
            $success['data'] = '';
            return $this->sendResponse($success);
        } 
    }

    // player will have listings of teams sending requests
    public function roster_requests(Request $request)
    {
    	if($request->player_id == "" || empty($request->player_id))
        {
            $success['status'] = '0';
            $success['message'] = "player_id is missing";
            return $this->sendResponse($success);
        }
        $player_id = $request->player_id;
        if($request->user()->id == $player_id)
        {
        	$teams_data = array();
        	$rosters = Rosters::with('user')->get();
        	foreach ($rosters as $key => $value) 
        	{
        		$team_data[$key] = User::where('id',$value['team_id'])->first();
        		$team_data[$key]['request'] = $value['request'];
        		$team_data[$key]['roster_id'] = $value['id'];
        	}
        	// $team_data;
        	$success['status'] = "1";
    		$success['message'] = "Request send to player";
    		$success['data'] = $team_data;
            return $this->sendResponse($success);
        	// dd($rosters);
        }
        else
        {
            $success['status'] = "0";
        	$success['message'] = "Unauthorized User";
            $success['data'] = '';
            return $this->sendResponse($success);
        }

    }

    // player can accept or reject team request
    public function action_request(Request $request)
    {
        if($request->roster_id == "" || empty($request->roster_id))
        {
            $success['status'] = '0';
            $success['message'] = "roster_id is missing";
            return $this->sendResponse($success);
        }
        if($request->player_id == "" || empty($request->player_id))
        {
            $success['status'] = '0';
            $success['message'] = "player_id is missing";
            return $this->sendResponse($success);
        }
    	$player_id = $request->input('player_id');
    	$action = $request->input('action');
        $get_roster = Rosters::where('id', $request->roster_id)->where('player_id', $request->player_id)->first();
        if($get_roster == null || empty($get_roster))
        {
            $success['status'] = "0";
            $success['message'] = "No request send to Player";
            return $this->sendResponse($success);
        }
        else
        {
            $team_id = $get_roster->team_id;
            if($request->user()->id == $player_id )
            {
                if($action == 'accept')
                {
                    $res = Rosters::where('id', $request->roster_id)->where('player_id', $request->player_id)->update(array('request'=> 1));
                    if($res == 1)
                    {
                        $notify = array(
                        'roster_id'=>$request->roster_id,
                        'to'=>$team_id,
                        'from'=>$player_id,
                        'type'=>env('NOTIFICATION_TYPE_ACCEPT_REQUEST'),
                        'title'=>'Rosters Accept',
                        'message'=>'Player Added to Rosters',
                        );
                        $res_notify = Notification::create($notify);
    
                        $token[] = $request->user()->device_token;
                        $data = array(
                            'title' => $notify['title'],
                            'message' => $notify['message'],
                            'notification_type' => env('NOTIFICATION_TYPE_ACCEPT_REQUEST')
                        );
                        $data['device_tokens'] = $token;
                        $data['device_type'] = $request->user()->device_type;
                        push_notification($data);
                        $success['status'] = "1";
                        $success['message'] = "Request accepted";
                        return $this->sendResponse($success);
                    }
                }
                elseif($action == 'reject') 
                {
                    $res = Rosters::where('id', $request->roster_id)->where('player_id', $request->player_id)->update(array('request'=> 0));
                    if($res == 1)
                    {
                        $notify = array(
                        'roster_id'=>$request->roster_id,
                        'to'=>$team_id,
                        'from'=>$player_id,
                        'type'=>env('NOTIFICATION_TYPE_REJECT_REQUEST'),
                        'title'=>'Rosters Rejected',
                        'message'=>'Player Rejected Roster Request',
                        );
                        $res_notify = Notification::create($notify);
    
                        $token[] = $request->user()->device_token;
                        $data = array(
                            'title' => $notify['title'],
                            'message' => $notify['message'],
                            'notification_type' => env('NOTIFICATION_TYPE_REJECT_REQUEST')
                        );
                        $data['device_tokens'] = $token;
                        $data['device_type'] = $request->user()->device_type;
                        push_notification($data);
                        $success['status'] = "1";
                        $success['message'] = "Request Rejected";
                        return $this->sendResponse($success);
                    }   
                }
                else
                {
                    $success['status'] = "0";
                    $success['message'] = "Request not updated";
                    $success['data'] = '';
                    return $this->sendResponse($success);
                }
            }
            else
            {
                $success['status'] = "0";
                $success['message'] = "Unauthorized User";
                $success['data'] = '';
                return $this->sendResponse($success);
            }
        }
    }

    // listing of accepted request players in rosters
    public function roster_listing(Request $request)
    {
    	if($request->team_id == "" || empty($request->team_id))
        {
            $success['status'] = '0';
            $success['message'] = "team_id is missing";
            return $this->sendResponse($success);
        }
    	$team_id = $request->input('team_id');
    	if($request->user()->id == $team_id )
    	{
    		$players = Rosters::with('user')->where('team_id',$team_id)->where('request',1)->get();
    		$players_data = array();
    		foreach ($players as $key => $value) 
    		{
    			$players_data[$key] = User::join('profiles','users.id','profiles.user_id')->select('users.id as player_id','profiles.id as player_profile_id',DB::raw('CONCAT('."first_name".'," ",'."last_name".') AS display_name'),'image')->where('users.id',$value['player_id'])->first();
    			$players_data[$key]['image'] = URL::to('/').'/public/images/profile_images/'.$players_data[$key]['image']; 
    		}
    		$success['status'] = "1";
    		$success['message'] = "Players in the team";
    		$success['data'] = $players_data;
    		return $this->sendResponse($success);
    	}
    	else
    	{
            $success['status'] = "0";
    		$success['message'] = "Unauthorized User";
            $success['data'] = '';
            return $this->sendResponse($success);
    	}
    }

    // delete player from team
    public function delete_player(Request $request)
    {
        if($request->team_id == "" || empty($request->team_id))
        {
            $success['status'] = '0';
            $success['message'] = "team_id is missing";
            return $this->sendResponse($success);
        }
        if($request->player_id == "" || empty($request->player_id))
        {
            $success['status'] = '0';
            $success['message'] = "player_id is missing";
            return $this->sendResponse($success);
        }
        elseif($request->user()->id == $request->team_id )
        {
            $res = DB::table('rosters')->where('team_id', $request->team_id)->where('player_id', $request->player_id)->delete();
            if($res == 1)
            {
                $success['status'] = "1";
                $success['message'] = "sucessfully deleted";
                $success['data'] = '';
                return $this->sendResponse($success);
            }
            else
            {
                $success['status'] = "1";
                $success['message'] = "not exist";
                $success['data'] = '';
                return $this->sendResponse($success);
            }
        }
        else
        {
            $success['status'] = "0";
            $success['message'] = "Unauthorized User";
            $success['data'] = '';
            return $this->sendResponse($success);
        }
    }

    public function get_notification_list(Request $request)
    {
        if($request->user_id == "" || empty($request->user_id))
        {
            $success['status'] = '0';
            $success['message'] = "user_id is missing";
            return $this->sendResponse($success);
        }
        elseif($request->user()->id == $request->user_id )
        {
            $res = Notification::select('roster_id','type','title','message','is_read','news_id','to as receiver_id','from as sender_id','image')->join('profiles','profiles.user_id','=','notifications.from')->where('to', $request->user_id)->get();
            // $players_image = array();
            foreach ($res as $key => $value) 
            {
                $res[$key]['image'] = URL::to('/').'/public/images/profile_images/'.$value['image']; 
            }
            if(count($res) > 0 )
            {
                $success['status'] = "1";
                $success['message'] = "Notification list";
                $success['data'] = $res;
                return $this->sendResponse($success);
            }
            else
            {
                $success['status'] = "1";
                $success['message'] = "not exist";
                return $this->sendResponse($success);
            }
        }

    }
}