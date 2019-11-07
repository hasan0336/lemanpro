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
				        return $this->sendResponse($success);
					}
				}
				$success['status'] = "1";
			    $success['message'] = "News Posted";
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
						dd(count($check_images));
						

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
				        return $this->sendResponse($success);
					}
				/*Insert your data*/
				}

				$success['status'] = "1";
	    		$success['message'] = "News Updated";
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
