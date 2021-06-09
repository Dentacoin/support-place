@if($user)
	@php($slug = encryptCode($user->id))
	@php($type = encryptCode($user->is_dentist ? 'dentist' : 'patient'))
	@php($token = session('user_token'))
<!-- 
	custom-cookie?slug={{ urlencode($slug) }}&type={{ urlencode($type) }}&token={{ urlencode($token) }} -->
	<div class="sso" style="display: none;">
		@foreach( config('platforms') as $k => $platform )
			@if( !empty($platform['url']) && ( mb_strpos(request()->getHttpHost(), $platform['url'])===false || $platform['url']=='dentacoin.com' )  )
		 		<img src="//{{ $platform['url'] }}/custom-cookie?slug={{ urlencode($slug) }}&type={{ urlencode($type) }}&token={{ urlencode($token) }}" class="hide"/>
		 	@endif
		@endforeach
	</div>
@endif