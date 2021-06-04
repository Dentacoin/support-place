var dentacoin_down = false;
var recaptchaCode = null;
var sendReCaptcha;
var sendValidation;

jQuery(document).ready(function($){

    $.ajax( {
		url: 'https://dentacoin.com',
		type: 'GET',
		success: function( data ) {
			dentacoin_down = false;

			
		},
		error: function(data) {
			console.log(data);
		    dentacoin_down = true;
		},
		timeout: 5000
	});

    $.ajax( {
		url: 'https://dev-api.dentacoin.com/api/enums/',
		type: 'GET',
		success: function( data ) {
			if(data) {
				dentacoin_down = false;
			} else {
				dentacoin_down = true;
			}
		},
		error: function(data) {
			console.log(data);
		    dentacoin_down = true;
		},
		timeout: 5000
	});

	$('.input').focus( function() {
		$(this).removeClass('has-error');
	});

    if(!dentacoin_down) {
    	
	    $(document).on('dentistAuthSuccessResponse', async function ( event) {
	    	if(event.response_data.trp_ban) {
	    		window.location.href = $('#site-url').attr('url')+lang+'/banned/';
	    	} else {
	    		window.location.href = $('#site-url').attr('url');
	    	}
	    });
	    $(document).on('patientAuthSuccessResponse', async function ( event) {
	    	if(event.response_data.trp_ban) {
	    		window.location.href = $('#site-url').attr('url')+lang+'/banned/';
	    	} else {

	    		var attr = $('#site-url').attr('open-popup');

				if (typeof attr !== typeof undefined && attr !== false && attr == 'invite-dentist') {
				    window.location.href = $('#site-url').attr('url')+'?popup=invite-new-dentist-popup';
				} else {
					window.location.href = $('#site-url').attr('url');
				}
	    	}
	    });
    }

    $('.categories-wrapper .category').click( function(e) {
    	$('.categories-wrapper .category').removeClass('active');
    	$(this).addClass('active');
    	$('.category-questions').removeClass('active');
    	$('#cat-'+$(this).attr('cat-id')).addClass('active');
    });

    $('#search').on( 'click keyup', function(e) {
        if( $(this).val().length > 2 ) {
            var query = $(this).val().toLowerCase();
            var shown_questions = 0;

            $('.results .list').html('');
            for(var i in questions) {
                var question = questions[i].question.toLowerCase();

                // var values = query.split(' ');
                // var values_count = values.length;
                // var values_found = 0;

                // console.log(values_count, values);
                // for(var i in values) {
                //     if (question.indexOf(values[i]) >= 0) {
                //         values_found++;
                //     }
                // }

                // if(values_found == values_count) {
                //     if(shown_questions <= 12) {
                //         $('.results .list').append('\
                //             <a href="'+question_url+questions[i].slug+'">'+questions[i].question+'</a>\
                //         ');
                //     }
                //     shown_questions++;
                // }

                if (question.indexOf(query) >= 0) {
                    if(shown_questions <= 12) {
                        $('.results .list').append('\
                            <a href="'+question_url+questions[i].slug+'">'+questions[i].question+'</a>\
                        ');
                    }
                    shown_questions++;
                }
            }

            if(!shown_questions) {
                $('.results .list').append('<p>No results.</p>');
            }
            $('.results').show();
        } else {
            $('.results').hide();
        }
    });

    $('body').click( function(e) {
        if (!$(e.target).closest('.search-bar').length) {
            $('.results').hide();
        }
    });

    if ($('.newsletter-register').length) {
        basic.initCustomCheckboxes('.newsletter-register');

        $('.newsletter-register form').on('submit', function (event) {
            event.preventDefault();
            var this_form_native = this;
            var this_form = $(this_form_native);

            var error = false;
            this_form.find('.error-handle').remove();

            if (!basic.validateEmail(this_form.find('input[type="email"]').val().trim())) {
                error = true;
                customErrorHandle(this_form.find('input[type="email"]').closest('.newsletter-field'), this_form.find('input[type="email"]').closest('.newsletter-field').attr('data-valid-message'));
            }

            if (!this_form.find('#newsletter-privacy-policy').is(':checked')) {
                error = true;
                customErrorHandle(this_form.find('#newsletter-privacy-policy').closest('.newsletter-field'), this_form.find('#newsletter-privacy-policy').closest('.newsletter-field').attr('data-valid-message'));
            }

            if (!error) {
                // projectData.events.fireGoogleAnalyticsEvent('Subscribe', 'Subscribe', 'Newsletter');
                fbq('track', 'Newsletter');

                this_form_native.submit();

                $('.newsletter-register form .custom-checkbox').html('');
                $('.newsletter-register form #newsletter-privacy-policy').prop('checked', false);
                this_form.find('input[type="email"]').val('');
                $('.newsletter-register .form-container').append('<div class="success-handle">Thank you for signing up.</div>');
            }
        });
    }

    $('.custom-checkbox-input').change( function() {
        if($(this).is(":checked")) {
            $(this).closest('div').find('.custom-checkbox').html('✓');
        } else {
            $(this).closest('div').find('.custom-checkbox').html('');
        }
    });

});

