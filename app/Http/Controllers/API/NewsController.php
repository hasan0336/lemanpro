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

class NewsController extends ResponseController
{
    public function create_news(Request $request)
    {
    	$validator = Validator::make($request->all(), [
            'team_id' => 'required',
            'title' => 'required',
            'description' => 'required',
            // 'news_image[]' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);

        if($validator->fails())
        {
            return $this->sendError($validator->errors());       
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
						$file->move('news_image',$name);
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
				$success['status'] = "1";
			    $success['message'] = "News Posted";
			    $success['data'] = '';
			    return $this->sendResponse($success);
			}
			else
			{
				$success['status'] = "1";
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
    			$news_result = News::join('news_images','news.id','=','news_images.news_id')->where('news.team_id',$request->team_id)->groupBy('news_images.news_id')->get();
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
    			// dd($rosters);
    			// $news_result = array();
    			foreach ($rosters as $key => $value) 
    			{
    				$news_result2 = News::join('news_images','news.id','=','news_images.news_id')->where('news.team_id',$value->team_id)->groupBy('news_images.news_id')->get();
    				if(count($news_result2) > 0)
    				{
    					$news_result[] = $news_result2;
    				}
    				// dd($news_result);	
    			}
    			// dd($news_result);
    			// $news_result = News::join('news_images','news.id','=','news_images.news_id')->where('news.team_id',$request->team_id)->groupBy('news_images.news_id')->get();
    			// // dd($news_result);
    			$news_pics = array();
    			$res = array();
    			foreach ($news_result[0] as $key => $value) 
    			{
    				// dd($value->news_id);
    				$res[] = $this->get_news_pictures($value->news_id);
    				// $news_result[$key]['news_pics'] = isset($res) ? $res : "";
    			}
    			foreach ($res as $key => $res_val) 
    			{

    				$news_result[0][$key]['news_pics'] = $res_val;
    			}
    			// $news_result['news_pics'] = $news_pics; 
    			$success['status'] = "1";
	    		$success['message'] = "All NEWS of Your Team";
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

    public function get_news_pictures($news_id)
    {
    	$res_news_images = DB::table('news_images')->where('news_id',$news_id)->get();
    	$news_pic_data_arr = [];
    	foreach ($res_news_images as $key => $value)
    		
    		$news_pic_data_arr[] =  URL::to('/public/news_image/'.$value->news_image);
    	return isset($news_pic_data_arr) ? $news_pic_data_arr : false;
    }
}
