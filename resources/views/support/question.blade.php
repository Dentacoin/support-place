@extends('support')

@section('content')

	<div class="question-wrapper container">
		<a href="{{ getLangUrl('/') }}" class="back">< Back</a>
		<h2>{{ $question->question }}</h2>

		<div class="question-content">
			{!! $question->content !!}
			<br/>
			<p>If you require further assistance, please <a href="{{ getLangUrl('contact') }}">contact our support team</a>.&nbsp;</p>
		</div>
	</div>

	@include('support.parts.topics', [
        'current_question' => $question,
    ])

    <div class="questions-search container">
		@include('support.parts.search')
	</div>

	<script type="text/javascript">
		var questions = JSON.parse('{!! $all_questions !!}');
        var question_url = '{{ getLangUrl("question") }}';
	</script>

@endsection