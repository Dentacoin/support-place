@extends('support')

@section('content')

	<div class="search-wrapper">
		<div class="container">
			<h1>We’re here to help</h1>

			@include('support.parts.search')

			@if(!empty($main_questions))
				<div class="flex row wrap main-questions">
					@foreach($main_questions as $mq)
						<a href="{{ getLangUrl('question/'.$mq->slug) }}" class="main-question">{{ $mq->question }}</a>
					@endforeach
				</div>
			@endif
		</div>
	</div>

	@include('support.parts.topics', [
        'current_question' => null,
    ])

	{{-- <div class="contact-wrapper container">
		<h2>Can’t find an answer to your question?</h2>
		<a href="{{ getLangUrl('contact') }}">Contact support</a>
	</div> --}}

	<script type="text/javascript">
		var questions = JSON.parse('{!! $all_questions !!}');
        var question_url = '{{ getLangUrl("question") }}';
	</script>

@endsection