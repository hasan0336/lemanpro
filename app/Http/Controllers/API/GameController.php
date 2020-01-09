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
use App\Notification;
class GameController extends ResponseController
{
    public function create_game(Request $request)
    {
    	if($request->team_id == "" || empty($request->team_id))
        {
            $success['status'] = '0';
            $success['message'] = "team id is missing";
            return $this->sendResponse($success);
        }
        elseif($request->user()->id == $request->team_id)
        {
            // dd($request->teams);
        	$game = Game::create(array('team_id' => $request->team_id));
            
        	$match_players = explode(',',$request->players);
            $players_team = explode(',',$request->team_assign);
        	$matches = array();
        	$mytime = Carbon::now();
            // dd($match_players);
            //multiple variable foreach loop

        	foreach(array_combine($match_players, $players_team) as $match_player => $player_team)
        	{
                // dd($match_player);
                // dd($player_team);
        		$matches[] = array('game_id'=>$game->id,'player_id'=>$match_player,'team_assign'=>$player_team,'created_at'=>$mytime->toDateTimeString(),'updated_at'=>$mytime->toDateTimeString());
        	}
        	$matches2 = MAtch::insert($matches);

        	if($matches2 == 1)
        	{
                foreach ($match_players as $key => $value) {
                    // dd($value);
                    $notify = array(
                    'game_id'=>$game->id,
                    'to'=>$value,
                    'from'=>$request->team_id,
                    'type'=>env('NOTIFICATION_TYPE_SEND_PLAYER_SELECTED_REQUEST'),
                    'title'=>'Game Created',
                    'message'=>'You are selected for this game',
                    );
                        // dd($notify);
                    $res_notify = Notification::create($notify);

                    $token[] = $request->user()->device_token;
                    $data = array(
                        'title' => $notify['title'],
                        'message' => $notify['message'],
                        'notification_type' => env('NOTIFICATION_TYPE_SEND_PLAYER_SELECTED_REQUEST')
                    );
                    $data['device_tokens'] = $token;
                    $data['device_type'] = $request->user()->device_type;
                    push_notification($data);
                }
                $success['status'] = "1";
                $success['message'] = "Game and Match has been created";
                $success['data'] = array('game_id'=>$game->id);
                return $this->sendResponse($success);
        	}
        	else
        	{
        		$success['status'] = "1";
                $success['message'] = "Game and Match not created";
                return $this->sendResponse($success);	
        	}
        }
        else
        {
            $success['status'] = "0";
        	$success['message'] = "Unauthorized User";
            return $this->sendResponse($success);
        }
    }

    public function delete_game(Request $request)
    {
        if($request->team_id == "" || empty($request->team_id))
        {
            $success['status'] = '0';
            $success['message'] = "team id is missing";
            return $this->sendResponse($success);
        }
        if($request->game_id == "" || empty($request->game_id))
        {
            $success['status'] = '0';
            $success['message'] = "game id is missing";
            return $this->sendResponse($success);
        }
        if($request->user()->id == $request->team_id)
        {
        				DB::table("matches")->where("game_id", $request->game_id)->delete();
			$res 	= 	DB::table("games")->where("id", $request->game_id)->delete();
			if($res)
			{
				$success['status'] = "1";
                $success['message'] = "Game and Match deleted";

                return $this->sendResponse($success);	
			}
			else
			{
				$success['status'] = "0";
                $success['message'] = "Game and Match not deleted";

                return $this->sendResponse($success);	
			}
        }
        else
        {
            $success['status'] = "0";
        	$success['message'] = "Unauthorized User";
            return $this->sendResponse($success);
        }
    }

