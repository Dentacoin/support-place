@extends('support')

@section('content')

	<div class="contact-container">
		<a href="{{ getLangUrl('/') }}" class="back">< Back</a>
		<h2>Support form</h2>

		<form class="contact-form" action="{{ getLangUrl('contact') }}" method="POST" enctype="multipart/form-data">

			<select name="issue" class="input alert-after" id="issue">
				<option value="" disabled selected>Type of issue:</option>
				@foreach(config('support.issues') as $k => $issue)
					<option value="{{ $k }}">{{ $issue }}</option>
				@endforeach
			</select>

			<div class="bottom-form" {!! empty($user) ? 'style="display:none;"' : '' !!}>
				<select name="platform" class="input alert-after">
					<option value="" disabled selected>Choose platform:</option>
					@foreach(config('support.platforms') as $k => $platform)
						<option value="{{ $k }}">{{ $platform }}</option>
					@endforeach
				</select>

				@if(empty($user))
					<input type="email" name="email" class="input alert-after" placeholder="Your email" />
				@endif

				<textarea class="input alert-after" name="description" id="description" placeholder="Describe your issue"></textarea>
				<div id="error-description" class="alert-error" style="display: none;">Currently, we provide support in English language only.</div>

				<div class="alert-after">
					
					<label for="attach-file" class="attach-file">
						<span>Attach screenshot or video</span>
						<input type="file" name="file" id="attach-file"/>
					</label>
					<p class="file-requirements" accept="png|jpg|jpeg|mp4|wmv|avi|mov|m3u8|ts">File types allowed: .png,.jpg,.mp4,.wmv,.avi,.mov up to 10MB</p>
				</div>

				<div id="error-file" class="alert-error" style="display: none;">The file you selected is large. Max size: 10MB.</div>

				<div class="g-recaptcha" id="g-recaptcha" data-callback="sendReCaptcha" style="display: inline-block;" data-sitekey="6LddiYEeAAAAAJR7ynIBy4aDGm6tFzWs3rCw3MTK"></div>
				<div class="alert-error" id="captcha-error" style="display: none;">
					<!-- Invalid attempt. Please try again. -->
					Please, check the checkbox for verification.
				</div>

				<div class="tac">
					<button type="submit" class="button">SUBMIT</button>
				</div>
			</div>

			<div class="alert-error" id="not-logged-error" style="display: none;">You must log in to continue. <a href="javascript:;" class="open-dentacoin-gateway">Log in here</a>.</div>
			<div class="alert-error" id="query-error" style="display: none;margin-top: 20px">Something went wrong, please try again later.</div>
			<div class="alert-error" id="error-message" style="display: none;margin-top: 20px"></div>

		</form>

		<div class="contact-success">
			<div class="alert alert-success">
				Your inquiry was sucessfully submitted. Our support team will get back to you by email once we reach your case. It may take 7 business days. Note that support  issues are not handled over social media. Meanwhile, you can find answers to most frequently asked questions here: <a href="https://support.dentacoin.com/">https://support.dentacoin.com/</a>
			</div>
		</div>
	</div>

@endsection