<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;

use Request;
use Route;
use App;

class FrontController extends BaseController {

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    public $request;
    public $current_page;
    public $user;

    public function __construct(\Illuminate\Http\Request $request, Route $route, $locale=null) {

        $roter_params = $request->route()->parameters();
        if(empty($roter_params['locale'])) { // || $roter_params['locale']=='_debugbar'
            $locale = 'en';
        } else {
            // $locale = $roter_params['locale'];
            $locale = 'en';
        }

        App::setLocale( $locale );
        date_default_timezone_set("Europe/Sofia");

        $this->request = $request;
        $path = explode('/', Request::path());
        $this->current_page = isset($path[1]) ? $path[1] : null;
        if(empty($this->current_page)) {
            $this->current_page='index';
        }
        
        $this->current_subpage = isset($path[2]) ? $path[2] : null;
        if(empty($this->current_subpage)) {
            $this->current_subpage='home';
        }

        $this->middleware(function ($request, $next) {
            $this->user = session('user');
            $this->api_link = 'https://api.dentacoin.com/api';
            // $this->api_link = 'https://dev-api.dentacoin.com/api';

            $request->attributes->add([
                'user' => $this->user,
            ]);

            $response = $next($request);
            $response->headers->set('Referrer-Policy', 'no-referrer');
            $response->headers->set('X-XSS-Protection', '1; mode=block');
     
            return $response;
        });

    }

    public function ShowView($page, $params=array(), $statusCode=null) {
        
        $params['current_page'] = $this->current_page;
        $params['current_subpage'] = $this->current_subpage;
        $params['request'] = $this->request;
        $params['user'] = $this->user;

        $params['socials'] = @file_get_contents('https://dentacoin.com/info/socials-data', true);

        $params['seo_title'] = !empty($params['seo_title']) ? $params['seo_title'] : 'Help Center | Dentacoin';
        $params['seo_description'] = !empty($params['seo_description']) ? $params['seo_description'] : 'Find quick answers to your question.';

        $params['social_title'] = !empty($params['social_title']) ? $params['social_title'] : 'Help Center | Dentacoin';
        $params['social_description'] = !empty($params['social_description']) ? $params['social_description'] : 'Find quick answers to your question.';

        $params['canonical'] = !empty($params['canonical']) ? $params['canonical'] : getLangUrl($this->current_page);
        $params['social_image'] = !empty($params['social_image']) ? $params['social_image'] : url( '/img-support/social-image.png' );

        if( session('mark-login') && empty($params['skipSSO']) ) {
            //dd(session('mark-login'));
            $ep = session('mark-login');
            session([
                'mark-login' => false
            ]);

            $params['markLogin'] = true;
        }
        if( session('login-logged-out') && empty($params['skipSSO']) ) {
            //dd(session('login-logged-out'));
            $params['markLogout'] = session('login-logged-out');
            session([
                'login-logged-out' => false
            ]);
        }

        $params['cache_version'] = '20220630';

        if(!isset($params['xframe'])) {
            return response()->view('support.'.$page, $params, $statusCode ? $statusCode : 200)->header('X-Frame-Options', 'DENY');
        } else {
            return response()->view('support.'.$page, $params, $statusCode ? $statusCode : 200);
        }
    }
}