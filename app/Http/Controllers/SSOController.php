<?php

namespace App\Http\Controllers;
use Illuminate\Routing\Controller as BaseController;

use App\Models\User;

use Response;
use Request;
use Auth;

class SSOController extends BaseController {

	protected function manageCustomCookie() {

        if(!empty(request('slug')) && !empty(request('type')) && !empty(request('token'))) {
            //logging
            $token = decryptCode(request('token'));

            $header = array();
            $header[] = 'Accept: */*';
            $header[] = 'Authorization: Bearer ' . $token;
            $header[] = 'Content-Type: application/json';
            $header[] = 'Cache-Control: no-cache';

            $url = 'https://api.dentacoin.com/api/user/';

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_POST => 0,
                CURLOPT_URL => $url,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_HTTPHEADER => $header,
            ));

            $resp = json_decode(curl_exec($curl));
            curl_close($curl);

            if(isset($resp->success) && !empty($resp->success)) {

                $type = decryptCode(request('type'));
                $approved_statuses = array('approved', 'test', 'added_by_clinic_claimed','added_by_dentist_claimed', 'clinic_branch');

                if($resp->data->self_deleted != NULL) {

                } else if(!in_array($resp->data->status, $approved_statuses) ) {

                } else {
                    session([
                        'user' => $resp->data,
                        'user_token' => $token,
                        'logged_user' => [
                            'token' => $token,
                            'id' => $slug,
                            'type' => $type,
                        ],
                    ]);
                }
            }

        } else if(!empty(request('logout-token'))) {
            //logging out
            $token = decryptCode(request('logout-token'));
            if(!empty(session('logged_user')['token']) && session('logged_user')['token'] == $token) {
                session([
                    'logged_user' => false,                    
                    'user' => null,
                    'user_token' => null,
                ]);
            }
        }

        return redirect(getLangUrl('/'));
    }
}