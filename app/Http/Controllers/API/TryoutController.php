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
use Stripe\Error\Card;
// use Cartalyst\Stripe\Stripe;
use Stripe;
use URL;
use App\Notification;
class TryoutController extends ResponseController
{
    public function create_tryout(Request $request)
    {
      if($request->team_id == "" || empty($request->team_id))
        {
            $success['status'] = '0';
            $success['message'] = "team_id is missing";
            return $this->sendResponse($success);
        }
        elseif($request->street == "" || empty($request->street))
        {
            $success['status'] = '0';
            $success['message'] = "street is missing";
            return $this->sendResponse($success);   
        }
        elseif($request->latitude == "" || empty($request->latitude))
        {
            $success['status'] = '0';
            $success['message'] = "latitude is missing";
            return $this->sendResponse($success);   
        }
        elseif($request->longitude == "" || empty($request->longitude))
        {
            $success['status'] = '0';
            $success['message'] = "longitude is missing";
            return $this->sendResponse($success);   
        }
        elseif($request->zipcode == "" || empty($request->zipcode))
        {
            $success['status'] = '0';
            $success['message'] = "zipcode is missing";
            return $this->sendResponse($success);   
        }
        elseif($request->timeoftryout == "" || empty($request->timeoftryout))
        {
            $success['status'] = '0';
            $success['message'] = "time of try out is missing";
            return $this->sendResponse($success);   
        }
        elseif($request->dateoftryout == "" || empty($request->dateoftryout))
        {
            $success['status'] = '0';
            $success['message'] = "date of try out is missing";
            return $this->sendResponse($success);   
        }
        elseif($request->costoftryout == "" || empty($request->costoftryout))
        {
            $success['status'] = '0';
            $success['message'] = "cost of try out is missing";
            return $this->sendResponse($success);   
        }
      	elseif($request->user()->id == $request->team_id)
      	{
      		$tryout = Tryout::create($request->all());

              
      		$success['status'] = "1";
      		$success['message'] = "Tryout created";
          return $this->sendResponse($success);
      	}
      	else
      	{
              
              $success['status'] = "0";
              $success['message'] = "Unauthorized User";
              return $this->sendResponse($success);
      	}
    }

