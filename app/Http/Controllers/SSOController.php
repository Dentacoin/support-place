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
	        $slug = User::decrypt(request('slug'));

            $user = User::find( $slug );

            if($user) {
            	$token = User::decrypt(request('token'));
	            $type = User::decrypt(request('type'));
                $approved_statuses = array('approved', 'test', 'added_by_clinic_claimed','added_by_dentist_claimed', 'clinic_branch');

                if($user->self_deleted != NULL) {
                    return redirect(getLangUrl('page-not-found'));
                } else if(!in_array($user->status, $approved_statuses) ) {
                    return redirect(getLangUrl('page-not-found'));
                } else {
                    $session_arr = [
                        'token' => $token,
                        'id' => $slug,
                        'type' => $type
                    ];
                    session(['logged_user' => $session_arr]);
                    Auth::login($user, true);

                    if(!empty(request('dentist_slug'))) {
                        return redirect(getLangUrl('dentist/'.request('dentist_slug')));
                    }

                    return redirect(getLangUrl('/'));
                }
            } else {
                return redirect(getLangUrl('page-not-found'));
            }
        } else if(!empty(request('logout-token'))) {
            //logging out
            $token = User::decrypt(request('logout-token'));
            if(!empty(session('logged_user')['token']) && session('logged_user')['token'] == $token) {
                session([
                    'logged_user' => false
                ]);
            }

            //TRP / Vox
	        if( Auth::guard('web')->user() ) {
	            Auth::guard('web')->user()->logoutActions();
	        }
            Auth::guard('web')->logout();
        } else {
            return redirect(getLangUrl('page-not-found'));
        }
    }
}