var getUrlParameter = function(sParam) {
    var sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
};

var basic = {
    cookies: {
        set: function(name, value) {
            if(name == undefined){
                name = "cookieLaw";
            }
            if(value == undefined){
                value = 1;
            }
            var d = new Date();
            d.setTime(d.getTime() + (100*24*60*60*1000));
            var expires = "expires="+d.toUTCString();
            document.cookie = name + "=" + value + "; " + expires + ";domain=.dentacoin.com;path=/;secure";
            if(name == "cookieLaw"){
                $(".cookies_popup").slideUp();
            }
        },
        get: function(name) {

            if(name == undefined){
                var name = "cookieLaw";
            }
            name = name + "=";
            var ca = document.cookie.split(';');
            for(var i=0; i<ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0)==' ') c = c.substring(1);
                if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
            }

            return "";
        }
    },
    validateEmail: function(email)   {
        return /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(email);
    },
    initCustomCheckboxes: function(parent, type) {
        if (typeof(parent) == undefined) {
            parent = '';
        } else {
            parent = parent + ' ';
        }

        if (type == undefined) {
            type = 'prepend';
        }

        for (var i = 0, len = jQuery(parent + '.custom-checkbox-style').length; i < len; i+=1) {
            if (!jQuery(parent + '.custom-checkbox-style').eq(i).hasClass('already-custom-style')) {
                if (jQuery(parent + '.custom-checkbox-style').eq(i).find('input[type="checkbox"]').is(':checked')) {
                    if (type == 'prepend') {
                        jQuery(parent + '.custom-checkbox-style').eq(i).prepend('<label for="'+jQuery(parent + '.custom-checkbox-style').eq(i).find('input[type="checkbox"]').attr('id')+'" class="custom-checkbox">âœ“</label>');
                    } else if (type == 'append') {
                        jQuery(parent + '.custom-checkbox-style').eq(i).append('<label for="'+jQuery(parent + '.custom-checkbox-style').eq(i).find('input[type="checkbox"]').attr('id')+'" class="custom-checkbox">âœ“</label>');
                    }
                } else {
                    jQuery(parent + '.custom-checkbox-style').eq(i).prepend('<label for="'+jQuery(parent + '.custom-checkbox-style').eq(i).find('input[type="checkbox"]').attr('id')+'" class="custom-checkbox"></label>');
                }
                jQuery(parent + '.custom-checkbox-style').eq(i).addClass('already-custom-style');
            }
        }

        jQuery(parent + '.custom-checkbox-style .custom-checkbox-input').unbind('change').on('change', function() {
            if (!jQuery(this).closest('.custom-checkbox-style').hasClass('predefined')) {
                if (jQuery(this).is(':checked')) {
                    jQuery(this).closest(parent + '.custom-checkbox-style').find('.custom-checkbox').html('âœ“');
                } else {
                    jQuery(this).closest(parent + '.custom-checkbox-style').find('.custom-checkbox').html('');
                }

                if (jQuery(this).attr('data-radio-group') != undefined) {
                    for (var i = 0, len = jQuery('[data-radio-group="'+jQuery(this).attr('data-radio-group')+'"]').length; i < len; i+=1) {
                        if (!jQuery(this).is(jQuery('[data-radio-group="'+jQuery(this).attr('data-radio-group')+'"]').eq(i))) {
                            jQuery('[data-radio-group="'+jQuery(this).attr('data-radio-group')+'"]').eq(i).prop('checked', false);
                            jQuery('[data-radio-group="'+jQuery(this).attr('data-radio-group')+'"]').eq(i).closest(parent + '.custom-checkbox-style').find('.custom-checkbox').html('');
                        }
                    }
                }
            }
        });
    }
};

//INIT LOGIC FOR ALL STEPS
function customErrorHandle(el, string) {
    el.append('<div class="error-handle">' + string + '</div>');
}