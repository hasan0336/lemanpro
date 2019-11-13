<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\API\ResponseController as ResponseController;
use App\User;
use Validator;
use App\Profile;
use App\Tryout;
use App\TryoutPlayers;
use DB;
class TryoutController extends ResponseController
{
    public function create_tryout(Request $request)
    {
    	$validator = Validator::make($request->all(), [
            'team_id' => 'required',
            'street' => 'required',
            'state' => 'required',
            'zipcode' => 'required',
            'timeoftryout' => 'required',
            'dateoftryout' => 'required',
            'costoftryout' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError($validator->errors());       
        }
    	if($request->user()->id == $request->team_id)
    	{
    		$tryout = Tryout::create($request->all());

    		$success['status'] = "1";
    		$success['message'] = "Tryout created";
            $success['data'] = '';
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

    public function update_tryout(Request $request)
    {
    	$validator = Validator::make($request->all(), [
            'team_id' => 'required',
            'tryout_id' => 'required',
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors());       
        }
        $data = array();
        

        if($request->user()->id == $request->team_id)
    	{
    		if($request->street != null || !empty($request->street))
	        {
	        	$data['street'] = $request->street;
	        }
	        if($request->state != null || !empty($request->state))
	        {
	        	$data['state'] = $request->state;
	        }
	        if($request->zipcode != null || !empty($request->zipcode))
	        {
	        	$data['zipcode'] = $request->zipcode;
	        }
	        if($request->timeoftryout != null || !empty($request->timeoftryout))
	        {
	        	$data['timeoftryout'] = $request->timeoftryout;
	        }
	        if($request->dateoftryout != null || !empty($request->dateoftryout))
	        {
	        	$data['dateoftryout'] = $request->dateoftryout;
	        }
	        if($request->costoftryout != null || !empty($request->costoftryout))
	        {
	        	$data['costoftryout'] = $request->costoftryout;
	        }
	        $update_tryout = Tryout::where('team_id', $request->team_id)->where('id', $request->tryout_id)->update($data);
	        if($update_tryout)
	        {
	        	$success['status'] = "1";
	    		$success['message'] = "Tryout Updated";
                $success['data'] = '';
	            return $this->sendResponse($success);
	        }
	        else
	        {
	        	$success['status'] = "0";
	    		$success['message'] = "Tryout not updated";
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

    public function tryout_listing(Request $request)
    {
    	$input['team_id'] = $request->team_id;
    	if($request->user()->id == $request->team_id)
    	{
    		$tryout = User::find($request->team_id)->tryout();
    		// dd($tryout->get());
    		$tryout_player = $tryout->with(['tryoutplayers'])->get()->toArray();
    		// dd($tryout_player);
    		$player_profile = array();
    		foreach($tryout_player as $key => $value)
    		{
    			foreach ($value['tryoutplayers'] as $key => $value) {
    				$player_info = User::where('id',$value['player_id'])->first();
    				$player_profile[$key] =  $player_info;
    			}
    		}
    		$tryout_player['players_info'] = $player_profile;
    		// dd($player_profile);
    		// dd($tryout_player);
    		
	    	if(count($tryout_player) > 0)
	    	{
	    		$success['status'] = "1";
		    	$success['message'] = "Tryout Listing";
		    	$success['data'] = $tryout_player;
		        return $this->sendResponse($success);
	    	}
	    	else
	    	{
	    		$success['status'] = "1";
		    	$success['message'] = "No Tryouts available";
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

    public function del_tryout(Request $request)
    {
    	$input['team_id'] = $request->team_id;
    	$input['tryout_id'] = $request->tryout_id;
    	if($request->user()->id == $request->team_id)
    	{
    		$del_res = Tryout::where('team_id',$input['team_id'])->where('id',$input['tryout_id'])->delete();
	    	if($del_res)
	    	{
	    		$success['status'] = "1";
		    	$success['message'] = "Tryout deleted";
                $success['data'] = '';
		        return $this->sendResponse($success);
	    	}
	    	else
	    	{
	    		$success['status'] = "1";
		    	$success['message'] = "Tryout not present";
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

    public function join_tryout(Request $request)
    {
    	$input['tryout_id'] = $request->tryout_id;
    	$input['player_id'] = $request->player_id;
        $input['stripe_id'] = $request->stripe_id;
        $input['card_brand'] = $request->card_brand;
        $input['card_last_four'] = $request->card_last_four;
        $input['trial_ends_at'] = $request->trial_ends_at;
    	if($request->user()->id == $request->player_id)
    	{
    		$check_player =TryoutPlayers::where('player_id',$request->player_id)->where('tryout_id',$request->tryout_id)->first();
            $data = array('tryout_id' => $request->tryout_id, 'player_id' => $request->player_id );
            $card_data = array('user_id' => $request->player_id,'stripe_id' => $request->stripe_id, 'card_brand' => $request->card_brand, 'card_last_four' => $request->card_last_four, 'trial_ends_at' => $request->trial_ends_at );
    		if($check_player != null || !empty($check_player))
    		{

    			$success['status'] = "1";
		    	$success['message'] = "You have already joined this tryout.";
                $success['data'] = '';
		        return $this->sendResponse($success);
    		}
    		else
    		{ 
	    		$join_player = TryoutPlayers::create($data);
	    		if($join_player)
	    		{
                    DB::table('stripe')->insert($card_data);
	    			$success['status'] = "1";
			    	$success['message'] = "Player joins tryout";
                    $success['data'] = '';
			        return $this->sendResponse($success);
	    		}
	    		else
	    		{
	    			$success['status'] = "1";
			    	$success['message'] = "Some Problem occur";
                    $success['data'] = '';
			        return $this->sendResponse($success);
	    		}
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

    // public function tryout_players_list(Request $request)
    // {
    // 	$validator = Validator::make($request->all(), [
    //         'team_id' => 'required',
    //         'tryout_id' => 'required',
    //     ]);
    //     if($validator->fails()){
    //         return $this->sendError($validator->errors());       
    //     }
    //     if($request->user()->id == $request->team_id)
    // 	{
    // 		// dd($request->tryout_id);
    // 		$tryout_players = Tryout::find($request->tryout_id)->tryoutplayers;
    // 		dd($tryout_players);
    // 	}
    // 	else
    // 	{
    // 		dd(111111);
    // 	}

    // }
}
