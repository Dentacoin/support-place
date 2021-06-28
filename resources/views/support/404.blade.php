@extends('support')

@section('content')

	<div class="container tac">
		<img src="{{ url('img-support/not-found.png') }}">
		<h1>Oops! We couldn't find this page.</h1>
		<a href="{{ getLangUrl('/') }}">Back to help center</a>
	</div>

@endsection