    public function add_score_sheet(Request $request)
    {
        if($request->team_id == "" || empty($request->team_id))
        {
            $success['status'] = '0';
            $success['message'] = "team id is missing";
            return $this->sendResponse($success);
        }
        if($request->game_id == "" || empty($request->game_id))
        {
            $success['status'] = '0';
            $success['message'] = "game id is missing";
            return $this->sendResponse($success);
        }
        if($request->player_id == "" || empty($request->player_id))
        {
            $success['status'] = '0';
            $success['message'] = "player id is missing";
            return $this->sendResponse($success);
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

                return $this->sendResponse($success);
        	}
        	else
        	{
        		$success['status'] = "1";
                $success['message'] = "player score not inserted";

                return $this->sendResponse($success);
        	}
        }
        else
        {
            $success['status'] = "0";
        	$success['message'] = "Unauthorized User";
            return $this->sendResponse($success);
        }
    }

    public function report_to_manager(Request $request)
    {
        if($request->team_id == "" || empty($request->team_id))
        {
            $success['status'] = '0';
            $success['message'] = "team id is missing";
            return $this->sendResponse($success);
        }
        if($request->game_id == "" || empty($request->game_id))
        {
            $success['status'] = '0';
            $success['message'] = "game id is missing";
            return $this->sendResponse($success);
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
        else
        {
            $success['status'] = "0";
            $success['message'] = "Unauthorized User";
            return $this->sendResponse($success);
        }
    }

    public function players_team_list(Request $request)
    {
        if($request->team_id == "" || empty($request->team_id))
        {
            $success['status'] = '0';
            $success['message'] = "team id is missing";
            return $this->sendResponse($success);
        }
        if($request->team_assign == "" || empty($request->team_assign))
        {
            $success['status'] = '0';
            $success['message'] = "team_assign is missing";
            return $this->sendResponse($success);
        }
        if($request->game_id == "" || empty($request->game_id))
        {
            $success['status'] = '0';
            $success['message'] = "game id is missing";
            return $this->sendResponse($success);
        }
        if($request->user()->id == $request->team_id)
        {
            if($request->team_assign == 'a')
            {
                $team_a = Match::select('profiles.user_id','profiles.first_name','profiles.last_name')->join('profiles','profiles.user_id','=','matches.player_id')->where('game_id',$request->game_id)->where('team_assign','a')->get();
                $success['status'] = "1";
                $success['message'] = "TEam A";
                $success['data'] = $team_a;
            }
            if($request->team_assign == 'b')
            {
                $team_b = Match::select('profiles.user_id','profiles.first_name','profiles.last_name')->join('profiles','profiles.user_id','=','matches.player_id')->where('game_id',$request->game_id)->where('team_assign','b')->get();
                $success['status'] = "1";
                $success['message'] = "TEam B";
                $success['data'] = $team_b;
            } 
            return $this->sendResponse($success);
        }
        else
        {
            $success['status'] = "0";
            $success['message'] = "Unauthorized User";
            return $this->sendResponse($success);
        }
    }

    public function start_match(Request $request)
    {
        if($request->team_id == "" || empty($request->team_id))
        {
            $success['status'] = '0';
            $success['message'] = "team id is missing";
            return $this->sendResponse($success);
        }
        if($request->game_id == "" || empty($request->game_id))
        {
            $success['status'] = '0';
            $success['message'] = "game id is missing";
            return $this->sendResponse($success);
        }
        if($request->player_players_team_a == "" || empty($request->player_players_team_a))
        {
            $success['status'] = '0';
            $success['message'] = "player players team_a id is missing";
            return $this->sendResponse($success);
        }
        if($request->player_players_team_b == "" || empty($request->player_players_team_b))
        {
            $success['status'] = '0';
            $success['message'] = "player players team_b id is missing";
            return $this->sendResponse($success);
        }
        if($request->user()->id == $request->team_id)
        {
            $mytime = Carbon::now();
            $start_time = $mytime->toDateTimeString();
            $match = Game::where('id',$request->game_id)->update(['game_start_time' => $start_time]);
            $player_players_team_a = explode(',',$request->player_players_team_a);
            $playing_positions_team_a = explode(',',$request->playing_positions_team_a);
            $player_players_team_b = explode(',',$request->player_players_team_b);
            $playing_positions_team_b = explode(',',$request->playing_positions_team_b);
            $starting_player = array();
            // $result = '';
            foreach(array_combine($player_players_team_a, $playing_positions_team_a) as $player_team_a => $player_pos_team_a)
            {
                // dd($players);
                $starting_player = array('playing_player' => '1','player_start_time' => $start_time, 'playing_pos' => $player_pos_team_a);
                $result = DB::table('matches')->where('game_id',$request->game_id)->where('player_id',$player_team_a)->where('team_assign','a')->update($starting_player);
            }
            foreach(array_combine($player_players_team_b, $playing_positions_team_b) as $player_team_b => $player_pos_team_b)
            {
                // dd($players);
                $starting_player = array('playing_player' => '1','player_start_time' => $start_time, 'playing_pos' => $player_pos_team_b);
                $result = DB::table('matches')->where('game_id',$request->game_id)->where('player_id',$player_team_b)->where('team_assign','b')->update($starting_player);
            }
            if($result == 1)
            {
                $success['status'] = '1';
                $success['message'] = "game started";
                return $this->sendResponse($success);
            }
            else
            {
                $success['status'] = '0';
                $success['message'] = "game not started";
                return $this->sendResponse($success);
            }
        }
        else
        {
            $success['status'] = "0";
            $success['message'] = "Unauthorized User";
            return $this->sendResponse($success);
        }
    }

    public function substitute_player(Request $request)
    {
        if($request->team_id == "" || empty($request->team_id))
        {
            $success['status'] = '0';
            $success['message'] = "team id is missing";
            return $this->sendResponse($success);
        }
        if($request->game_id == "" || empty($request->game_id))
        {
            $success['status'] = '0';
            $success['message'] = "match id is missing";
            return $this->sendResponse($success);
        }
        if($request->player_in_id == "" || empty($request->player_in_id))
        {
            $success['status'] = '0';
            $success['message'] = "player in id is missing";
            return $this->sendResponse($success);
        }
        if($request->player_out_id == "" || empty($request->player_out_id))
        {
            $success['status'] = '0';
            $success['message'] = "player out id is missing";
            return $this->sendResponse($success);
        }
        if($request->user()->id == $request->team_id)
        {
            $mytime = Carbon::now();
            $start_time = $mytime->toDateTimeString();
            $starting_player = array('playing_player' => '1','player_start_time' => $start_time);
            $result_start = DB::table('matches')->where('game_id',$request->game_id)->where('player_id',$request->player_in_id)->update($starting_player);
            $ending_player = array('playing_player' => '0','player_end_time' => $start_time);
            $result_end = DB::table('matches')->where('game_id',$request->game_id)->where('player_id',$request->player_out_id)->update($ending_player);
            if($result_start == '1' && $result_end == '1')
            {
                $get_playing_time = Match::select('player_start_time','player_end_time')->where('game_id',$request->game_id)->where('player_id',$request->player_out_id)->first();
                $start_time = Carbon::parse($get_playing_time->player_start_time)->format('h:i:s');

                
                $end_time = Carbon::parse($get_playing_time->player_end_time)->format('h:i:s');
                $get_minutes = (strtotime($end_time) - strtotime($start_time))/60;
                $player_time = array('time' => $get_minutes);
                $result_end = DB::table('matches')->where('game_id',$request->game_id)->where('player_id',$request->player_out_id)->update($player_time);
                // dd($get_minutes);
                $success['status'] = '1';
                $success['message'] = "player Substitute";
                return $this->sendResponse($success);
            }
            else
            {
                $success['status'] = '1';
                $success['message'] = "player not Substitute";
                return $this->sendResponse($success);   
            }
        }
        else
        {
            $success['status'] = "0";
            $success['message'] = "Unauthorized User";
            return $this->sendResponse($success);
        }
    }

    public function end_match(Request $request)
    {
        if($request->team_id == "" || empty($request->team_id))
        {
            $success['status'] = '0';
            $success['message'] = "team id is missing";
            return $this->sendResponse($success);
        }
        if($request->game_id == "" || empty($request->game_id))
        {
            $success['status'] = '0';
            $success['message'] = "match id is missing";
            return $this->sendResponse($success);
        }
        if($request->user()->id == $request->team_id)
        {

            $mytime = Carbon::now();
            $start_time = $mytime->toDateTimeString();
            $ending_player = array('player_end_time' => $start_time);
            $result_end_match = DB::table('matches')->where('game_id',$request->game_id)->where('playing_player',1)->update($ending_player);
            $ending_game = array('game_end_time' => $start_time);
            $result_end_game = DB::table('games')->where('id',$request->game_id)->update($ending_game);
            $get_playing_time = Match::select('player_id','player_start_time','player_end_time')->where('game_id',$request->game_id)->where('playing_player','1')->get();

            foreach ($get_playing_time as $key => $value) 
            {
                $end_time = Carbon::parse($value->player_end_time)->format('h:i:s');
                $start_time = Carbon::parse($value->player_start_time)->format('h:i:s');
                $get_minutes = (strtotime($end_time) - strtotime($start_time))/60;
                $player_time = array('time' => number_format((float)$get_minutes, 0, '.', ''));
                $result_end = DB::table('matches')->where('game_id',$request->game_id)->where('player_id',$value->player_id)->update($player_time);
            }
            $success['status'] = '1';
            $success['message'] = "match is finished";
            return $this->sendResponse($success);
        }
        else
        {
            $success['status'] = "0";
            $success['message'] = "Unauthorized User";
            return $this->sendResponse($success);
        }
    }

    public function check_game(Request $request)
    {
        if($request->team_id == "" || empty($request->team_id))
        {
            $success['status'] = '0';
            $success['message'] = "team id is missing";
            return $this->sendResponse($success);
        }
        if($request->user()->id == $request->team_id)
        {
            $check_game = Game::where('team_id',$request->team_id)->where('game_end_time','')->first();
        }
        else
        {
            $success['status'] = "0";
            $success['message'] = "Unauthorized User";
            return $this->sendResponse($success);
        }
    }
}