    public function update_tryout(Request $request)
    {
      if($request->team_id == "" || empty($request->team_id))
      {
          $success['status'] = '0';
          $success['message'] = "team_id is missing";
          return $this->sendResponse($success);
      }
      elseif($request->tryout_id == "" || empty($request->tryout_id))
      {
          $success['status'] = '0';
          $success['message'] = "tryout_id is missing";
          return $this->sendResponse($success);   
      }
        $data = array();
      if($request->user()->id == $request->team_id)
    	{
    		if($request->street != null || !empty($request->street))
        {
        	$data['street'] = $request->street;
        }
        if($request->latitude != null || !empty($request->latitude))
        {
          $data['latitude'] = $request->latitude;
        }
        if($request->longitude != null || !empty($request->longitude))
        {
          $data['longitude'] = $request->longitude;
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
          return $this->sendResponse($success);
        }
        else
        {
        	$success['status'] = "0";
    		  $success['message'] = "Tryout not updated";
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

    public function tryout_listing(Request $request)
    {
      if($request->team_id == "" || empty($request->team_id))
        {
            $success['status'] = '0';
            $success['message'] = "team_id is missing";
            return $this->sendResponse($success);
        }
    	$input['team_id'] = $request->team_id;
    	if($request->user()->id == $request->team_id)
    	{
        $tryout_listing = Tryout::select('profiles.first_name','profiles.last_name','profiles.team_name','tryouts.id','tryouts.team_id','tryouts.street','tryouts.state','tryouts.zipcode','tryouts.timeoftryout','tryouts.dateoftryout','tryouts.costoftryout','tryouts.latitude','tryouts.longitude','tryouts.created_at')->join('profiles','profiles.user_id','=','tryouts.team_id')->where('team_id',$input['team_id'])->get();
	    	if(count($tryout_listing) > 0)
	    	{
	    		$success['status'] = "1";
		    	$success['message'] = "Tryout Listing";
		    	$success['data'] = $tryout_listing;
		      return $this->sendResponse($success);
	    	}
	    	else
	    	{
	    		$success['status'] = "1";
		    	$success['message'] = "No Tryouts available";
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

    public function single_tryout_info(Request $request)
    {
      $input['team_id'] = $request->team_id;
      $input['player_id'] = $request->player_id;
      $input['tryout_id'] = $request->tryout_id;
      if($input['player_id'] == null || empty($input['player_id']))
      {
        if($request->user()->id == $request->team_id)
        {
          $tryout_info = Tryout::select('profiles.first_name','profiles.last_name','profiles.team_name','tryouts.id','tryouts.team_id','tryouts.street','tryouts.state','tryouts.zipcode','tryouts.timeoftryout','tryouts.dateoftryout','tryouts.costoftryout','tryouts.latitude','tryouts.longitude','tryouts.created_at')->join('profiles','profiles.user_id','=','tryouts.team_id')->where('team_id',$input['team_id'])->where('tryouts.id',$input['tryout_id'])->first();
          $success['status'] = "1";
          $success['message'] = "Tryout data";
          $success['data'] = $tryout_info;
          return $this->sendResponse($success);
        }
        else
        {
              $success['status'] = "0";
              $success['message'] = "Unauthorized User";
              return $this->sendResponse($success);
        }
      }
      elseif($input['team_id'] == null || empty($input['team_id']))
      {
        //tryout price screen
        if($request->user()->id == $request->player_id)
        {
          $tryout_info = Tryout::select('profiles.first_name','profiles.last_name','profiles.team_name','tryouts.id','tryouts.team_id','tryouts.street','tryouts.state','tryouts.zipcode','tryouts.timeoftryout','tryouts.dateoftryout','tryouts.costoftryout','tryouts.latitude','tryouts.longitude','tryouts.created_at')->join('profiles','profiles.user_id','=','tryouts.team_id')->where('tryouts.id',$input['tryout_id'])->first();
          $leman_pro_fees = DB::table('lemanpro_fees')->first();
          $tryout_info['lemanpro_fees'] = $leman_pro_fees->lemanpro_fee;
          $success['status'] = "1";
          $success['message'] = "Tryout data";
          $success['data'] = $tryout_info;
          return $this->sendResponse($success);
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
        $success['status'] = "0";
        $success['message'] = "error";
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
		        return $this->sendResponse($success);
	    	}
	    	else
	    	{
	    		$success['status'] = "1";
		    	$success['message'] = "Tryout not present";
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

    public function tryout_participants(Request $request)
    {
      $input['team_id'] = $request->team_id;
      $input['tryout_id'] = $request->tryout_id;
      if($request->user()->id == $request->team_id)
      {
        $tryout_participants = TryoutPlayers::join('profiles','tryout_players.player_id','=','profiles.user_id')->select('profiles.user_id as player_id','profiles.image',DB::raw('CONCAT('."profiles.first_name".'," ",'."profiles.last_name".') AS display_name'))->where('tryout_id',$input['tryout_id'])->get();
        if(count($tryout_participants) > 0)
        {
          $participants_info = array();
          foreach($tryout_participants as $key => $tryout_participant)
          {
            $tryout_participant['image'] = URL::to('public/images/profile_images/'.$tryout_participant['image']);
            $participants_info[] = $tryout_participant;
          }
          // dd($tryout_participants);
          $success['status'] = "1";
          $success['message'] = "All Participants";
          $success['data'] = $participants_info;
          return $this->sendResponse($success);
        }
        else
        {
          $success['status'] = "1";
          $success['message'] = "No Participants Available";
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

    public function join_tryout(Request $request)
    {
      if($request->tryout_id == "" || empty($request->tryout_id))
        {
            $success['status'] = '0';
            $success['message'] = "tryout_id is missing";
            return $this->sendResponse($success);
        }
      elseif($request->player_id == "" || empty($request->player_id))
        {
            $success['status'] = '0';
            $success['message'] = "player_id is missing";
            return $this->sendResponse($success);   
        }
      elseif($request->card_no == "" || empty($request->card_no))
        {
            $success['status'] = '0';
            $success['message'] = "card_no is missing";
            return $this->sendResponse($success);   
        }
      elseif($request->exp_month == "" || empty($request->exp_month))
        {
            $success['status'] = '0';
            $success['message'] = "exp_month is missing";
            return $this->sendResponse($success);   
        }
      elseif($request->cvc == "" || empty($request->cvc))
        {
            $success['status'] = '0';
            $success['message'] = "cvc is missing";
            return $this->sendResponse($success);   
        }
      elseif($request->amount == "" || empty($request->amount))
        {
            $success['status'] = '0';
            $success['message'] = "amount is missing";
            return $this->sendResponse($success);   
        }
    	elseif($request->user()->id == $request->player_id)
    	{
        $input['tryout_id'] = $request->tryout_id;
        $input['player_id'] = $request->player_id;
        $input['number'] = $request->card_no;
        $input['exp_month'] = $request->exp_month;
        $input['exp_year'] = $request->exp_year;
        $input['cvc'] = $request->cvc;
        $input['amount'] = $request->amount;
    		$check_player =TryoutPlayers::where('player_id',$input['player_id'])->where('tryout_id',$input['tryout_id'])->first();
        $tryout_team = Tryout::select('profiles.user_id')->join('profiles','tryouts.team_id','=','profiles.user_id')->where('tryouts.id',$input['tryout_id'])->first();
        $team_id = $tryout_team->user_id;
    		if($check_player != null || !empty($check_player))
    		{
                
    			$success['status'] = "1";
		    	$success['message'] = "You have already joined this tryout.";
		      return $this->sendResponse($success);
    		}
    		else
    		{ 
          $leman_pro_fees = DB::table('lemanpro_fees')->first();
          $input['amount'] = $input['amount'] + $leman_pro_fees->lemanpro_fee;
          $stripe = Stripe::setApiKey(env('STRIPE_SECRET'));
          $token = $stripe->tokens()->create
          ([
            'card' => 
              [
                'number' => $input['number'],
                'exp_month' => $input['exp_month'],
                'exp_year' => $input['exp_year'],
                'cvc' => $input['cvc'],
              ],
          ]);
          if (!isset($token['id'])) 
          {
              $success['status'] = "0";
              $success['message'] = "Card details failed.";
              return $this->sendResponse($success);
          }
          $charge = $stripe->charges()->create
          ([
              'card' => $token['id'],
              'currency' => 'USD',
              'amount' => $input['amount'],
              'description' => 'wallet',
          ]);
          
          if($charge['status'] == 'succeeded') 
          {
              $card_brand = $charge['payment_method_details']['card']['brand'];//'visa';
              $card_last_four_digit = $charge['payment_method_details']['card']['last4']; //$request->card_no;
              $card_expiry = $charge['payment_method_details']['card']['exp_month'].'/'.$charge['payment_method_details']['card']['exp_year'];//'05/23';
              $token['id'] = $token['id'];
              $data = array('tryout_id' => $request->tryout_id, 'player_id' => $request->player_id );
              $player_id = $input['player_id'];
              $tryout_id = $input['tryout_id'];
              $join_player = TryoutPlayers::create($data);
              $team_user = User::where('id',$team_id)->first();
              if($join_player->id)
              {
                $notify = array(
                    'tryout_id'=>(int)$tryout_id,
                    'to'=>$team_id,
                    'from'=>$player_id,
                    'type'=>env('NOTIFICATION_TYPE_SEND_PURCHASE_TRYOUT_REQUEST'),
                    'title'=>'Tryout Purchase',
                    'message'=>'Tryout Purchase by Player',
                );
                $res_notify = Notification::create($notify);

                $device_token[] = $team_user->device_token;
                $data = array(
                    'title' => $notify['title'],
                    'message' => $notify['message'],
                    'notification_type' => env('NOTIFICATION_TYPE_SEND_PURCHASE_TRYOUT_REQUEST'),
                    'x_data' => ['tryout_id' => (int)$tryout_id]
                );
                $data['device_tokens'] = $device_token;
                $data['device_type'] = $team_user->device_type;
                $datasvvef = push_notification($data);
                
                  $card_data = array('user_id' => $request->player_id,'stripe_id' => $token['id'], 'card_brand' => $card_brand, 'card_last_four' => $card_last_four_digit, 'trial_ends_at' => $card_expiry );
                  DB::table('stripe')->insert($card_data);
              }
              else
              {
                  $success['status'] = "0";
                  $success['message'] = "Some Problem occur";
                  return $this->sendResponse($success);
              }
              $success['status'] = "1";
              $success['message'] = "Player joins tryout";
              // $success['data'] = $charge;
              return $this->sendResponse($success);
          //     return redirect()->route('addmoney.paymentstripe');
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
}
