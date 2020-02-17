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
use App\Activity;
use DateTime;
use DatePeriod;
use DateInterval;
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
                    
                    $matches2 = Match::insert($matches);

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
                        $team_profile = Profile::where('user_id',$request->team_id)->first();
                        $success['status'] = "1";
                        $success['message'] = "Game and Match has been created";
                        $success['data'] = array('game_id'=>$game->id,'team_name'=>$team_profile->team_name, 'team_nick' =>$team_profile->team_nick);
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
                if($request->time != null || !empty($request->time))
                {
                    // dd($get_player_data->goals);
                    $total_goals = $get_player_data->goals + $request->goals;
                    $data['goals'] = $total_goals;
                    $activity_data = array(
                        'game_id' =>$request->game_id,
                        'player_id' =>$request->player_id,
                        'type' =>'goal',
                        'time' =>$request->time + 1,
                    );
                    $get_result = Activity::insert($activity_data);
                    // if($get_result)
                    // {
                    //     $success['status'] = '1';
                    //     $success['message'] = "Goal Inserted";
                    //     return $this->sendResponse($success);
                    // }
                    // else
                    // {
                    //     $success['status'] = '0';
                    //     $success['message'] = "Goal not Inserted";
                    //     return $this->sendResponse($success);
                    // }
                }
                else
                {
                    $success['status'] = '0';
                    $success['message'] = "Time is missing";
                    return $this->sendResponse($success);
                }
            }
            if($request->red != null || !empty($request->red))
            {
                if($request->time != null || !empty($request->time))
                {
                    $data['red'] = $request->red;
                    $data['yellow'] = 0;
                    $activity_data = array(
                        'game_id' =>$request->game_id,
                        'player_id' =>$request->player_id,
                        'type' =>'red',
                        'time' =>$request->time + 1,
                    );
                    $get_result = Activity::insert($activity_data);
                    $player_match_time = Match::select('player_start_time')->where('player_id',$request->player_id)->where('game_id',$request->game_id)->first();
                    $player_time = new DateTime($player_match_time->player_start_time);
                    $player_time->add(new DateInterval('PT' . $request->time . 'M'));
                    $player_end_time = $player_time->format('Y-m-d H:i:s');
                    $data['player_end_time'] = $player_end_time;
                    $match = Match::where('player_id',$request->player_id)->where('game_id',$request->game_id)->update($data);

                    $get_playing_time = Match::select('player_start_time','player_end_time')->where('game_id',$request->game_id)->where('player_id',$request->player_id)->first();
                    $start_time = Carbon::parse($get_playing_time->player_start_time)->format('h:i:s');
                    $end_time = Carbon::parse($get_playing_time->player_end_time)->format('h:i:s');
                    $get_minutes = (strtotime($end_time) - strtotime($start_time))/60;
                    $get_minutes = abs($get_minutes);
                    $player_time = array('time' => $get_minutes);
                    $match = DB::table('matches')->where('game_id',$request->game_id)->where('player_id',$request->player_id)->update($player_time);
                }
                else
                {
                    $success['status'] = '0';
                    $success['message'] = "Time is missing";
                    return $this->sendResponse($success);
                }
            }
            if($request->own_goal != null || !empty($request->own_goal))
            {
                if($request->time != null || !empty($request->time))
                {
                    $total_own_goals = $get_player_data->own_goal + $request->own_goal;
                    $data['own_goal'] = $total_own_goals;
                    $activity_data = array(
                        'game_id' =>$request->game_id,
                        'player_id' =>$request->player_id,
                        'type' =>'own_goal',
                        'time' =>$request->time + 1,
                    );
                    $get_result = Activity::insert($activity_data);
                }
                else
                {
                    $success['status'] = '0';
                    $success['message'] = "Time is missing";
                    return $this->sendResponse($success);
                }
            }
            if($request->yellow != null || !empty($request->yellow))
            {
                if($get_player_data->yellow == 1 || $get_player_data->yellow > 1)
                {
                    if($request->time != null || !empty($request->time))
                    {
                        $data['red'] = 1;
                        $data['yellow'] = 0;
                        $activity_data = array(
                            'game_id' =>$request->game_id,
                            'player_id' =>$request->player_id,
                            'type' =>'red',
                            'time' =>$request->time + 1,
                        );
                        $get_result = Activity::insert($activity_data);
                    }
                    else
                    {
                        $success['status'] = '0';
                        $success['message'] = "Time is missing";
                        return $this->sendResponse($success);
                    }
                }
                else
                {
                    if($request->time != null || !empty($request->time))
                    {
                        $data['yellow'] = $request->yellow;
                        $activity_data = array(
                            'game_id' =>$request->game_id,
                            'player_id' =>$request->player_id,
                            'type' =>'yellow',
                            'time' =>$request->time + 1,
                        );
                        $get_result = Activity::insert($activity_data);
                    }
                    else
                    {
                        $success['status'] = '0';
                        $success['message'] = "Time is missing";
                        return $this->sendResponse($success);
                    }
                }
            }
            if($request->trophies != null || !empty($request->trophies))
            {
                $data['trophies'] = $request->trophies;
            }
            $match = Match::where('player_id',$request->player_id)->where('game_id',$request->game_id)->update($data);
            if($match == 1)
            {
                $user = User::join('matches','users.id','matches.player_id')->where('matches.player_id',$request->player_id)->where('matches.game_id',$request->game_id)->first();
                //PlayerReport
                // Mail::to($user->email)->send(new PlayerReport($user));
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
            $game = Match::join('users','users.id','=','matches.player_id')->join('profiles','profiles.user_id','=','matches.player_id')->where('game_id',$request->game_id)->get();
            $team_name = Profile::where('user_id',$request->team_id)->first();
            $opponent_name = Game::where('id',$request->game_id)->first();
            // dd($opponent_name->opponent);
            foreach ($game as $key => $value) 
            {
                // dd($value);
                $users = User::where('id',$value->player_id)->first();
                $game_data = array(
                    'first_name' => $value->first_name,
                    'last_name' => $value->last_name,
                    'email' => $value->email,
                    'yellow' => $value->yellow,
                    'red' => $value->red,
                    'goals' => $value->goals,
                    'own_goals' => $value->own_goal,
                    'trophies' => $value->trophies,
                    'time' => $value->time
                );
                Mail::to($users->email)->send(new MatchReport($game_data,$users->role_id,$team_name->team_name,$opponent_name->opponent));
            }
            $users = User::where('id',$request->team_id)->first();
            Mail::to($users->email)->send(new MatchReport($game,$users->role_id,$team_name->team_name,$opponent_name->opponent));
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
                $team_a = Match::select('profiles.user_id','profiles.first_name','profiles.last_name','profiles.position','profiles.image','matches.yellow','matches.red','matches.goals','matches.own_goal','matches.trophies')->join('profiles','profiles.user_id','=','matches.player_id')->where('game_id',$request->game_id)->where('team_assign','a')->get();

                foreach ($team_a as $key => $team) 
                {
                    $team_a[$key]['image'] = URL::to('public/images/profile_images/').'/'.$team->image;
                }

                $success['status'] = "1";
                $success['message'] = "TEam A";
                $success['data'] = $team_a;
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
        if($request->extra_time == 0)
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
                $success['message'] = "playing players id is missing";
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
            if($request->playing_positions_team_a == "" || empty($request->playing_positions_team_a))
            {
                $success['status'] = '0';
                $success['message'] = "playing positions is missing";
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
                // dd($player_players_team_a);
                $playing_positions_team_a = explode(',',$request->playing_positions_team_a);
                $starting_player = array();
                // $result = '';
                foreach(array_combine($player_players_team_a, $playing_positions_team_a) as $player_team_a => $player_pos_team_a)
                {
                    // dd($player_pos_team_a);
                    $starting_player = array('playing_player' => '1','player_start_time' => $start_time, 'playing_pos' => $player_pos_team_a);
                    $result = DB::table('matches')->where('game_id',$request->game_id)->where('player_id',$player_team_a)->where('team_assign','a')->update($starting_player);
                    // dd($result);
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
        else
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
            if($request->start_time == "" || empty($request->start_time))
            {
                $success['status'] = '0';
                $success['message'] = "start time is missing";
                return $this->sendResponse($success);
            }
            $start_time = $request->start_time;
            // $timestamp = strtotime($start_time);
            // $start_time = date('Y-m-d H:i:s', $timestamp);
            // dd($timestamp);
            $match = Game::where('id',$request->game_id)->update(['ext_first_hlf_start' => $start_time]);
            $player_players_team_a = Match::where('game_id',$request->game_id)->where('playing_player',1)->get();
            // dd($player_players_team_a);
            $starting_player = array();
            // $result = '';
            foreach($player_players_team_a as $key => $player_pos_team_a)
            {
                // dd($player_pos_team_a);
                $starting_player = array('player_ext_hlf_start' => $start_time);

                $result = DB::table('matches')->where('game_id',$request->game_id)->where('player_id',$player_pos_team_a->player_id)->update($starting_player);
                // dd($result);
            }
            // dd($result);
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
            // dd($request->start_time);
            if($request->extra_time == 0)
            {
                $timestamp = $request->start_time;
                // dd($timestamp);
                $start_time = $timestamp;
                $get_pos_out = DB::table('matches')->where('game_id',$request->game_id)->where('player_id',$request->player_out_id)->first();
                
                $starting_player = array('playing_player' => '1','player_start_time' => $start_time,'playing_pos' =>$get_pos_out->playing_pos);
                $result_start = DB::table('matches')->where('game_id',$request->game_id)->where('player_id',$request->player_in_id)->update($starting_player);
                // dd($result_start);
                $ending_player = array('playing_player' => '2','player_end_time' => $start_time);
                $result_end = DB::table('matches')->where('game_id',$request->game_id)->where('player_id',$request->player_out_id)->update($ending_player);

                if($result_start == '1' && $result_end == '1')
                {
                    $get_playing_time = Match::select('player_start_time','player_end_time')->where('game_id',$request->game_id)->where('player_id',$request->player_out_id)->first();
                    $start_time = Carbon::parse($get_playing_time->player_start_time)->format('h:i:s');

                    
                    $end_time = Carbon::parse($get_playing_time->player_end_time)->format('h:i:s');
                    $get_minutes = (strtotime($end_time) - strtotime($start_time))/60;
                    $get_minutes = abs($get_minutes);
                    $player_time = array('time' => $get_minutes);
                    $result_end = DB::table('matches')->where('game_id',$request->game_id)->where('player_id',$request->player_out_id)->update($player_time);
                    
                    $get_result_sub_out_time = Activity::where('player_id',$request->player_out_id)->where('game_id',$request->game_id)->where('type','sub_in')->first();
                    // dd($get_result_sub_out_time);
                    if($get_result_sub_out_time != null || !empty($get_result_sub_out_time))
                    {
                        $activity_data_sub_in = array(
                            'game_id' =>$request->game_id,
                            'player_id' =>$request->player_in_id,
                            'type' =>'sub_in',
                            'time' =>(int)$get_minutes + (int)$get_result_sub_out_time->time + 1,
                        );
                        $get_result = Activity::insert($activity_data_sub_in);
                        
                        $activity_data_sub_out = array(
                                'game_id' =>$request->game_id,
                                'player_id' =>$request->player_out_id,
                                'type' =>'sub_out',
                                'time' =>(int)$get_minutes + (int)$get_result_sub_out_time->time + 1,
                        );
                        $get_result = Activity::insert($activity_data_sub_out);
                    }
                    else
                    {
                        $activity_data_sub_in = array(
                            'game_id' =>$request->game_id,
                            'player_id' =>$request->player_in_id,
                            'type' =>'sub_in',
                            'time' =>(int)$get_minutes + 1,
                        );
                        $get_result = Activity::insert($activity_data_sub_in);
                        
                        $activity_data_sub_out = array(
                                'game_id' =>$request->game_id,
                                'player_id' =>$request->player_out_id,
                                'type' =>'sub_out',
                                'time' =>(int)$get_minutes + 1,
                        );
                        $get_result = Activity::insert($activity_data_sub_out);   
                    }
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
                $timestamp = $request->start_time;
                // dd($timestamp);
                $start_time = $timestamp;
                $get_pos_out = DB::table('matches')->where('game_id',$request->game_id)->where('player_id',$request->player_out_id)->first();

                $starting_player = array('playing_player' => '1','player_ext_hlf_start' => $start_time,'playing_pos' =>$get_pos_out->playing_pos);
                $result_start = DB::table('matches')->where('game_id',$request->game_id)->where('player_id',$request->player_in_id)->update($starting_player);

                $ending_player = array('playing_player' => '2','player_ext_hlf_end' => $start_time);
                $result_end = DB::table('matches')->where('game_id',$request->game_id)->where('player_id',$request->player_out_id)->update($ending_player);

                if($result_start == '1' && $result_end == '1')
                {
                    $get_playing_time = Match::select('player_ext_hlf_start','player_ext_hlf_end','time')->where('game_id',$request->game_id)->where('player_id',$request->player_out_id)->first();
                    $start_time = Carbon::parse($get_playing_time->player_ext_hlf_start)->format('h:i:s');

                    
                    $end_time = Carbon::parse($get_playing_time->player_ext_hlf_end)->format('h:i:s');
                    $get_minutes = (strtotime($end_time) - strtotime($start_time))/60;
                    $get_minutes = abs($get_minutes) + $get_playing_time->time;
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
        }
        else
        {
            $success['status'] = "0";
            $success['message'] = "Unauthorized User";
            return $this->sendResponse($success);
        }
    }
    
    
    public function sub_players_list(Request $request)
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
            $team_a = Match::select('profiles.user_id','profiles.first_name','profiles.last_name','profiles.position','profiles.image','matches.yellow','matches.red','matches.goals','matches.own_goal','matches.trophies','matches.playing_player','matches.game_id')->join('profiles','profiles.user_id','=','matches.player_id')->where('game_id',$request->game_id)->whereRaw('`playing_player` IN (0,2)')->get();
            $team_b = array();
            foreach ($team_a as $key => $team) 
            {
                $team_a[$key]['image'] = URL::to('public/images/profile_images/').'/'.$team->image;
                // $team_b = Match::select('profiles.user_id','profiles.first_name','profiles.last_name','profiles.position','profiles.image','matches.yellow','matches.red','matches.goals','matches.own_goal','matches.trophies','matches.playing_player','matches.game_id')->join('profiles','profiles.user_id','=','matches.player_id')->where('game_id',$request->game_id)->where('playing_player','2')->get();
            }
            // $team_a = array_combine($team_a,$team_b);

            $success['status'] = "1";
            $success['message'] = "Team A";
            $success['data'] = $team_a;
            return $this->sendResponse($success);
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
            if($request->extra_time == 0)
            {
                $start_time = $request->end_time;
                // $start_time = date('Y-m-d H:i:s', $timestamp);
                $ending_player = array('player_end_time' => $start_time);
                $result_end_match = DB::table('matches')->where('game_id',$request->game_id)->where('playing_player',1)->where('red','!=',1)->update($ending_player);
                $ending_game = array('game_end_time' => $start_time,'game_status'=>'4');
                $result_end_game = DB::table('games')->where('id',$request->game_id)->update($ending_game);
                $get_playing_time = Match::select('player_id','player_start_time','player_end_time')->where('game_id',$request->game_id)->where('playing_player','1')->get();

                foreach ($get_playing_time as $key => $value) 
                {
                    $end_time = Carbon::parse($value->player_end_time)->format('h:i:s');
                    $start_time = Carbon::parse($value->player_start_time)->format('h:i:s');
                    $get_minutes = (strtotime($end_time) - strtotime($start_time))/60;
                    $get_minutes = abs($get_minutes);
                    $player_time = array('time' => number_format((float)$get_minutes, 0, '.', ''));
                    $result_end = DB::table('matches')->where('game_id',$request->game_id)->where('player_id',$value->player_id)->update($player_time);
                }
                $success['status'] = '1';
                $success['message'] = "match is finished";
                return $this->sendResponse($success);
            }
            else
            {
                $start_time = $request->end_time;
                // $start_time = date('Y-m-d H:i:s', $timestamp);
                $ending_player = array('player_ext_hlf_end' => $start_time);
                $result_end_match = DB::table('matches')->where('game_id',$request->game_id)->where('playing_player',1)->where('red','!=',1)->update($ending_player);
                $ending_game = array('ext_second_hlf_end' => $start_time,'game_status'=>'4');
                $result_end_game = DB::table('games')->where('id',$request->game_id)->update($ending_game);
                $get_playing_time = Match::select('time','player_id','player_ext_hlf_start','player_ext_hlf_end')->where('game_id',$request->game_id)->where('playing_player','1')->get();

                foreach ($get_playing_time as $key => $value) 
                {
                    $end_time = Carbon::parse($value->player_ext_hlf_end)->format('h:i:s');
                    $start_time = Carbon::parse($value->player_ext_hlf_start)->format('h:i:s');
                    $get_minutes = (strtotime($end_time) - strtotime($start_time))/60;
                    
                    $get_minutes = abs($get_minutes) + (int)$value->time;
                    $player_time = array('time' => number_format((float)$get_minutes, 0, '.', ''));
                    $result_end = DB::table('matches')->where('game_id',$request->game_id)->where('player_id',$value->player_id)->update($player_time);
                }
                $success['status'] = '1';
                $success['message'] = "match is finished";
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
                    $match_data = Match::where('game_id',$check_game->id)->where('playing_player',1)->join('profiles','profiles.user_id','=','matches.player_id')
                    ->groupBy('team_assign')
                    ->selectRaw('GROUP_CONCAT(player_id) as player_id,GROUP_CONCAT(playing_pos) as playing_pos,team_assign,GROUP_CONCAT(yellow) as yellow,GROUP_CONCAT(red) as red,GROUP_CONCAT(goals) as goals,SUM(goals) as total_goals,GROUP_CONCAT(own_goal) as own_goal,SUM(own_goal) as total_own_goals ,GROUP_CONCAT(trophies) as trophies,GROUP_CONCAT(profiles.first_name," ",profiles.last_name) as display_name')
                    ->get();

                    $match_sub_data = Match::where('game_id',$check_game->id)->where('playing_player',0)->where('player_start_time','==','')->join('profiles','profiles.user_id','=','matches.player_id')
                    ->groupBy('team_assign')
                    ->selectRaw('GROUP_CONCAT(player_id) as player_id,GROUP_CONCAT(playing_pos) as playing_pos,team_assign,GROUP_CONCAT(profiles.first_name," ",profiles.last_name) as display_name')
                    ->get();

                    $match_already_sub_data = Match::where('game_id',$check_game->id)->where('playing_player',0)->where('player_start_time','!=','')->where('player_end_time','!=','')->join('profiles','profiles.user_id','=','matches.player_id')
                    ->groupBy('team_assign')
                    ->selectRaw('GROUP_CONCAT(player_id) as player_id,GROUP_CONCAT(playing_pos) as playing_pos,team_assign,GROUP_CONCAT(profiles.first_name," ",profiles.last_name) as display_name')
                    ->get();

                    $data =array();

                    foreach($match_data as $key => $value)
                    {
                        if($value->team_assign == 'a' )
                        {
                            $data['player_players_team_a'] = $value->player_id;
                            $data['playing_positions_team_a'] = $value->playing_pos;
                            $data['playing_players_name'] = $value->display_name;
                            $data['yellow'] = $value->yellow;
                            $data['red'] = $value->red;
                            $data['goals'] = $value->goals;
                            $data['own_goal'] = $value->own_goal;
                            $data['trophies'] = $value->trophies;
                            $data['total_goals'] = (string)$value->total_goals;
                            $data['total_own_goals'] = (string)$value->total_own_goals;
                        }
                    }
                    foreach($match_sub_data as $key => $value)
                    {
                        if($value->team_assign == 'a' )
                        {
                            $data['substitutes_team_a'] = $value->player_id;
                            $data['substitutes_positions_team_a'] = $value->playing_pos;
                            $data['substitutes_players_name'] = $value->display_name;
                        }
                    }

                    foreach($match_already_sub_data as $key => $value)
                    {
                        if($value->team_assign == 'a' )
                        {
                            $data['already_substitutes_team_a'] = $value->player_id;
                            $data['already_substitutes_positions_team_a'] = $value->playing_pos;
                            $data['already_substitutes_players_name'] = $value->display_name;
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
                if($request->extra_time == 0)
                {
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
                else
                {
                    $pause = Game::where('id',$request->game_id)->update(['ext_first_hlf_end' => $pause_time,'game_status'=>'2']);
                    if($pause == 1)
                    {
                        $data = Game::where('id',$request->game_id)->first();
                        // dd($data);
                        if(strtotime($data->ext_first_hlf_start) == false || strtotime($data->ext_first_hlf_start) == '')
                        {
                            $data['game_start_timestamp'] = '0';
                        }
                        else
                        {
                            // $data['game_start_timestamp'] = strtotime($data->game_start_time);
                            $data['game_start_timestamp'] = $data->ext_first_hlf_start;
                        }
                        if(strtotime($data->ext_second_hlf_end) == false || strtotime($data->ext_second_hlf_end) == '')
                        {
                            $data['game_end_timestamp'] =  '0';
                        }
                        else
                        {
                            // $data['game_end_timestamp'] = strtotime($data->game_end_time);
                            $data['game_end_timestamp'] = $data->ext_second_hlf_end;
                        }
                        if(strtotime($data->ext_first_hlf_end) == false || strtotime($data->ext_first_hlf_end) == '')
                        {
                            $data['game_pause_timestamp'] =  '0';
                        }
                        else
                        {
                            // $data['game_pause_timestamp'] = strtotime($data->game_pause);
                            $data['game_pause_timestamp'] = $data->ext_first_hlf_end;
                        }
                        if(strtotime($data->ext_second_hlf_start) == false || strtotime($data->ext_second_hlf_start) == '')
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
                if($request->extra_time == 0)
                {
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
                else
                {
                    $pause = Game::where('id',$request->game_id)->update(['ext_second_hlf_start' => $resume_time,'game_status'=>'3']);
                    if($pause == 1)
                    {
                        $data = Game::where('id',$request->game_id)->first();
                        if(strtotime($data->ext_first_hlf_start) == false || strtotime($data->ext_first_hlf_start) == '')
                        {
                            $data['game_start_timestamp'] = '0';
                        }
                        else
                        {
                            // $data['game_start_timestamp'] = strtotime($data->game_start_time);
                            $data['game_start_timestamp'] = $data->ext_first_hlf_start;
                        }
                        if(strtotime($data->ext_second_hlf_end) == false || strtotime($data->ext_second_hlf_end) == '')
                        {
                            $data['game_end_timestamp'] =  '0';
                        }
                        else
                        {
                            // $data['game_end_timestamp'] = strtotime($data->game_end_time);
                            $data['game_end_timestamp'] = $data->game_end_time;
                        }
                        if(strtotime($data->ext_first_hlf_end) == false || strtotime($data->ext_first_hlf_end) == '')
                        {
                            $data['game_pause_timestamp'] =  '0';
                        }
                        else
                        {
                            // $data['game_pause_timestamp'] = strtotime($data->game_pause);
                            $data['game_pause_timestamp'] = $data->ext_first_hlf_end;
                        }
                        if(strtotime($data->ext_second_hlf_start) == false || strtotime($data->ext_second_hlf_start) == '')
                        {
                            $data['game_resume_timestamp'] = '0';
                        }
                        else
                        {
                            // $data['game_resume_timestamp'] = strtotime($data->game_resume);
                            $data['game_resume_timestamp'] = $data->ext_second_hlf_start;
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
            
        }
        else
        {
            $success['status'] = "0";
            $success['message'] = "Unauthorized User";
            return $this->sendResponse($success);
        }
    }

    // public function update_score_sheet(Request $request)
    // {
    //     if($request->team_id == "" || empty($request->team_id))
    //     {
    //         $success['status'] = '0';
    //         $success['message'] = "team id is missing";
    //         return $this->sendResponse($success);
    //     }
    //     if($request->game_id == "" || empty($request->game_id))
    //     {
    //         $success['status'] = '0';
    //         $success['message'] = "game id is missing";
    //         return $this->sendResponse($success);
    //     }
    //     elseif($request->user()->id == $request->team_id)
    //     {
    //         $match_players = explode(',',$request->players_id);
    //         $players_goals = explode(',',$request->goals);
    //         $players_red_cards = explode(',',$request->red_card);
    //         $players_yellow_cards = explode(',',$request->yellow_card);
    //         $players_own_goals = explode(',',$request->own_goals);
    //         $goals = array();
    //         $yellow_cards = array();
    //         $red_cards = array();
    //         $own_goals = array();
    //         foreach(array_combine($match_players, $players_goals) as $match_player => $player_goal)
    //         {
    //             $goals = array('goals'=>$player_goal);
    //             $result_1 = DB::table('matches')->where('game_id',$request->game_id)->where('player_id',$match_player)->update($goals);
    //         }
    //         if($own_goals != null || !empty($own_goals))
    //         {
    //             foreach(array_combine($match_players, $players_own_goals) as $match_player => $player_own_goal)
    //             {
    //                 $own_goals = array('own_goal'=>$player_own_goal);
    //                 $result_2 = DB::table('matches')->where('game_id',$request->game_id)->where('player_id',$match_player)->update($own_goals);
    //             }
    //         }
    //         foreach(array_combine($match_players, $players_red_cards) as $match_player => $player_red_card)
    //         {
    //             $red_cards = array('red'=>$player_red_card);
    //             $result_3 = DB::table('matches')->where('game_id',$request->game_id)->where('player_id',$match_player)->update($red_cards);
                
    //         }
    //         foreach(array_combine($match_players, $players_yellow_cards) as $match_player => $player_yellow_card)
    //         {
    //             $yellow_cards = array('yellow'=>$player_yellow_card);
    //             $result_4 = DB::table('matches')->where('game_id',$request->game_id)->where('player_id',$match_player)->update($yellow_cards);
    //         }
            
    //         $success['status'] = "1";
    //         $success['message'] = "Updated";
    //         return $this->sendResponse($success);
    //     }
    //     else
    //     {
    //         $success['status'] = "0";
    //         $success['message'] = "Unauthorized User";
    //         return $this->sendResponse($success);
    //     }
    // }

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
        if($request->player_id == "" || empty($request->player_id))
        {
            $success['status'] = '0';
            $success['message'] = "player id is missing";
            return $this->sendResponse($success);
        }
        if($request->type == "" || empty($request->type))
        {
            $success['status'] = '0';
            $success['message'] = "type is missing";
            return $this->sendResponse($success);
        }
        if($request->time == "" || empty($request->time))
        {
            $success['status'] = '0';
            $success['message'] = "time is missing";
            return $this->sendResponse($success);
        }
        elseif($request->user()->id == $request->team_id)
        {
            $data = array();
            $activity_data = array();
            $get_player_result = Match::where('game_id',$request->game_id)->where('player_id',$request->player_id)->first();
            if($get_player_result->playing_player == 0)
            {
                $success['status'] = "0";
                $success['message'] = "Player haven't played a single minute";
                return $this->sendResponse($success);
            }
            else
            {
                $check_sub_out = Activity::where('game_id',$request->game_id)->where('player_id',$request->player_id)->where('type','sub_out')->first();
                
                if(isset($check_sub_out) && $check_sub_out != null || !!empty($check_sub_out))
                {
                    if(isset($check_sub_out->time) && $check_sub_out->time < $request->time )
                    {
                        $success['status'] = "0";
                        $success['message'] = "Player was substituted before this time";
                        return $this->sendResponse($success);
                    }
                    else
                    {
                        if($request->type == 'goal')
                        {
                            $check_red_card = Activity::where('game_id',$request->game_id)->where('player_id',$request->player_id)->where('type','red')->first();
                            if($check_red_card != null || !empty($check_red_card))
                            {
                                if($request->time > $check_red_card->time)
                                {
                                    $success['status'] = "0";
                                    $success['message'] = "You have already received a Red card before this time";
                                    return $this->sendResponse($success);
                                }
                                else
                                {
                                    $total_goals = $get_player_result->goals + 1; 
                                    $data['goals'] = $total_goals;
                                }
                            }
                            else
                            {
                                $total_goals = $get_player_result->goals + 1; 
                                $data['goals'] = $total_goals;
                            }
                        }
                        if($request->type == 'red')
                        {
                            if($get_player_result->red == 1)
                            {
                                $success['status'] = "1";
                                $success['message'] = "You already got a red card";
                                return $this->sendResponse($success);
                            }
                            else
                            {
                                $check_goal = Activity::where('game_id',$request->game_id)->where('player_id',$request->player_id)->where('type','goal')->first();
                                if($check_goal != null || !empty($check_goal) )
                                {   
                                    if($request->time < $check_goal->time)
                                    {
                                        $success['status'] = "0";
                                        $success['message'] = "You have already scored a goal before this time";
                                        return $this->sendResponse($success);
                                    }
                                    else
                                    {
                                        
                                        $data['red'] = 1;
                                        $result = Match::where('game_id',$request->game_id)->where('player_id',$request->player_id)->update($data);
                                        $activity_data = array('time'=>$request->time, 'type'=>$request->type,'game_id'=>$request->game_id,'player_id'=>$request->player_id);
                                        $result_activity = Activity::where('game_id',$request->game_id)->where('player_id',$request->player_id)->insert($activity_data);
                        
                                        $player_time = new DateTime($get_player_result->player_end_time);
                                        $player_time->sub(new DateInterval('PT' . $request->time . 'M'));
                                        $player_end_time = $player_time->format('Y-m-d H:i:s');
                                        $end_time_data['player_end_time'] = $player_end_time;
                                        $match = Match::where('player_id',$request->player_id)->where('game_id',$request->game_id)->update($end_time_data);
                        
                                        $get_playing_time = Match::select('player_start_time','player_end_time')->where('game_id',$request->game_id)->where('player_id',$request->player_id)->first();
                                        $start_time = Carbon::parse($get_playing_time->player_start_time)->format('h:i:s');
                                        $end_time = Carbon::parse($get_playing_time->player_end_time)->format('h:i:s');
                                        $get_minutes = (strtotime($end_time) - strtotime($start_time))/60;
                                        $get_minutes = abs($get_minutes);
                                        $player_time = array('time' => $get_minutes);
                                        $match = DB::table('matches')->where('game_id',$request->game_id)->where('player_id',$request->player_id)->update($player_time);
                                        if($result == 1)
                                        {
                                            $success['status'] = "1";
                                            $success['message'] = "Player received a red card";
                                            return $this->sendResponse($success);
                                        }
                                        else
                                        {
                                            $success['status'] = "0";
                                            $success['message'] = "Not Updated";
                                            return $this->sendResponse($success);
                                        }
                                    }
                                }
                                else
                                {
                                    $data['red'] = 1;
                                    $result = Match::where('game_id',$request->game_id)->where('player_id',$request->player_id)->update($data);
                                    $activity_data = array('time'=>$request->time, 'type'=>$request->type,'game_id'=>$request->game_id,'player_id'=>$request->player_id);
                                    $result_activity = Activity::where('game_id',$request->game_id)->where('player_id',$request->player_id)->insert($activity_data);
                    
                                    $player_time = new DateTime($get_player_result->player_end_time);
                                    $player_time->sub(new DateInterval('PT' . $request->time . 'M'));
                                    $player_end_time = $player_time->format('Y-m-d H:i:s');
                                    $end_time_data['player_end_time'] = $player_end_time;
                                    $match = Match::where('player_id',$request->player_id)->where('game_id',$request->game_id)->update($end_time_data);
                    
                                    $get_playing_time = Match::select('player_start_time','player_end_time')->where('game_id',$request->game_id)->where('player_id',$request->player_id)->first();
                                    $start_time = Carbon::parse($get_playing_time->player_start_time)->format('h:i:s');
                                    $end_time = Carbon::parse($get_playing_time->player_end_time)->format('h:i:s');
                                    $get_minutes = (strtotime($end_time) - strtotime($start_time))/60;
                                    $get_minutes = abs($get_minutes);
                                    $player_time = array('time' => $get_minutes);
                                    $match = DB::table('matches')->where('game_id',$request->game_id)->where('player_id',$request->player_id)->update($player_time);
                                    if($result == 1)
                                    {
                                        $success['status'] = "1";
                                        $success['message'] = "Player received a red card";
                                        return $this->sendResponse($success);
                                    }
                                    else
                                    {
                                        $success['status'] = "0";
                                        $success['message'] = "Not Updated";
                                        return $this->sendResponse($success);
                                    }
                                }
                            }
                        }
                        if($request->type == 'yellow')
                        {
                            
                            if($get_player_result->yellow == 1)
                            {
                                $data['red'] = 1;
                                $data['yellow'] = 0;
                                if($data['red'] == 1 && $data['yellow'] == 0)
                                {
                                    $delete_yellow_activity = Activity::where('game_id',$request->game_id)->where('player_id',$request->player_id)->where('type','yellow')->delete();
                                    if($delete_yellow_activity == 1)
                                    {
                                        $result = Match::where('game_id',$request->game_id)->where('player_id',$request->player_id)->update($data);
                                        $activity_data = array('time'=>$request->time, 'type'=>'red','game_id'=>$request->game_id,'player_id'=>$request->player_id);
                                        $result_activity = Activity::where('game_id',$request->game_id)->where('player_id',$request->player_id)->insert($activity_data);
                                        $success['status'] = "1";
                                        $success['message'] = "Yellow card converted to Red card";
                                        return $this->sendResponse($success);
                                        
                                    }
                                }
                            }
                            else
                            {
                                if($get_player_result->red == 1)
                                {
                                    $success['status'] = "0";
                                    $success['message'] = "Player already have a Red card";
                                    return $this->sendResponse($success);
                                }
                                else
                                {
                                    $data['yellow'] = $get_player_result->yellow + 1;
                                }
                                
                            }
                        }
                        if($request->type == 'own_goal')
                        {
                            $check_red_card = Activity::where('game_id',$request->game_id)->where('player_id',$request->player_id)->where('type','red')->first();
                            if($check_red_card != null || !empty($check_red_card))
                            {
                                if($request->time > $check_red_card->time)
                                {
                                    $success['status'] = "0";
                                    $success['message'] = "You have already received a Red card before this time";
                                    return $this->sendResponse($success);
                                }
                                else
                                {
                                    $total_own_goals = $get_player_result->own_goal + 1;
                                    $data['own_goal'] = $total_own_goals;
                                }
                            }
                            else
                            {
                                $total_own_goals = $get_player_result->own_goal + 1;
                                $data['own_goal'] = $total_own_goals;
                            }
                        }
                        $result = Match::where('game_id',$request->game_id)->where('player_id',$request->player_id)->update($data);
                        $activity_data = array('time'=>$request->time, 'type'=>$request->type,'game_id'=>$request->game_id,'player_id'=>$request->player_id);
                        $result_activity = Activity::where('game_id',$request->game_id)->where('player_id',$request->player_id)->insert($activity_data);
                        if($result == 1)
                        {
                            $success['status'] = "1";
                            $success['message'] = "Updated";
                            return $this->sendResponse($success);
                        }
                        else
                        {
                            $success['status'] = "0";
                            $success['message'] = "Not Updated";
                            return $this->sendResponse($success);
                        } 
                    }
                }
                else
                {
                    if($request->type == 'goal')
                    {
                        $check_red_card = Activity::where('game_id',$request->game_id)->where('player_id',$request->player_id)->where('type','red')->first();
                        if($check_red_card != null || !empty($check_red_card))
                        {
                            if($request->time > $check_red_card->time)
                            {
                                $success['status'] = "0";
                                $success['message'] = "You have already received a Red card before this time";
                                return $this->sendResponse($success);
                            }
                            else
                            {
                                $total_goals = $get_player_result->goals + 1; 
                                $data['goals'] = $total_goals;
                            }
                        }
                        else
                        {
                            $total_goals = $get_player_result->goals + 1; 
                            $data['goals'] = $total_goals;
                        }
                    }
                    if($request->type == 'red')
                    {
                        if($get_player_result->red == 1)
                        {
                            $success['status'] = "1";
                            $success['message'] = "You already got a red card";
                            return $this->sendResponse($success);
                        }
                        else
                        {
                            $check_goal = Activity::where('game_id',$request->game_id)->where('player_id',$request->player_id)->where('type','goal')->first();
                            if($check_goal != null || !empty($check_goal) )
                            {   
                                if($request->time < $check_goal->time)
                                {
                                    $success['status'] = "0";
                                    $success['message'] = "You have already scored a goal before this time";
                                    return $this->sendResponse($success);
                                }
                                else
                                {
                                    
                                    $data['red'] = 1;
                                    $result = Match::where('game_id',$request->game_id)->where('player_id',$request->player_id)->update($data);
                                    $activity_data = array('time'=>$request->time, 'type'=>$request->type,'game_id'=>$request->game_id,'player_id'=>$request->player_id);
                                    $result_activity = Activity::where('game_id',$request->game_id)->where('player_id',$request->player_id)->insert($activity_data);
                    
                                    $player_time = new DateTime($get_player_result->player_end_time);
                                    $player_time->sub(new DateInterval('PT' . $request->time . 'M'));
                                    $player_end_time = $player_time->format('Y-m-d H:i:s');
                                    $end_time_data['player_end_time'] = $player_end_time;
                                    $match = Match::where('player_id',$request->player_id)->where('game_id',$request->game_id)->update($end_time_data);
                    
                                    $get_playing_time = Match::select('player_start_time','player_end_time')->where('game_id',$request->game_id)->where('player_id',$request->player_id)->first();
                                    $start_time = Carbon::parse($get_playing_time->player_start_time)->format('h:i:s');
                                    $end_time = Carbon::parse($get_playing_time->player_end_time)->format('h:i:s');
                                    $get_minutes = (strtotime($end_time) - strtotime($start_time))/60;
                                    $get_minutes = abs($get_minutes);
                                    $player_time = array('time' => $get_minutes);
                                    $match = DB::table('matches')->where('game_id',$request->game_id)->where('player_id',$request->player_id)->update($player_time);
                                    if($result == 1)
                                    {
                                        $success['status'] = "1";
                                        $success['message'] = "Player received a red card";
                                        return $this->sendResponse($success);
                                    }
                                    else
                                    {
                                        $success['status'] = "0";
                                        $success['message'] = "Not Updated";
                                        return $this->sendResponse($success);
                                    }
                                }
                            }
                            else
                            {
                                $data['red'] = 1;
                                $result = Match::where('game_id',$request->game_id)->where('player_id',$request->player_id)->update($data);
                                $activity_data = array('time'=>$request->time, 'type'=>$request->type,'game_id'=>$request->game_id,'player_id'=>$request->player_id);
                                $result_activity = Activity::where('game_id',$request->game_id)->where('player_id',$request->player_id)->insert($activity_data);
                
                                $player_time = new DateTime($get_player_result->player_end_time);
                                $player_time->sub(new DateInterval('PT' . $request->time . 'M'));
                                $player_end_time = $player_time->format('Y-m-d H:i:s');
                                $end_time_data['player_end_time'] = $player_end_time;
                                $match = Match::where('player_id',$request->player_id)->where('game_id',$request->game_id)->update($end_time_data);
                
                                $get_playing_time = Match::select('player_start_time','player_end_time')->where('game_id',$request->game_id)->where('player_id',$request->player_id)->first();
                                $start_time = Carbon::parse($get_playing_time->player_start_time)->format('h:i:s');
                                $end_time = Carbon::parse($get_playing_time->player_end_time)->format('h:i:s');
                                $get_minutes = (strtotime($end_time) - strtotime($start_time))/60;
                                $get_minutes = abs($get_minutes);
                                $player_time = array('time' => $get_minutes);
                                $match = DB::table('matches')->where('game_id',$request->game_id)->where('player_id',$request->player_id)->update($player_time);
                                if($result == 1)
                                {
                                    $success['status'] = "1";
                                    $success['message'] = "Player received a red card";
                                    return $this->sendResponse($success);
                                }
                                else
                                {
                                    $success['status'] = "0";
                                    $success['message'] = "Not Updated";
                                    return $this->sendResponse($success);
                                }
                            }
                        }
                    }
                    if($request->type == 'yellow')
                    {
                        
                        if($get_player_result->yellow == 1)
                        {
                            $data['red'] = 1;
                            $data['yellow'] = 0;
                            if($data['red'] == 1 && $data['yellow'] == 0)
                            {
                                $delete_yellow_activity = Activity::where('game_id',$request->game_id)->where('player_id',$request->player_id)->where('type','yellow')->delete();
                                if($delete_yellow_activity == 1)
                                {
                                    $result = Match::where('game_id',$request->game_id)->where('player_id',$request->player_id)->update($data);
                                    $activity_data = array('time'=>$request->time, 'type'=>'red','game_id'=>$request->game_id,'player_id'=>$request->player_id);
                                    $result_activity = Activity::where('game_id',$request->game_id)->where('player_id',$request->player_id)->insert($activity_data);
                                    $success['status'] = "1";
                                    $success['message'] = "Yellow card converted to Red card";
                                    return $this->sendResponse($success);
                                    
                                }
                            }
                        }
                        else
                        {
                            if($get_player_result->red == 1)
                            {
                                $success['status'] = "0";
                                $success['message'] = "Player already have a Red card";
                                return $this->sendResponse($success);
                            }
                            else
                            {
                                $data['yellow'] = $get_player_result->yellow + 1;
                            }
                            
                        }
                    }
                    if($request->type == 'own_goal')
                    {
                        $check_red_card = Activity::where('game_id',$request->game_id)->where('player_id',$request->player_id)->where('type','red')->first();
                        if($check_red_card != null || !empty($check_red_card))
                        {
                            if($request->time > $check_red_card->time)
                            {
                                $success['status'] = "0";
                                $success['message'] = "You have already received a Red card before this time";
                                return $this->sendResponse($success);
                            }
                            else
                            {
                                $total_own_goals = $get_player_result->own_goal + 1;
                                $data['own_goal'] = $total_own_goals;
                            }
                        }
                        else
                        {
                            $total_own_goals = $get_player_result->own_goal + 1;
                            $data['own_goal'] = $total_own_goals;
                        }
                    }
                    $result = Match::where('game_id',$request->game_id)->where('player_id',$request->player_id)->update($data);
                    $activity_data = array('time'=>$request->time, 'type'=>$request->type,'game_id'=>$request->game_id,'player_id'=>$request->player_id);
                    $result_activity = Activity::where('game_id',$request->game_id)->where('player_id',$request->player_id)->insert($activity_data);
                    if($result == 1)
                    {
                        $success['status'] = "1";
                        $success['message'] = "Updated";
                        return $this->sendResponse($success);
                    }
                    else
                    {
                        $success['status'] = "0";
                        $success['message'] = "Not Updated";
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

    public function get_player_match_data(Request $request)
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
        if($request->type == "" || empty($request->type))
        {
            $success['status'] = '0';
            $success['message'] = "game id is missing";
            return $this->sendResponse($success);
        }
        elseif($request->user()->id == $request->team_id)
        {   
            $result_activity = array();
            if($request->type == "yellow")
            {
                $result_activity = Activity::where('type',$request->type)->where('game_id',$request->game_id)->where('player_id',$request->player_id)->orderBy('time','desc')->get();   
            }
            if($request->type == "red")
            {
                $result_activity = Activity::where('type',$request->type)->where('game_id',$request->game_id)->where('player_id',$request->player_id)->get();   
            }
            if($request->type == "goal")
            {
                $result_activity = Activity::where('type',$request->type)->where('game_id',$request->game_id)->where('player_id',$request->player_id)->orderBy('time','desc')->get();   
            }
            if($request->type == "own_goal")
            {
                $result_activity = Activity::where('type',$request->type)->where('game_id',$request->game_id)->where('player_id',$request->player_id)->get();   
            }
            $player_profile = Profile::where('user_id',$request->player_id)->first();
            
            // $display_name = $player_profile->first_name.' '.$player_profile->last_name;
            // dd($display_name);
            // $result_activity['display_name'] = $display_name;
            // $result_activity['image'] = URL::to('public/images/profile_images/').'/'.$player_profile->image;
            $result_activitys = [];
            foreach($result_activity as $key => $result_activity)
            {
                $result_activitys[] = $result_activity;
                $result_activitys[$key]['first_name'] =$player_profile->first_name;
                $result_activitys[$key]['last_name'] =$player_profile->last_name;
                $result_activitys[$key]['image'] = URL::to('public/images/profile_images/').'/'.$player_profile->image;
            }
            // $result_activitys['display_name'] = $display_name;
            // $result_activitys['image'] = URL::to('public/images/profile_images/').'/'.$player_profile->image;
            $success['status'] = "1";
            $success['message'] = "result";
            $success['data'] = $result_activitys;
            return $this->sendResponse($success);
        }
        else
        {
            $success['status'] = "0";
            $success['message'] = "Unauthorized User";
            return $this->sendResponse($success);
        }
    }

    public function del_player_match_data(Request $request)
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
        if($request->type == "" || empty($request->type))
        {
            $success['status'] = '0';
            $success['message'] = "game id is missing";
            return $this->sendResponse($success);
        }
        if($request->time == "" || empty($request->time))
        {
            $success['status'] = '0';
            $success['message'] = "time is missing";
            return $this->sendResponse($success);
        }
        elseif($request->user()->id == $request->team_id)
        {
            $result_activity = Activity::where('type',$request->type)->where('game_id',$request->game_id)->where('player_id',$request->player_id)->where('time',$request->time)->delete();
            if($result_activity == 1)
            {
                $result_match = Match::where('game_id',$request->game_id)->where('player_id',$request->player_id)->first();
                if($request->type == "yellow")
                {
                    $result_activity = Activity::where('type',$request->type)->where('game_id',$request->game_id)->where('player_id',$request->player_id)->get();
                    if($result_activity == null)
                    {
                        $data = array('yellow'=> 0);
                    }
                    else
                    {
                        $data = array('yellow'=>count($result_activity));
                    }
                    $result_update = Match::where('game_id',$request->game_id)->where('player_id',$request->player_id)->update($data);   
                }
                if($request->type == "red")
                {
                    $result_activity = Activity::where('type',$request->type)->where('game_id',$request->game_id)->where('player_id',$request->player_id)->get();
                    if($result_activity == null)
                    {
                        $data = array('red'=> 0);
                    }
                    else
                    {
                        $data = array('red'=>count($result_activity));
                    }
                    $result_update = Match::where('game_id',$request->game_id)->where('player_id',$request->player_id)->update($data);   
                }
                if($request->type == "goal")
                {
                    $result_activity = Activity::where('type',$request->type)->where('game_id',$request->game_id)->where('player_id',$request->player_id)->get();
                    if($result_activity == null)
                    {
                        $data = array('goals'=> 0);
                    }
                    else
                    {
                        $data = array('goals'=>count($result_activity));
                    }
                    $result_update = Match::where('game_id',$request->game_id)->where('player_id',$request->player_id)->update($data);   
                }
                if($request->type == "own_goal")
                {
                    $result_activity = Activity::where('type',$request->type)->where('game_id',$request->game_id)->where('player_id',$request->player_id)->get();
                    if($result_activity == null)
                    {
                        $data = array('own_goal'=> 0);
                    }
                    else
                    {
                        $data = array('own_goal'=>count($result_activity));
                    }
                    $result_update = Match::where('game_id',$request->game_id)->where('player_id',$request->player_id)->update($data);   
                }
                $success['status'] = "1";
                $success['message'] = "Result Updated";
                return $this->sendResponse($success);
            }
            else
            {
                $success['status'] = "0";
                $success['message'] = "Result not Updated";
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
}
