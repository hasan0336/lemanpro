<?php
if(!function_exists('push_notification')) {
    function push_notification($data) {
        $apiKey             = env('FIREBASE_API_KEY');
        $registrationIDs    = $data['device_tokens'];
        $message            = array(
            "body"           => $data['message'],
            "title"             => $data['title'],
            "date"              => date('Y-m-d H:i:s'),
            'priority'          => "high",
            'content_available' => true,
            'vibrate'           => 1,
            'sound'             => 'sound.wav',
            'notification_type' => $data['notification_type']
        );
        $url = 'https://fcm.googleapis.com/fcm/send';
        $fields = array(
            'registration_ids' =>  $registrationIDs,
        );
        //Send Extra Data
        if(isset($data['x_data']) && !empty($data['x_data']))
            foreach($data['x_data'] as $k => $v)
                $message[$k] = $v;
        //Set key according to mobile device
        if($data['device_type'] === env('DEVICE_TYPE_ANDROID')){
            $fields['data'] = $message;
            $fields['android'] = [
                'notification'=>[
                    'sound'=>'sound.wav'
                ]
            ];
        }elseif($data['device_type'] === env('DEVICE_TYPE_IOS')){
            $fields['notification'] = $message;
            $fields['apns'] = [
                'payload'=>[
                    'sound'=>'sound.wav'
                ]
            ];
        }
        $headers = array(
            'Authorization: key='. $apiKey,
            'Content-Type: application/json'
        );
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_POST, true );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $fields ) );
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}


