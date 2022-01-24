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

        var loginUser = function(token) {

            $.ajax({
                type: "POST",
                url: home_url+'login',
                data: {
                    token: token
                },
                dataType: 'json',
                success: function(ret) {
                    window.location.href = $('#site-url').attr('url');
                },
                error: function(ret) {
                    console.log('error');
                }
            });
        }

	    $(document).on('dentistAuthSuccessResponse', async function ( event) {
            loginUser(event.response_data.token);
	    });
	    $(document).on('patientAuthSuccessResponse', async function ( event) {
			loginUser(event.response_data.token);
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

    setTimeout( function() {
        if($('.logout-btn-parent').length) {
            $('.logout-btn-parent a').attr('href', '/user-logout');
        }
    }, 1000);

    var handleHorizontalScrolls = function() {
        var scrollableElement = $('.categories-wrapper .flex');
        var children = scrollableElement.children();
        var total = 0;

        children.each( function() { 
            total += $(this).outerWidth() + parseFloat($(this).css('margin-right')) + parseFloat($(this).css('margin-left'));
        });

        scrollableElement.css('width', total + parseFloat(scrollableElement.css('padding-left')) + parseFloat(scrollableElement.css('padding-right')) + 60);
    }

    if($(window).outerWidth() <= 998) {
        setTimeout(handleHorizontalScrolls , 10);
    }

    if (window.ethereum) {
        async function init() {
            $('#metamask-network').click(async function() {
                var chainId = await ethereum.request({method: 'eth_chainId'});
                if (chainId != '0xa') {
                    const wasAdded = await window.ethereum.request({
                        method: "wallet_addEthereumChain",
                        params: [{
                            chainId: "0xA",
                            rpcUrls: ["https://mainnet.optimism.io"],
                            chainName: "Optimism Mainnet",
                            nativeCurrency: {
                                name: "ETH",
                                symbol: "ETH",
                                decimals: 18
                            },
                            blockExplorerUrls: ["https://optimistic.etherscan.io/"]
                        }]
                    });
    
                    if (wasAdded) {
                        alert('Optimism network added successfully.');
                    } else {
                        alert('Optimism network not added. You did not approve it in your wallet.');
                    }
                } else {
                    alert('You already have Optimism network added to your MetaMask extension.');
                }
            });
            
            $('#metamask-currency').click(async function() {
                var chainId = await ethereum.request({method: 'eth_chainId'});
                if (chainId != '0xa') {
                    alert(' Before adding Dentacoin on Optimism please make sure you\'ve added Optimism network to your list of networks, and the current active network is Optimism.');
                } else {
                    const wasAdded = await ethereum.request({
                        method: 'wallet_watchAsset',
                        params: {
                            type: 'ERC20', // Initially only supports ERC20, but eventually more!
                            options: {
                                address: '0x1da650c3b2daa8aa9ff6f661d4156ce24d08a062', // The address that the token is at.
                                symbol: 'DCN', // A ticker symbol or shorthand, up to 5 chars.
                                decimals: 0, // The number of decimals in the token
                                image: 'https://dentacoin.com/assets/images/logo.svg', // A string url of the token logo
                            },
                        },
                    });
    
                    if (wasAdded) {
                        alert('Dentacoin on Optimism added successfully as a token.');
                    } else {
                        alert('Dentacoin token on Optimism not added. You did not approve it in your wallet.');
                    }
                }
            });
        }
        init();
    } else {
        $('#metamask-network, #metamask-currency').click(function() {
            alert('You don\'t have a MetaMask browser extension. Install it if you wish to proceed.');
        });
    }

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