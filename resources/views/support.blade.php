<!DOCTYPE html>
<html>
    <head>
        <base href="{{ url('/') }}">
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="height=device-height, width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no, target-densitydpi=device-dpi">
        
        @if(!empty($noIndex))
        	<meta name="robots" content="noindex">
        @endif

        <title>{{ $seo_title }}</title>
        <meta name="description" content="{{ $seo_description }}">
        <link rel="canonical" href="{{ $canonical }}" />

        @if(!empty($og_url))
			<meta property="og:url" content="{{ $og_url }}" />
		@endif

        <meta property="og:locale" content="{{ App::getLocale() }}" />
        <meta property="og:title" content="{{ $social_title }}"/>
        <meta property="og:description" content="{{ $social_description }}"/>
        <meta property="og:image" content="{{ $social_image }}"/>
        <meta property="og:site_name" content="{{ trans('front.social.site-name') }}" />
        
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="{{ $social_title }}" />
        <meta name="twitter:description" content="{{ $social_description }}" />
        <meta name="twitter:image" content="{{ $social_image }}"/>

        <meta name="csrf-token" content="{{ csrf_token() }}"/>

		<link rel="stylesheet" type="text/css" href="{{ url('/css/new-style-support.css').'?ver='.$cache_version }}" />
		
		<link rel="preload" href="{{ url('fonts/Calibri-Light.woff2') }}" as="font" crossorigin>
		<link rel="preload" href="{{ url('fonts/Calibri-Bold.woff2') }}" as="font" crossorigin>
		<link rel="preload" href="{{ url('fonts/Calibri.woff2') }}" as="font" crossorigin>
		
        @if(!empty($css) && is_array($css))
            @foreach($css as $file)
				<link rel="stylesheet" type="text/css" href="{{ url('/css/'.$file).'?ver='.$cache_version }}" />
            @endforeach
        @endif

        <script src='https://www.google.com/recaptcha/api.js'></script>

		<link rel="apple-touch-icon" sizes="57x57" href="{{ url('fav/apple-icon-57x57.png') }}">
		<link rel="apple-touch-icon" sizes="60x60" href="{{ url('fav/apple-icon-60x60.png') }}">
		<link rel="apple-touch-icon" sizes="72x72" href="{{ url('fav/apple-icon-72x72.png') }}">
		<link rel="apple-touch-icon" sizes="76x76" href="{{ url('fav/apple-icon-76x76.png') }}">
		<link rel="apple-touch-icon" sizes="114x114" href="{{ url('fav/apple-icon-114x114.png') }}">
		<link rel="apple-touch-icon" sizes="120x120" href="{{ url('fav/apple-icon-120x120.png') }}">
		<link rel="apple-touch-icon" sizes="144x144" href="{{ url('fav/apple-icon-144x144.png') }}">
		<link rel="apple-touch-icon" sizes="152x152" href="{{ url('fav/apple-icon-152x152.png') }}">
		<link rel="apple-touch-icon" sizes="180x180" href="{{ url('fav/apple-icon-180x180.png') }}">
		<link rel="icon" type="image/png" sizes="192x192"  href="{{ url('fav/android-icon-192x192.png') }}">
		<link rel="icon" type="image/png" sizes="32x32" href="{{ url('fav/favicon-32x32.png') }}">
		<link rel="icon" type="image/png" sizes="96x96" href="{{ url('fav/favicon-96x96.png') }}">
		<link rel="icon" type="image/png" sizes="16x16" href="{{ url('fav/favicon-16x16.png') }}">
		<meta name="msapplication-TileColor" content="#ffffff">
		<meta name="msapplication-TileImage" content="{{ url('fav/ms-icon-144x144.png') }}">
		<meta name="theme-color" content="#ffffff">

    </head>

    <body class="page-{{ $current_page }} sp-{{ $current_subpage }} {{ !empty($extra_body_class) ? $extra_body_class : '' }}">
    	<div id="site-url" url="{{ empty($_SERVER['REQUEST_URI']) ? getLangUrl('/') : 'https://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] }}"></div>

		<header class="header">
		    <div class="navbar-header">
				<a class="logo" href="https://dentacoin.com/" target="_blank"><img src="{{ url('img-support/logo.svg') }}"></a>
				<div class="header-info">
                    @if(!empty($user))
	                    <div class="flex flex-mobile flex-center">
	                    	<div class="user-and-price">
								<p class="my-name" href="javascript:;">
									{{ $user->name }}
								</p>
							</div>
							@if( $user->platform!='external' )
								<a class="header-avatar" id="header-avatar" href="javascript:;" >
									<img src="{{ $user->thumbnail_url }}" width="50" height="50">
								</a>
							@endif
	                    </div>
                    @else
						<a href="javascript:;" class="button-sign-in open-dentacoin-gateway">
							SIGN IN
						</a>
                    @endif
				</div>
		    </div>
	    </header>

	    <div class="site-content">
			@yield('content')
		</div>

		<footer>
		    <div class="container-footer">
		        <div class="socials flex flex-text-center flex-center">
		        	@if(!empty($socials))
		        		@foreach(json_decode($socials, true) as $social)
		        			<a target="_blank" href="{{ $social['link'] }}">
		                        <img src="{{ $social['media_name'] }}" alt="{{ $social['media_alt'] }}"/>
		                    </a>
		        		@endforeach
	                @endif
                </div>

		        <div class="footer-menu flex flex-mobile flex-center flex-text-center">
		        	<a target="_blank" href="https://dentacoin.com/assets/uploads/dentacoin-company-introduction.pdf">
		        		Company Intro
		        	</a>
                    <div class="has-menu" href="javascript:;">
                        Fact Sheet
                        <div class="sub-menu">
                        	<a target="_blank" href="https://dentacoin.com/assets/uploads/dentacoin-fact-sheet.pdf">English</a>
                        	<a target="_blank" href="https://dentacoin.com/assets/uploads/was-ist-dentacoin.pdf">Deutsch</a>
                        </div>
                    </div> 
		            <a target="_blank" href="https://dentacoin.com/corporate-identity">
		            	Corp. Identity
		            </a>
		            <a target="_blank" href="https://dentacoin.com/corporate-design/one-line-logo">
		            	Corp. Design
		            </a>
		            <a target="_blank" href="https://dentacoin.com/assets/uploads/whitepaper.pdf">
		            	Whitepaper
		            </a>
		            <a target="_blank" href="https://dentacoin.com/team">
		            	Team
		            </a>
		            <a target="_blank" href="https://dentacoin.com/careers">
		            	Careers
		            </a>
		            <a target="_blank" href="https://blog.dentacoin.com/">
		            	Blog
		            </a>
		            <a target="_blank" href="https://dentacoin.com/press-center/page/1">
		            	Press
		            </a>
		        </div>
		    </div>

	        <div class="all-rights tac">
                <div>© 2021 Dentacoin Foundation. All rights reserved.</div>
                <div>
                	<a href="https://dentacoin.com/assets/uploads/dentacoin-foundation.pdf" target="_blank" class="footer-bottom-link">
	                	Verify Dentacoin Foundation
	                </a>
	                <a href="https://dentacoin.com/privacy-policy" target="_blank" class="footer-bottom-link">Privacy Policy</a>
	            </div>
                <div class="contract-title">Contract Address:</div>
                <div>
                	<a href="https://etherscan.io/address/0x08d32b0da63e2C3bcF8019c9c5d849d7a9d791e6#code" target="_blank" class="contract-address">0x08d32b0da63e2C3bcF8019c9c5d849d7a9d791e6</a>
                </div>
	    	</div>
		</footer>

        @if(!empty($csscdn) && is_array($csscdn))
            @foreach($csscdn as $file)
				<link rel="stylesheet" type="text/css" href="{{ $file }}" />
            @endforeach
        @endif

		<script src="{{ url('/js/jquery-3.4.1.min.js') }}"></script>

		@if(empty($user))
			<script src="https://dentacoin.com/assets/libs/dentacoin-login-gateway/js/init.js?ver={{$cache_version}}"></script>
			@if(strpos($_SERVER['HTTP_HOST'], 'dev') !== false) 
				<script type="text/javascript">
					dcnGateway.init({
						'platform' : 'dentacoin',
						'forgotten_password_link' : 'https://account.dentacoin.com/forgotten-password?platform=dentacoin',
						// 'environment' : 'staging',
					});	
				</script>
			@else
				<script type="text/javascript">
					dcnGateway.init({
						'platform' : 'dentacoin',
						'forgotten_password_link' : 'https://account.dentacoin.com/forgotten-password?platform=dentacoin',
					});	
				</script>
			@endif
		@else
			@if($user->platform != 'external')
				<link rel="stylesheet" type="text/css" href="https://dentacoin.com/assets/libs/dentacoin-package/css/style.css?ver={{$cache_version}}">
				<script src="https://dentacoin.com/assets/libs/dentacoin-package/js/init.js?ver={{$cache_version}}"></script>

				<script type="text/javascript">
					if(typeof dcnHub !== 'undefined') {

						var miniHubParams = {
							'notifications_counter' : true,
							'element_id_to_bind' : 'header-avatar',
							'platform' : 'dentacoin',
							'log_out_link' : 'dentacoin.com/user-logout'
						};

						miniHubParams.type_hub = '{{ $user->is_dentist ? 'mini-hub-dentists' : 'mini-hub-patients' }}';
						dcnHub.initMiniHub(miniHubParams);
					}
				</script>
			@endif
		@endif

		@if(empty($user) && empty($_COOKIE['performance_cookies']) && empty($_COOKIE['marketing_cookies']) && empty($_COOKIE['strictly_necessary_policy']) && empty($_COOKIE['functionality_cookies']))
			<script src="https://dentacoin.com/assets/libs/dentacoin-package/js/init.js?ver={{$cache_version}}"></script>
			<link rel="stylesheet" type="text/css" href="https://dentacoin.com/assets/libs/dentacoin-package/css/style-cookie.css?ver={{$cache_version}}">

			<script type="text/javascript">
				if (typeof dcnCookie !== 'undefined') {
					dcnCookie.init({});
				}
			</script>
		@endif

		@if(!empty( $markLogout )) 
			@include('sso-logout')
		@endif
		@if(!empty( $markLogin )) 
			@include('sso')
		@endif
		
		<script src="{{ url('/js/cookie.min.js') }}"></script>
		<script src="{{ url('/js-support/main.js').'?ver='.$cache_version }}"></script>
		
        @if(!empty($jscdn) && is_array($jscdn))
            @foreach($jscdn as $file)
                <script src="{{ $file }}"></script>
            @endforeach
        @endif
		
        @if(!empty($js) && is_array($js))
            @foreach($js as $file)
                <script src="{{ url('/js-support/'.$file).'?ver='.$cache_version }}"></script>
            @endforeach
        @endif
        
        <script type="text/javascript">
        	var lang = '{{ App::getLocale() }}';
        	var home_url = '{{ getLangUrl("/") }}';
        	var user_id = {{ !empty($user) ? $user->id : 'null' }};
        </script>
    </body>
</html>