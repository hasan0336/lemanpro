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
            $check_game = Game::where('team_id',$request->team_id)->where('game_end_time','')->where('game_start_time','' )->first();
            if($check_game != "" || !empty($check_game))
            {
                $success['status'] = "1";
                $success['message'] = "Game and Match already created";
                return $this->sendResponse($success);
            }
            else
            {
                $check_game_start = Game::where('team_id',$request->team_id)->where('game_end_time','')->where('game_start_time','!=','' )->first();
                if($check_game_start != null || !empty($check_game_start))
                {
                    $success['status'] = "1";
                    $success['message'] = "Game and Match already created";
                    return $this->sendResponse($success);
                }
                else
                {
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
                            $get_info = User::where('id', '=', $value)->first();
                            // dd($get_info->device_token);
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

                            $token[] = $get_info->device_token;
                            $data = array(
                                'title' => $notify['title'],
                                'message' => $notify['message'],
                                'notification_type' => env('NOTIFICATION_TYPE_SEND_PLAYER_SELECTED_REQUEST')
                            );
                            $data['device_tokens'] = $token;
                            $data['device_type'] = $get_info->device_type;
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
            $res    =   DB::table("games")->where("id", $request->game_id)->delete();
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
            $get_player_data = Match::where('player_id',$request->player_id)->where('game_id',$request->game_id)->first();
            if($request->goals != null || !empty($request->goals))
            {
                // dd($get_player_data->goals);
                $total_goals = $get_player_data->goals + $request->goals;
                $data['goals'] = $total_goals;
            }
            if($request->red != null || !empty($request->red))
            {
                $data['red'] = $request->red;
            }
            if($request->own_goal != null || !empty($request->own_goal))
            {
                $total_own_goals = $get_player_data->own_goal + $request->own_goal;
                $data['own_goal'] = $total_own_goals;
            }
            if($request->yellow != null || !empty($request->yellow))
            {
                if($get_player_data->yellow == 1 || $get_player_data->yellow > 1)
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
                $team_a = Match::select('profiles.user_id','profiles.first_name','profiles.last_name','profiles.image','matches.yellow','matches.red','matches.goals','matches.own_goal','matches.trophies')->join('profiles','profiles.user_id','=','matches.player_id')->where('game_id',$request->game_id)->where('team_assign','a')->get();

                foreach ($team_a as $key => $team) 
                {
                    $team_a[$key]['image'] = URL::to('public/images/profile_images/').'/'.$team->image;
                }

                $success['status'] = "1";
                $success['message'] = "TEam A";
                $success['data'] = $team_a;
            }
            // if($request->team_assign == 'b')
            // {
            //     $team_b = Match::select('profiles.user_id','profiles.first_name','profiles.last_name')->join('profiles','profiles.user_id','=','matches.player_id')->where('game_id',$request->game_id)->where('team_assign','b')->get();
            //     $success['status'] = "1";
            //     $success['message'] = "TEam B";
            //     $success['data'] = $team_b;
            // } 
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
        if($request->opponent == "" || empty($request->opponent))
        {
            $success['status'] = '0';
            $success['message'] = "opponent name is missing";
            return $this->sendResponse($success);
        }
        if($request->game_type == "" || empty($request->game_type))
        {
            $success['status'] = '0';
            $success['message'] = "game type is missing";
            return $this->sendResponse($success);
        }
        if($request->start_time == "" || empty($request->start_time))
        {
            $success['status'] = '0';
            $success['message'] = "start time is missing";
            return $this->sendResponse($success);
        }
        if($request->user()->id == $request->team_id)
        {
            // $mytime = Carbon::now();
            // $start_time = $mytime->toDateTimeString();
            $start_time = $request->start_time;
            // $timestamp = strtotime($start_time);
            // $start_time = date('Y-m-d H:i:s', $timestamp);
            // dd($timestamp);
            $match = Game::where('id',$request->game_id)->update(['game_start_time' => $start_time,'opponent'=>$request->opponent,'game_type'=>$request->game_type, 'game_status'=>'1']);
            $player_players_team_a = explode(',',$request->player_players_team_a);
            $playing_positions_team_a = explode(',',$request->playing_positions_team_a);
            $starting_player = array();
            // $result = '';
            foreach(array_combine($player_players_team_a, $playing_positions_team_a) as $player_team_a => $player_pos_team_a)
            {
                // dd($players);
                $starting_player = array('playing_player' => '1','player_start_time' => $start_time, 'playing_pos' => $player_pos_team_a);
                $result = DB::table('matches')->where('game_id',$request->game_id)->where('player_id',$player_team_a)->where('team_assign','a')->update($starting_player);
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
            // $mytime = Carbon::now();
            // $start_time = $mytime->toDateTimeString();
            $timestamp = $request->start_time;
            $start_time = date('Y-m-d H:i:s', $timestamp);
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
        if($request->end_time == "" || empty($request->end_time))
        {
            $success['status'] = '0';
            $success['message'] = "end time is missing";
            return $this->sendResponse($success);
        }
        if($request->user()->id == $request->team_id)
        {

            // $mytime = Carbon::now();
            // $start_time = $mytime->toDateTimeString();
            $start_time = $request->end_time;
            // $start_time = date('Y-m-d H:i:s', $timestamp);
            $ending_player = array('player_end_time' => $start_time);
            $result_end_match = DB::table('matches')->where('game_id',$request->game_id)->where('playing_player',1)->update($ending_player);
            $ending_game = array('game_end_time' => $start_time,'game_status'=>'4');
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
            
            $check_game = Game::where('team_id',$request->team_id)->where('game_end_time','')->where('game_start_time','!=','' )->first();
            
            if($check_game != null && $check_game != '')
            {
                if($check_game->game_start_time != '' && $check_game->game_end_time == '' )
                {
                    $match_data = Match::where('game_id',$check_game->id)->where('playing_player',1)
                    ->groupBy('team_assign')
                    ->selectRaw('GROUP_CONCAT(player_id) as player_id,GROUP_CONCAT(playing_pos) as playing_pos,team_assign,GROUP_CONCAT(yellow) as yellow,GROUP_CONCAT(red) as red,GROUP_CONCAT(goals) as goals,SUM(goals) as total_goals,GROUP_CONCAT(own_goal) as own_goal,SUM(own_goal) as total_own_goals ,GROUP_CONCAT(trophies) as trophies')
                    ->get();
                    $data =array();

                    foreach($match_data as $key => $value)
                    {
                        if($value->team_assign == 'a' )
                        {
                            $data['player_players_team_a'] = $value->player_id;
                            $data['playing_positions_team_a'] = $value->playing_pos;
                            $data['yellow'] = $value->yellow;
                            $data['red'] = $value->red;
                            $data['goals'] = $value->goals;
                            $data['own_goal'] = $value->own_goal;
                            $data['trophies'] = $value->trophies;
                            $data['total_goals'] = (string)$value->total_goals;
                            $data['total_own_goals'] = (string)$value->total_own_goals;
                        }
                    }
                    $data['team_id'] = $request->team_id;
                    $data['game_id'] = $check_game->id;
                    $data['opponent'] = $check_game->opponent;
                    $data['game_type'] = $check_game->game_type;
                    $data['game_status'] = $check_game->game_status;
                    
                    if(strtotime($check_game->game_start_time) == false || strtotime($check_game->game_start_time) == '')
                    {
                        $check_game->game_start_time = '0';
                        $data['game_start_timestamp'] = $check_game->game_start_time;
                    }
                    else
                    {  
                        // $check_game->game_start_time = strtotime($check_game->game_start_time);
                        $data['game_start_timestamp'] = $check_game->game_start_time;
                    }
                    if(strtotime($check_game->game_end_time) == false || strtotime($check_game->game_end_time) == '')
                    {
                        $check_game->game_end_time =  '0';
                        $data['game_end_timestamp'] =  $check_game->game_end_time;
                    }
                    else
                    {
                        // $check_game->game_end_time = strtotime($check_game->game_end_time);
                        $data['game_end_timestamp'] = $check_game->game_end_time;
                    }
                    if(strtotime($check_game->game_pause) == false || strtotime($check_game->game_pause) == '')
                    {
                        $check_game->game_pause =  '0';
                        $data['game_pause_timestamp'] =  $check_game->game_pause;
                    }
                    else
                    {
                        // $check_game->game_pause = strtotime($check_game->game_pause);
                        $data['game_pause_timestamp'] = $check_game->game_pause ;
                    }
                    if(strtotime($check_game->game_resume) == false || strtotime($check_game->game_resume) == '')
                    {
                        $check_game->game_resume = '0';
                        $data['game_resume_timestamp'] = $check_game->game_resume ;
                    }
                    else
                    {
                        // $check_game->game_resume = strtotime($check_game->game_resume);
                        $data['game_resume_timestamp'] = $check_game->game_resume;
                    }
                    $data['game_id'] = $check_game->id;

                    $success['status'] = '1';
                    $success['message'] = "game is in process";
                    $success['data'] = $data;
                    return $this->sendResponse($success);
                }
                else
                {
                    $success['status'] = '0';
                    $success['message'] = "game is already ended";
                    return $this->sendResponse($success);
                }
            }
            else
            {
                $check_game = Game::where('team_id',$request->team_id)->where('game_end_time','')->where('game_start_time','')->first();
                
                if($check_game != "" || !empty($check_game))
                {
                    $check_game['player_players_team_a'] = '';
                    $check_game['playing_positions_team_a'] = '';
                    $check_game['yellow'] = '';
                    $check_game['red'] = '';
                    $check_game['goals'] = '';
                    $check_game['own_goal'] = '';
                    $check_game['trophies'] = '';

                    $check_game['team_id'] = $request->team_id;
                    $check_game['game_id'] = $check_game->id;
                    $check_game['opponent'] = $check_game->opponent;
                    $check_game['game_type'] = $check_game->game_type;
                    $check_game['game_status'] = $check_game->game_status;
                    
                    // dd($check_game->game_start_time);
                    if(strtotime($check_game->game_start_time) == false || strtotime($check_game->game_start_time) == '')
                    {
                        $check_game->game_start_time = '0';
                        $data['game_start_timestamp'] = $check_game->game_start_time;
                    }
                    else
                    {  
                        // $check_game->game_start_time = strtotime($check_game->game_start_time);
                        $data['game_start_timestamp'] = $check_game->game_start_time;
                    }
                    if(strtotime($check_game->game_end_time) == false || strtotime($check_game->game_end_time) == '')
                    {
                        $check_game->game_end_time =  '0';
                        $data['game_end_timestamp'] =  $check_game->game_end_time;
                    }
                    else
                    {
                        // $check_game->game_end_time = strtotime($check_game->game_end_time);
                        $data['game_end_timestamp'] = $check_game->game_end_time;
                    }
                    if(strtotime($check_game->game_pause) == false || strtotime($check_game->game_pause) == '')
                    {
                        $check_game->game_pause =  '0';
                        $data['game_pause_timestamp'] =  $check_game->game_pause;
                    }
                    else
                    {
                        // $check_game->game_pause = strtotime($check_game->game_pause);
                        $data['game_pause_timestamp'] = $check_game->game_pause ;
                    }
                    if(strtotime($check_game->game_resume) == false || strtotime($check_game->game_resume) == '')
                    {
                        $check_game->game_resume = '0';
                        $data['game_resume_timestamp'] = $check_game->game_resume ;
                    }
                    else
                    {
                        // $check_game->game_resume = strtotime($check_game->game_resume);
                        $data['game_resume_timestamp'] = $check_game->game_resume;
                    }
                    $data['game_id'] = $check_game->id;
                    $success['status'] = "1";
                    $success['message'] = "Game and Match are created";
                    $success['data'] = $check_game;
                    return $this->sendResponse($success);
                }
                else
                {
                    $success['status'] = '0';
                    $success['message'] = "game is already ended";
                    return $this->sendResponse($success);
                }
            }
        }   
        else
        {
            $success['status'] = "0";
            $success['message'] = "Unauthorized User";
            return $this->sendResponse($success);
        }
    }

    public function pause_game(Request $request)
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
        if($request->action == "" || empty($request->action))
        {
            $success['status'] = '0';
            $success['message'] = "action is missing";
            return $this->sendResponse($success);
        }
        if($request->user()->id == $request->team_id)
        {
            if($request->action == "pause")
            {
                if($request->pause_time == "" || empty($request->pause_time))
                {
                    $success['status'] = '0';
                    $success['message'] = "pause time is missing";
                    return $this->sendResponse($success);
                }
                // $mytime = Carbon::now();
                // $pause_time = $mytime->toDateTimeString();
                $pause_time = $request->pause_time;
                // $pause_time = date('Y-m-d H:i:s', $timestamp);
                $pause = Game::where('id',$request->game_id)->update(['game_pause' => $pause_time,'game_status'=>'2']);
                if($pause == 1)
                {
                    $data = Game::where('id',$request->game_id)->first();
                    // dd($data);
                    if(strtotime($data->game_start_time) == false || strtotime($data->game_start_time) == '')
                    {
                        $data['game_start_timestamp'] = '0';
                    }
                    else
                    {
                        // $data['game_start_timestamp'] = strtotime($data->game_start_time);
                        $data['game_start_timestamp'] = $data->game_start_time;
                    }
                    if(strtotime($data->game_end_time) == false || strtotime($data->game_end_time) == '')
                    {
                        $data['game_end_timestamp'] =  '0';
                    }
                    else
                    {
                        // $data['game_end_timestamp'] = strtotime($data->game_end_time);
                        $data['game_end_timestamp'] = $data->game_end_time;
                    }
                    if(strtotime($data->game_pause) == false || strtotime($data->game_pause) == '')
                    {
                        $data['game_pause_timestamp'] =  '0';
                    }
                    else
                    {
                        // $data['game_pause_timestamp'] = strtotime($data->game_pause);
                        $data['game_pause_timestamp'] = $data->game_pause;
                    }
                    if(strtotime($data->game_resume) == false || strtotime($data->game_resume) == '')
                    {
                        $data['game_resume_timestamp'] = '0';
                    }
                    else
                    {
                        // $data['game_resume_timestamp'] = strtotime($data->game_resume);
                        $data['game_resume_timestamp'] = $data->game_resume;
                    }
                    $data['game_id'] = $data->id;
                    $data['team_id'] = (string)$data->team_id;
                    
                    $success['status'] = '1';
                    $success['message'] = "Game is pause";
                    $success['data'] = $data;
                    return $this->sendResponse($success);
                }
                else
                {
                    $success['status'] = '0';
                    $success['message'] = "Game is not pause";
                    return $this->sendResponse($success);
                }
            }
            elseif ($request->action == "resume") 
            {
                if($request->resume_time == "" || empty($request->resume_time))
                {
                    $success['status'] = '0';
                    $success['message'] = "resume time is missing";
                    return $this->sendResponse($success);
                }
                // $mytime = Carbon::now();
                // $resume_time = $mytime->toDateTimeString();
                $resume_time = $request->resume_time;
                // $resume_time = date('Y-m-d H:i:s', $timestamp);
                $pause = Game::where('id',$request->game_id)->update(['game_resume' => $resume_time,'game_status'=>'3']);
                if($pause == 1)
                {
                    $data = Game::where('id',$request->game_id)->first();
                    if(strtotime($data->game_start_time) == false || strtotime($data->game_start_time) == '')
                    {
                        $data['game_start_timestamp'] = '0';
                    }
                    else
                    {
                        // $data['game_start_timestamp'] = strtotime($data->game_start_time);
                        $data['game_start_timestamp'] = $data->game_start_time;
                    }
                    if(strtotime($data->game_end_time) == false || strtotime($data->game_end_time) == '')
                    {
                        $data['game_end_timestamp'] =  '0';
                    }
                    else
                    {
                        // $data['game_end_timestamp'] = strtotime($data->game_end_time);
                        $data['game_end_timestamp'] = $data->game_end_time;
                    }
                    if(strtotime($data->game_pause) == false || strtotime($data->game_pause) == '')
                    {
                        $data['game_pause_timestamp'] =  '0';
                    }
                    else
                    {
                        // $data['game_pause_timestamp'] = strtotime($data->game_pause);
                        $data['game_pause_timestamp'] = $data->game_pause;
                    }
                    if(strtotime($data->game_resume) == false || strtotime($data->game_resume) == '')
                    {
                        $data['game_resume_timestamp'] = '0';
                    }
                    else
                    {
                        // $data['game_resume_timestamp'] = strtotime($data->game_resume);
                        $data['game_resume_timestamp'] = $data->game_resume;
                    }

                    $data['game_id'] = $data->id;
                    $data['team_id'] = (string)$data->team_id;
                    $success['status'] = '1';
                    $success['message'] = "Game is resume";
                    $success['data'] = $data;
                    return $this->sendResponse($success);
                }
                else
                {
                    $success['status'] = '0';
                    $success['message'] = "Game is not resume";
                    return $this->sendResponse($success);
                }
            }
            
        }
        else
        {
            $success['status'] = "0";
            $success['message'] = "Unauthorized User";
            return $this->sendResponse($success);
        }
    }

    public function update_score_sheet(Request $request)
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
        elseif($request->user()->id == $request->team_id)
        {
            $match_players = explode(',',$request->players_id);
            $players_goals = explode(',',$request->goals);
            $players_red_cards = explode(',',$request->red_card);
            $players_yellow_cards = explode(',',$request->yellow_card);
            $players_own_goals = explode(',',$request->own_goals);
            $goals = array();
            $yellow_cards = array();
            $red_cards = array();
            $own_goals = array();
            foreach(array_combine($match_players, $players_goals) as $match_player => $player_goal)
            {
                $goals = array('goals'=>$player_goal);
                $result_1 = DB::table('matches')->where('game_id',$request->game_id)->where('player_id',$match_player)->update($goals);
            }
            if($own_goals != null || !empty($own_goals))
            {
                foreach(array_combine($match_players, $players_own_goals) as $match_player => $player_own_goal)
                {
                    $own_goals = array('own_goal'=>$player_own_goal);
                    $result_2 = DB::table('matches')->where('game_id',$request->game_id)->where('player_id',$match_player)->update($own_goals);
                }
            }
            foreach(array_combine($match_players, $players_red_cards) as $match_player => $player_red_card)
            {
                $red_cards = array('red'=>$player_red_card);
                $result_3 = DB::table('matches')->where('game_id',$request->game_id)->where('player_id',$match_player)->update($red_cards);
                
            }
            foreach(array_combine($match_players, $players_yellow_cards) as $match_player => $player_yellow_card)
            {
                $yellow_cards = array('yellow'=>$player_yellow_card);
                $result_4 = DB::table('matches')->where('game_id',$request->game_id)->where('player_id',$match_player)->update($yellow_cards);
            }
            
            $success['status'] = "1";
            $success['message'] = "Updated";
            return $this->sendResponse($success);
        }
        else
        {
            $success['status'] = "0";
            $success['message'] = "Unauthorized User";
            return $this->sendResponse($success);
        }
    }
}
