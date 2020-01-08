<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\API\ResponseController as ResponseController;
use Illuminate\Support\Facades\Auth;
use App\User;
use Validator;
use App\Profile;
use File;
use App\Match;
use URL;
use DB;
use App\News;
use App\Rosters;
use App\HelpFeedback;
use App\HelpFeedbackImage;
use App\Notification;
use Mail;
use App\Mail\Help_Feedback;
class NewsController extends ResponseController
{
    public function create_news(Request $request)
    {
        if($request->team_id == "" || empty($request->team_id))
        {
            $success['status'] = '0';
            $success['message'] = "team_id ID is missing";
            return $this->sendResponse($success);
        }
        if($request->title == "" || empty($request->title))
        {
            $success['status'] = '0';
            $success['message'] = "title is missing";
            return $this->sendResponse($success);
        }
        if($request->description == "" || empty($request->description))
        {
            $success['status'] = '0';
            $success['message'] = "description is missing";
            return $this->sendResponse($success);
        }
        if($request->user()->id == $request->team_id)
    	{
    		$input = $request->all();
	        $data = array('team_id'=> $input['team_id'], 'title' => $input['title'], 'description'=> $input['description']);
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
                $get_players = Rosters::select('rosters.player_id','device_token','device_type')->join('users','rosters.player_id','=','users.id')->where('team_id',$request->team_id)->get();
                // dd($get_players);
                foreach ($get_players as $key => $player) 
                {
                    // dd($player['device_token']);
                    $notify = array(
                    'news_id'=>$news,
                    'to'=>$player['player_id'],
                    'from'=>$request->team_id,
                    'type'=>env('NOTIFICATION_TYPE_SEND_NEWS_ALERT_REQUEST'),
                    'title'=>'News',
                    'message'=>'News from Team',
                );
                    // dd($notify);
                $res_notify = Notification::create($notify);

                $token[] = $player['device_token'];
                $data = array(
                    'title' => $notify['title'],
                    'message' => $notify['message'],
                    'notification_type' => env('NOTIFICATION_TYPE_SEND_NEWS_ALERT_REQUEST')
                );
                $data['device_tokens'] = $token;
                $data['device_type'] =$player['device_type'];
                }
                push_notification($data);
				$success['status'] = "1";
			    $success['message'] = "News Posted";
			    // $success['data'] = $get_players;
			    return $this->sendResponse($success);
			}
			else
			{
				$success['status'] = "0";
			    $success['message'] = "Image is required";
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

    public function edit_news(Request $request)
    {
        $data = array();
        if($request->team_id == "" || empty($request->team_id))
        {
            $success['status'] = '0';
            $success['message'] = "team_id is missing";
            return $this->sendResponse($success);
        }

        elseif($request->news_id == "" || empty($request->news_id))
        {
            $success['status'] = '0';
            $success['message'] = "news_id is missing";
            return $this->sendResponse($success);   
        }
        if($request->user()->id == $request->team_id)
    	{	
    		$input = $request->all();
    		// dd($input);
    		if($input['title'] != '' || $input['title'] != false)
    		{
    			$data['title'] = $input['title'];
    		}
    		if($input['description'] != '' || $input['description'] != false)
    		{
    			$data['description'] = $input['description'];
    		}
    		$news = DB::table('news')->where('id',$input['news_id'])->update($data);
    		DB::table('news_images')->where('news_id', $input['news_id'])->delete();
    		$allowedfileExtension = ['jpeg','jpg','png','gif','svg'];
			$files = $request->file('news_image');
    		if($files != '' || !empty($files))
			{	
				// dd(count($files));
				foreach($files as $file)
				{
					$extension = $file->getClientOriginalExtension();
					$check = in_array($extension,$allowedfileExtension);
					if($check)
					{
						$check_images = DB::table('news_images')->where('news_id',$request->news_id)->get();
						foreach($check_images as $key => $check_image)
						{
							// dd($check_image->news_image);
							$destinationPath = public_path('/news_image/'.$check_image->news_image);
							// dd($destinationPath);
							if(file_exists($destinationPath))
							{
								unlink($destinationPath);								
							}
						}

						$name=str_random(5)."-".date('his')."-".str_random(3).".".$file->getClientOriginalExtension();
						$file->move('news_image',$name);
						$images[]=$name;
						/*Insert your data*/
						$news_image = DB::table('news_images')->insert([
										'news_image' => $name,
										'news_id' => $input['news_id'],
										]);
					}
					else
					{
						// News::where('id',$news)->delete();
						$success['status'] = "1";
				        $success['message'] = "Sorry Only Upload png, jpg, gif, jpeg, svg";
				        $success['data'] = '';
				        return $this->sendResponse($success);
					}
				/*Insert your data*/
				}

				$success['status'] = "1";
	    		$success['message'] = "News Updated";
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

    public function delete_news(Request $request)
    {
    	if($request->team_id == "" || empty($request->team_id))
        {
            $success['status'] = '0';
            $success['message'] = "team_id is missing";
            return $this->sendResponse($success);
        }
        elseif($request->news_id == "" || empty($request->news_id))
        {
            $success['status'] = '0';
            $success['message'] = "news_id is missing";
            return $this->sendResponse($success);   
        }
        if($request->user()->id == $request->team_id)
    	{
    						DB::table("news_images")->where("news_id", $request->news_id)->delete();
    		$delete_news = 	DB::table("news")->where("team_id", $request->team_id)->where("id", $request->news_id)->delete();
    		if($delete_news)
    		{
    			$success['status'] = "1";
	    		$success['message'] = "NEWS Deleed";
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

    public function news_listing(Request $request)
    {
    	if($request->team_id == "" || empty($request->team_id))
        {
            $success['status'] = '0';
            $success['message'] = "team_id is missing";
            return $this->sendResponse($success);
        }
        else
        {
        	if($request->user()->id == $request->team_id)
    		{
    			$news_result = News::select('news.id','news.team_id','news.title','news.description','news.created_at','news_images.news_id')->join('news_images','news.id','=','news_images.news_id')->where('news.team_id',$request->team_id)->orwhere('news.is_admin',1)->groupBy('news_images.news_id')->orderBy('news.created_at', 'desc')->get();
    			// dd($news_result);
    			$news_pics = array();
    			foreach ($news_result as $key => $value) {
    				// dd($value->news_id);
    				$res = $this->get_news_pictures($value->news_id);
    				$news_result[$key]['news_pics'] = isset($res) ? $res : "";
    			}
    			// $news_result['news_pics'] = $news_pics; 
    			$success['status'] = "1";
	    		$success['message'] = "All NEWS";
	    		$success['data'] = $news_result;
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
    }


    public function player_news_listing(Request $request)
    {
    	if($request->player_id == "" || empty($request->player_id))
        {
            $success['status'] = '0';
            $success['message'] = "Player ID is missing";
            return $this->sendResponse($success);
        }
        else
        {
        	if($request->user()->id == $request->player_id)
    		{

    			$rosters = Rosters::where('player_id',$request->player_id)->get();
                if(count($rosters) > 0)
                {
                    foreach ($rosters as $key => $value) 
                    {
                        $news_result2 = News::select('news.id','news.team_id','news.title','news.description','news.created_at','news_images.news_id','news_images.news_image')->join('news_images','news.id','=','news_images.news_id')->where('news.team_id',$value->team_id)->groupBy('news_images.news_id')->orderBy('news.created_at','desc')->get();
                        if(count($news_result2) > 0)
                        {
                            $news_result = $news_result2;
                        }   
                    }
                    $news_pics = array();
                    $res = array();
                    foreach ($news_result as $key => $value) 
                    {
                        // dd($value->news_id);
                        $res[] = $this->get_news_pictures($value->news_id);
                        // $news_result[$key]['news_pics'] = isset($res) ? $res : "";
                    }
                    foreach ($res as $key => $res_val) 
                    {

                        $news_result[$key]['news_pics'] = $res_val;
                    } 
                    $success['status'] = "1";
                    $success['message'] = "All NEWS of Your Team";
                    $success['data'] = $news_result;
                    return $this->sendResponse($success);
                }
                else
                {
                    $success['status'] = "1";
                    $success['message'] = "No news Posted";
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

    public function get_news_pictures($news_id)
    {
    	$res_news_images = DB::table('news_images')->where('news_id',$news_id)->get();
    	$news_pic_data_arr = [];
    	foreach ($res_news_images as $key => $value)
    		
    		$news_pic_data_arr[] =  URL::to('/public/news_image/'.$value->news_image);
    	return isset($news_pic_data_arr) ? $news_pic_data_arr : false;
    }

    public function content(Request $request)
    {
        $content_type = $request->content_type;
        if($content_type == "tc")
        {
            $get_content = DB::table('contents')->where('content_type', 'tc')->first();
            $success['status'] = "1";
            $success['message'] = "Terms and Conditions";
            $success['data'] = $get_content;
            return $this->sendResponse($success);
        }
        elseif($content_type == "pp")
        {
            $get_content = DB::table('contents')->where('content_type', 'pp')->first();
            $success['status'] = "1";
            $success['message'] = "Privacy Policy";
            $success['data'] = $get_content;
            return $this->sendResponse($success);
        }
        else
        {
            $success['status'] = "0";
            $success['message'] = "Nothing";
            return $this->sendResponse($success);
        }
    }

    public function help_feedback(Request $request)
    {
        if($request->user_id == "" || empty($request->user_id))
        {
            $success['status'] = '0';
            $success['message'] = "user_id ID is missing";
            return $this->sendResponse($success);
        }
        if($request->subject == "" || empty($request->subject))
        {
            $success['status'] = '0';
            $success['message'] = "subject is missing";
            return $this->sendResponse($success);
        }
        if($request->description == "" || empty($request->description))
        {
            $success['status'] = '0';
            $success['message'] = "description is missing";
            return $this->sendResponse($success);
        }
         else
        {
            if($request->user()->id == $request->user_id)
            {
                $input = $request->all();
                $data = array('user_id'=> $input['user_id'], 'subject' => $input['subject'], 'description'=> $input['description']);
                $images=array();
                if($request->file('hf_image') != '' || !empty($request->file('hf_image')))
                {   
                    $allowedfileExtension = ['jpeg','jpg','png','gif','svg'];
                    $files = $request->file('hf_image');
                    $help_feedback = HelpFeedback::create($data);
                    // dd($files);
                    if($files != '' || !empty($files))
                    {
                        
                        foreach($files as $file)
                        {
                            $extension = $file->getClientOriginalExtension();
                            $check=in_array($extension,$allowedfileExtension);
                            if($check)
                            {
                                
                                $name=str_random(5)."-".date('his')."-".str_random(3).".".$file->getClientOriginalExtension();
                                $file->move('public/help_feedback_images',$name);
                                $images[]=$name;
                                /*Insert your data*/
                                $help_feedback_images = array(
                                    'help_feedback_image' => $name,
                                    'help_feedback_id' => $help_feedback->id
                                );
                                $news_image = HelpFeedbackImage::insertGetId($help_feedback_images);
                            }
                        /*Insert your data*/
                            else
                            {
                                DB::table('help_feedbacks')->where('id',$help_feedback)->delete();
                                $success['status'] = "1";
                                $success['message'] = "Sorry Only Upload png, jpg, gif, jpeg, svg";
                                return $this->sendResponse($success);
                            }
                        }
                        $help_feedback = HelpFeedback::select('help_feedbacks.id','help_feedbacks.user_id','help_feedbacks.subject','help_feedbacks.description','help_feedback_images.help_feedback_image','help_feedback_images.help_feedback_id')->join('help_feedback_images','help_feedback_images.help_feedback_id','=','help_feedbacks.id')->where('help_feedbacks.id',$help_feedback->id)->first();
                        $get_help_feedback_images = HelpFeedbackImage::select('help_feedback_image')->where('help_feedback_images.help_feedback_id',$help_feedback->id)->get();
                        $h_f_iamges = array();
                        foreach ($get_help_feedback_images as $key => $value) 
                        {
                            $h_f_iamges[$key] = $value->help_feedback_image;
                        }
                        $help_feedback->help_feedback_image = $h_f_iamges;
                        // print_r($help_feedback);
                        // exit();
                        Mail::to('dev.appsnado@gmail.com')->send(new Help_Feedback($help_feedback));
                        $success['status'] = "1";
                        $success['message'] = "Help and Feedback Posted";
                        // $success['data'] = $help_feedback;
                        return $this->sendResponse($success);
                    }
                    else
                    {
                        $success['status'] = "0";
                        $success['message'] = "Sorry";
                        return $this->sendResponse($success);
                    }
                }
                else
                {
                    HelpFeedback::create($data);
                    $success['status'] = "1";
                    $success['message'] = "Help and Feedback Posted";
                    return $this->sendResponse($success);
                }
            }
        }
    }
}
