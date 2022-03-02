<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\FrontController;

use Illuminate\Http\Request;

use Validator;
use Response;

class AuthenticateUser extends FrontController {

    public function login(Request $request)  {

        $validator = Validator::make($request->input(), [
            'token' => array('required'),
        ]);

        if ($validator->fails()) {

            $msg = $validator->getMessageBag()->toArray();
            $ret = array(
                'success' => false,
                'messages' => array()
            );

            foreach ($msg as $field => $errors) {
                $ret['messages'][$field] = implode(', ', $errors);
            }

            return Response::json( $ret );
        } else {

            $header = array();
            $header[] = 'Accept: */*';
            $header[] = 'Authorization: Bearer ' . $request->input('token');
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

                session([
                    'user' => $resp->data,
                    'user_token' => $request->input('token'),
                    'login-logged' => $resp->data->id,
                    'mark-login' => 'TRP',
                    'logged_user' => [
                        'token' => $request->input('token'),
                        'id' => $resp->data->id,
                        'type' => $resp->data->is_dentist ? 'dentist' : 'patient',
                    ],
                ]);

                return Response::json( [
                    'success' => true
                ] );
            } else {
                return Response::json( [
                    'success' => false
                ] );
            }
        }
    }

    public function logout()  {
        session([
            'user' => null,
            'user_token' => null,
            'mark-login' => false,
            'login-logged-out' => session('logged_user')['token'] ?? null,
        ]);
        
        return redirect( getLangUrl('/') );
    }
}