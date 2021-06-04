<div class="topics-wrapper">
	<img class="tag" src="{{ url('img-support/tag.svg') }}">
	<h2 class="tag-title">Help topics</h2>
	<div class="categories-wrapper">
		<div class="flex space-between container">
			@foreach($categories as $cat)
				<a href="javascript:;" class="category {{ $loop->first ? 'active' : '' }}" cat-id="{{ $cat->id }}">{{ $cat->name }}</a>
			@endforeach
		</div>
	</div>
	<div class="categories-questions container">
		@foreach($categories as $cat)
			<div class="category-questions {{ $loop->first ? 'active' : '' }}" id="cat-{{ $cat->id }}">
				@foreach($cat->questions as $q)
					<a href="{{ getLangUrl('question/'.$q->slug) }}" class="question {{ !empty($current_question) && $current_question->id == $q->id ? 'disabled' : '' }}">{{ $q->question }}<img class="arrow" src="{{ url('img-support/arrow-right.svg') }}"/></a>
				@endforeach
			</div>
		@endforeach
	</div>
</div>