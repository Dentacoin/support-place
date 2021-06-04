<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;

use DeviceDetector\Parser\Device\DeviceParserAbstract;
use DeviceDetector\DeviceDetector;

use App\Models\UserGuidedTour;
use App\Models\WhitelistIp;
use App\Models\PollAnswer;
use App\Models\UserLogin;
use App\Models\DcnReward;
use App\Models\VoxAnswer;
use App\Models\VoxScale;
use App\Models\Category;
use App\Models\Country;
use App\Models\Reward;
use App\Models\User;
use App\Models\City;
use App\Models\Poll;
use App\Models\Vox;

use Carbon\Carbon;

use Redirect;
use Session;
use Request;
use Cookie;
use Route;
use Auth;
use App;
use DB;

class FrontController extends BaseController {

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    public $request;
    public $current_page;
    public $user;

    public function __construct(\Illuminate\Http\Request $request, Route $route, $locale=null) {
        $to_redirect_404 = false;

        $roter_params = $request->route()->parameters();
        if(empty($roter_params['locale'])) { // || $roter_params['locale']=='_debugbar'
            $locale = 'en';
        } else {
            if(!empty( config('langs.'.$roter_params['locale']) ) ) {
                if(Request::getHost() == 'reviews.dentacoin.com' || Request::getHost() == 'urgent.reviews.dentacoin.com') {

                    $locale = $roter_params['locale'];
                } else {
                    $locale = 'en';
                }
            } else {
                $locale = 'en';

                $to_redirect_404 = true;
            }
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

        if (!empty($to_redirect_404)) {
            Redirect::to(getLangUrl('page-not-found'))->send();
        }

        $this->trackEvents = [];

        //$this->user = Auth::guard('web')->user();
        $this->middleware(function ($request, $next) {
            $this->user = Auth::guard('web')->user();

            if(!empty($this->user) && session('login-logged')!=$this->user->id) {

                //after login actions

                if(!session('login-logged')) {

                    $tokenobj = $this->user->createToken('LoginToken');
                    $tokenobj->token->platform = mb_strpos( Request::getHost(), 'vox' )!==false ? 'vox' : 'trp';
                    $tokenobj->token->save();

                    session([
                        'login-logged' => $this->user->id,
                        'mark-login' => mb_strpos( Request::getHost(), 'vox' )!==false ? 'DV' : 'TRP',
                        'logged_user' => [
                            'token' => $tokenobj->accessToken,
                            'id' => $this->user->id,
                            'type' => $this->user->is_dentist ? 'dentist' : 'patient',
                        ],
                    ]);
                }
            }

            if(strpos($_SERVER['HTTP_HOST'], 'dev') !== false) {
                $this->api_link = 'https://dev-api.dentacoin.com/api';
            } else {
                $this->api_link = 'https://api.dentacoin.com/api';
            }

            $request->attributes->add([
                'user' => $this->user,
            ]);

            $response = $next($request);
            $response->headers->set('Referrer-Policy', 'no-referrer');
            $response->headers->set('X-XSS-Protection', '1; mode=block');
            //$response->headers->set('X-Frame-Options', 'DENY');
     
            return $response;

            //return $next($request);
        });

    }

    public function ShowSupportView($page, $params=array(), $statusCode=null) {
        
        $text_domain = 'support';

        $params['dcn_price'] = @file_get_contents('/tmp/dcn_price');
        $params['dcn_original_price'] = @file_get_contents('/tmp/dcn_original_price');
        $params['current_page'] = $this->current_page;
        $params['current_subpage'] = $this->current_subpage;
        $params['request'] = $this->request;
        $params['user'] = $this->user;

        $params['seo_title'] = !empty($params['seo_title']) ? $params['seo_title'] : trans($text_domain.'.seo.'.$this->current_page.'.title');
        $params['seo_description'] = !empty($params['seo_description']) ? $params['seo_description'] : trans($text_domain.'.seo.'.$this->current_page.'.description');

        $params['social_title'] = !empty($params['social_title']) ? $params['social_title'] : trans($text_domain.'.social.'.$this->current_page.'.title');
        $params['social_description'] = !empty($params['social_description']) ? $params['social_description'] : trans($text_domain.'.social.'.$this->current_page.'.description');

        $params['canonical'] = !empty($params['canonical']) ? $params['canonical'] : getLangUrl($this->current_page);
        $params['social_image'] = !empty($params['social_image']) ? $params['social_image'] : url( '/img-trp/socials-cover.jpg' );

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

        $params['cache_version'] = '2021051803';

        if(!isset($params['xframe'])) {
            return response()->view('support.'.$page, $params, $statusCode ? $statusCode : 200)->header('X-Frame-Options', 'DENY');
        } else {
            return response()->view('support.'.$page, $params, $statusCode ? $statusCode : 200);
        }
    }

    public function PrepareViewData($page, &$params, $text_domain) {

        $params['dcn_price'] = @file_get_contents('/tmp/dcn_price');
        $params['dcn_original_price'] = @file_get_contents('/tmp/dcn_original_price');
        $params['dcn_change'] = @file_get_contents('/tmp/dcn_change');
        $params['current_page'] = $this->current_page;
        $params['current_subpage'] = $this->current_subpage;
        $params['request'] = $this->request;
        $params['user'] = $this->user;

        $params['seo_title'] = !empty($params['seo_title']) ? $params['seo_title'] : trans($text_domain.'.seo.'.$this->current_page.'.title');
        $params['seo_description'] = !empty($params['seo_description']) ? $params['seo_description'] : trans($text_domain.'.seo.'.$this->current_page.'.description');

        $params['social_title'] = !empty($params['social_title']) ? $params['social_title'] : trans($text_domain.'.social.'.$this->current_page.'.title');
        $params['social_description'] = !empty($params['social_description']) ? $params['social_description'] : trans($text_domain.'.social.'.$this->current_page.'.description');

        $params['canonical'] = !empty($params['canonical']) ? $params['canonical'] : getLangUrl($this->current_page);
        $params['social_image'] = !empty($params['social_image']) ? $params['social_image'] : url( $text_domain=='trp' ? '/img-trp/socials-cover.jpg' : '/img-vox/logo-text.png'  );
        //dd($params['pages_header']);
        
        //
        //Global
        //
        $platfrom = mb_strpos( Request::getHost(), 'vox' )!==false ? 'vox' : 'trp';

        $params['trackEvents'] = [];
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

        $params['cache_version'] = '2021051803';
    }
}