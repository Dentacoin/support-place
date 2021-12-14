var ajax_is_running = false;

jQuery(document).ready(function($){

    $('#attach-file').change(function() {
    	var size = this.files[0].size;

    	if(size > 10000000) {
    		$('#error-file').css('display', 'block');
    		$(this).val('');
    	} else {
    		$('#error-file').hide();

	    	var fileName = this.files[0].name;
			if(fileName.length > 10) {
				fileName = fileName.slice(-23);
			}
	    	$(this).closest('label').find('span').html(fileName);

		  	// console.log(this.files[0]);
    	}
	});

    $('#issue').change( function() {
    	if($(this).val() != 'login' && !user_id) {
    		console.log('must be logged');
    		$('.bottom-form').hide();
    		$('#not-logged-error').css('display', 'block');
    	} else {
    		$('.bottom-form').show();
    		$('#not-logged-error').hide();
    		console.log('ok logged');
    	}
    });

    $('#description').on('keyup', function() {
        checkForNonLatinCharacters($(this).val());
    });

    $('#description').bind("paste", function(e){
        var pastedData = e.originalEvent.clipboardData.getData('text');
        checkForNonLatinCharacters(pastedData);
    } );

    checkForNonLatinCharacters = function(text) {
        var rforeign = /[^\u0000-\u007f\’]/;

        if (rforeign.test(text)) {
            $('#error-description').css('display', 'block');
        } else {
            $('#error-description').hide();
        }
    }

    $('.contact-form').submit( function(e) {
    	e.preventDefault();

		if(ajax_is_running) {
            return;
        }

        ajax_is_running = true;

        $('.ajax-alert').remove();
        $('#captcha-error').hide();
        $('#query-error').hide();
        $('#error-description').hide();

        var formData = new FormData(this);
        formData.append("_token", $('[name="csrf-token"]').attr('content'));
        var that = $(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            cache: false,
            contentType: false,
            processData: false
        }).done( (function (data) {
            if(data.success) {
                // console.log(data);

                $(this).hide();
                $('.contact-success').show();
                
            } else {
                // console.log(data);
                if(data.messages) {
                    for(var i in data.messages) {
                        $('[name="'+i+'"]').addClass('has-error');
                        $('[name="'+i+'"]').closest('.alert-after').after('<div class="alert alert-warning ajax-alert alert-'+i+'">'+data.messages[i]+'</div>');
                    }
                } else if(data.error_captcha) {
                    $('#captcha-error').css('display', 'block');
                } else if(data.need_login) {
                    $('.bottom-form').hide();
                    $('#not-logged-error').css('display', 'block');
                } else if(data.non_latin) {
                    $('#error-description').css('display', 'block');
                }
            }
            ajax_is_running = false;

        }).bind(this) ).fail(function (data) {
            console.log(data);
            $('#query-error').css('display', 'block');
            ajax_is_running = false;
        });
    });

});
