<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\API\ResponseController as ResponseController;
use App\Game;
use Validator;
use App\Profile;
use File;
use App\Match;
use URL;
use DB;
use Carbon\Carbon;
use App\Mail\PlayerReport;
use App\Mail\MatchReport;
use App\User;
use Mail;
class GameController extends ResponseController
{
    public function create_game(Request $request)
    {
    	$validator = Validator::make($request->all(), [
            'team_id' => 'required',
        ]);
        if($validator->fails())
        {
            return $this->sendError($validator->errors());       
        }
        if($request->user()->id == $request->team_id)
        {
        	$game = Game::create(array('team_id' => $request->team_id));
        	$match_players = explode(',',$request->players);
        	$matches = array();
        	$mytime = Carbon::now();
        	foreach ($match_players as $key => $value) 
        	{
        		$matches[] = array('game_id'=>$game->id,'player_id'=>$value,'created_at'=>$mytime->toDateTimeString(),'updated_at'=>$mytime->toDateTimeString());
        	}
        	$matches2 = MAtch::insert($matches);
        	if($matches2 == 1)
        	{
        		$success['status'] = "1";
                $success['message'] = "Game and Match has been created";
                $success['data'] = '';
                return $this->sendResponse($success);
        	}
        	else
        	{
        		$success['status'] = "1";
                $success['message'] = "Game and Match not created";
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

    public function delete_game(Request $request)
    {
    	$validator = Validator::make($request->all(), [
            'team_id' => 'required',
            'game_id' => 'required'
        ]);
        if($validator->fails())
        {
            return $this->sendError($validator->errors());       
        }
        if($request->user()->id == $request->team_id)
        {
        				DB::table("matches")->where("game_id", $request->game_id)->delete();
			$res 	= 	DB::table("games")->where("id", $request->game_id)->delete();
			if($res)
			{
				$success['status'] = "1";
                $success['message'] = "Game and Match deleted";
                $success['data'] = '';
                return $this->sendResponse($success);	
			}
			else
			{
				$success['status'] = "0";
                $success['message'] = "Game and Match not deleted";
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

    public function add_score_sheet(Request $request)
    {
    	$validator = Validator::make($request->all(), [
            'team_id' => 'required',
            'player_id' => 'required',
            'game_id'=>'required',
        ]);
        if($validator->fails())
        {
            return $this->sendError($validator->errors());       
        }
        if($request->user()->id == $request->team_id)
        {
        	$data = array();
        	if($request->goals != null || !empty($request->goals))
        	{
        		$data['goals'] = $request->goals;
        	}
        	if($request->red != null || !empty($request->red))
        	{
        		$data['red'] = $request->red;
        	}
        	if($request->yellow != null || !empty($request->yellow))
        	{
        		if($request->yellow == 2 || $request->yellow > 2)
        		{
        			$data['red'] = 1;
        			$data['yellow'] = 0;
        		}
        		else
        		{
        			$data['yellow'] = $request->yellow;
        		}
        	}
        	if($request->trophies != null || !empty($request->trophies))
        	{
        		$data['trophies'] = $request->trophies;
        	}
        	if($request->time != null || !empty($request->time))
        	{
        		$data['time'] = $request->time;
        	}
        	$match = Match::where('player_id',$request->player_id)->where('game_id',$request->game_id)->update($data);
        	if($match == 1)
        	{
        		$user = User::join('matches','users.id','matches.player_id')->where('matches.player_id',$request->player_id)->where('matches.game_id',$request->game_id)->first();
        		
        		Mail::to($user->email)->send(new PlayerReport($user));
        		$success['status'] = "1";
                $success['message'] = "player score inserted";
                $success['data'] = '';
                return $this->sendResponse($success);
        	}
        	else
        	{
        		$success['status'] = "1";
                $success['message'] = "player score not inserted";
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

    public function report_to_manager(Request $request)
    {
    	$validator = Validator::make($request->all(), [
            'team_id' => 'required',
            'game_id'=>'required',
        ]);
        if($validator->fails())
        {
            return $this->sendError($validator->errors());       
        }
        if($request->user()->id == $request->team_id)
        {
        	$game = Match::join('users','users.id','=','matches.player_id')->where('game_id',$request->game_id)->get();
        	$users = User::where('id',$request->team_id)->first();
        	
        	Mail::to($users->email)->send(new MatchReport($game));
        	$success['status'] = "1";
            $success['message'] = "Report sent to Manager";
            $success['data'] = $game;
            return $this->sendResponse($success);
        }
    }
}
