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
class RosterController extends ResponseController
{
    //team sends request to player
    public function send_request(Request $request)
    {
    	$validator = Validator::make($request->all(), [
            'team_id' => 'required',
            'player_id' => 'required',
        ]);
    	if($validator->fails()){
            return $this->sendError($validator->errors());       
        }
    	$team_id = $request->input('team_id');
    	$player_id = $request->input('player_id');
    	if($request->user()->id == $team_id)
    	{
    		$rosters = Rosters::create($request->all());
    		$success['status'] = "1";
    		$success['message'] = "Request send to player";
            return $this->sendResponse($success);
    	}
    	else
    	{
    		$success['message'] = "Unauthorized User";
            return $this->sendResponse($success);
    	}
    }

    // player will have listings of teams sending requests
    public function roster_requests(Request $request)
    {
    	$validator = Validator::make($request->all(), [
            'player_id' => 'required',
        ]);
    	if($validator->fails()){
            return $this->sendError($validator->errors());       
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
        	$success['message'] = "Unauthorized User";
            return $this->sendResponse($success);
        }

    }

    // player can accept or reject team request
    public function action_request(Request $request)
    {
    	$validator = Validator::make($request->all(),[
    		'player_id' => 'required',
    		'roster_id' => 'required',
    	]);
    	if($validator->fails())
    	{
    		return $this->sendError($validator->error());
    	}
    	$player_id = $request->input('player_id');
    	$action = $request->input('action');
    	// dd($action);
    	if($request->user()->id == $player_id )
    	{
    		if($action == 'accept')
    		{
    			$res = Rosters::where('id', $request->roster_id)->where('player_id', $request->player_id)->update(array('request'=> 1));	
    			$success['status'] = "1";
    			$success['message'] = "Request accepted";
    			return $this->sendResponse($success);
    		}
    		else if ($action == 'reject') 
    		{

    			$res = Rosters::where('id', $request->roster_id)->where('player_id', $request->player_id)->update(array('request'=> 2));	
    			$success['status'] = "1";
    			$success['message'] = "Request Rejected";
    			return $this->sendResponse($success);
    		}
    		else
    		{
    			$success['status'] = "0";
    			$success['message'] = "Request not updated";
    		}
    	}
    	else
    	{
    		$success['message'] = "Unauthorized User";
            return $this->sendResponse($success);
    	}
    }

    // listing of accepted request players in rosters
    public function roster_listing(Request $request)
    {
    	$validator = Validator::make($request->all(),[
    		'team_id' => 'required'
    	]);
    	if($validator->fails())
    	{
    		return $this->sendError($validator->error());
    	}
    	$team_id = $request->input('team_id');
    	if($request->user()->id == $team_id )
    	{
    		$players = Rosters::with('user')->where('team_id',$team_id)->where('request',1)->get();
    		$players_data = array();
    		foreach ($players as $key => $value) 
    		{
    			$players_data[$key] = User::join('profiles','users.id','profiles.user_id')->select('users.id as user_id','profiles.id as player_profile_id',DB::raw('CONCAT('."first_name".'," ",'."last_name".') AS display_name'),'image')->where('users.id',$value['player_id'])->first();
    			$players_data[$key]['image'] = URL::to('/').'/images/profile_images/'.$players_data[$key]['image']; 
    		}
    		$success['status'] = "1";
    		$success['message'] = "Request Rejected";
    		$success['data'] = $players_data;
    		return $this->sendResponse($success);
    	}
    	else
    	{
    		$success['message'] = "Unauthorized User";
            return $this->sendResponse($success);
    	}
    }

    // delete player from team
    public function delete_player(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'team_id' => 'required',
            'player_id' => 'required'
        ]);
        if($validator->fails())
        {
            return $this->sendError($validator->error());
        }

        if($request->user()->id == $request->team_id )
        {
            $res = DB::table('rosters')->where('team_id', $request->team_id)->where('player_id', $request->player_id)->delete();
            if($res == 1)
            {
                $success['status'] = "1";
                $success['message'] = "sucessfully deleted";
                return $this->sendResponse($success);
            }
            else
            {
                $success['status'] = "1";
                $success['message'] = "not exist";
                return $this->sendResponse($success);
            }
        }
        else
        {
            $success['message'] = "Unauthorized User";
            return $this->sendResponse($success);
        }
    }
}