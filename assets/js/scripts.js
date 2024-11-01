/**
 * Rox WooCommerce Mega Scripts
 */
var rox_woo_search = rox_woo_search || {};
;(function($, window, document, opts){
    "use strict";
    var ajaxTimer, ajax;

    var get_results = function( url, data, callback, error_cb ){
        clearTimeout(ajaxTimer);
        if (ajax) {
            ajax.abort();
            ajax = false;
        }
        ajaxTimer = setTimeout(function() {
            ajax = $.get( url, data, function(response, status) {
                callback( response, status );
            }).fail(function( jqXHR, status ) {
                if( typeof( error_cb ) === 'function' ) error_cb( jqXHR, status );
            });
        }, 150);
    };
    var rox_wcms = function( searchEl) {
        var $searchEl = $( searchEl ),
	        $container = $searchEl.closest('.rox-search-container'), // DOM Cache
            $form = $container.find('form.rox-search'),
            $resultContainer = $container.find( '.result-contents' ),
            $searchBtn = $container.find( 'a.submit'),
            $clearBtn = $container.find('a.reset'),
	        oldValue = '',
            has_results = false,
            data = {
        			action: opts.rox_action,
                    verb: 'search',
    		        _nonce: opts.rox_csrf
    		},
            process_search_response = function ( response, status ) {
                if( response.success ) {
                    $resultContainer.html(response.data.result);
                } else {
                    $resultContainer.html( '<p class="alert alert-warning">' + opts.rox_error + '</p>' );
                }
            },
            process_ajax_error = function( jqXHR, status ){
                if( status !== 'abort' ) {
                    $resultContainer.html( '<p class="alert alert-warning">' + opts.rox_error + '</p>' );
                }
            },
            remove_icon = function( $el ) {
                if( $el.find('.rox-icon').length > 0 ) $el.find('.rox-icon').remove();
            };
        // Hide if result container is visible
        if( $container.hasClass('open') ) {
            $container.removeClass('open');
        }
        
        $searchEl.on( 'keyup', function( event ) {
            var keyCode = (event.which) ? event.which : event.keyCode,
                ignoreKeys = [9, 13, 16, 17,18, 20, 27, 33, 34, 45, 47, 58, 64, 91, 92, 93, 96, 123, 127, 144],
                value = $(this).val().trim(),
                _data = $.extend( {}, data, { s: value } );
            if ( ignoreKeys.indexOf(keyCode) === -1 && value.length > 0 && value !== oldValue ) {
	            oldValue = value;
                $resultContainer.html(opts.rox_loading);
                if( ! $container.hasClass('open') ) {
                    $container.addClass('open');
                }
                get_results( opts.rox_ajax, _data, process_search_response, process_ajax_error );
            }
        });
        $searchEl.on('focus', function () {
            if( $(this).val().length > 0 ) {
                $container.addClass('open');
            }
        });
        $form.on( 'keypress', function( event ) {
	        var keyCode = (event.which) ? event.which : event.keyCode;
            if ( keyCode === 13 ) {
                event.preventDefault();
                return false;
            }
        }).on( 'keyup', function( event ) {
	        var keyCode = (event.which) ? event.which : event.keyCode;
	        if ( keyCode === 27 ) {
		        if( $container.hasClass('open') ) {
			        $container.removeClass('open');
			        oldValue = '';
		        }
            }
        });
        $searchBtn.click(function (event) {
            event.preventDefault();
			var value = $searchEl.val(),
            	_data = $.extend( {}, data, { s: value } );
            if( value.length > 0 ) {
                $resultContainer.html(opts.rox_loading);
                if( ! $container.hasClass('open') ) {
                    $container.addClass('open');
                }
                get_results( opts.rox_ajax, _data, process_search_response );
            } else {
	            $searchEl.focus();
            }
        });
        $clearBtn.click(function (event) {
            event.preventDefault();
            $container.removeClass('open');
            $searchEl.val('');
            $resultContainer.html('');
            has_results = false;
	        oldValue = '';
	        $searchEl.blur();
        });
	    $container.on('click', '.rox-wcms-item.simple a.rox-wcms-cart', function(event){
		    event.preventDefault();
		    var $cartBtn = $(this),
                cartBtnText = $cartBtn.find('span').not('.rox-icon').text(),
                $item = $cartBtn.closest('.rox-wcms-item'),
			    data = {
				    action: opts.rox_action,
				    verb: 'add-to-cart',
				    _nonce: opts.rox_csrf,
				    product_id: $item.data('product_id'),
			    };
		    if( $cartBtn.hasClass('disabled') ) {
			    return false;
		    }
		    remove_icon( $cartBtn );
		    $cartBtn.addClass('disabled');
		    $cartBtn.prepend( opts.icons.rox_loading );
		    $.post( opts.rox_ajax, data, function( response ) {
			    remove_icon( $cartBtn );
			    if( response.success ) {
				    $item.addClass('in_cart');
				    $cartBtn.prepend( opts.icons.rox_tick );
				    $cartBtn.find('span').not('.rox-icon').text(response.data.message);
			    } else {
				    alert( response.data.message );
				    $cartBtn.removeClass('disabled');
			    }
		    } ).fail(function( jqXHR, status ) {
			    process_ajax_error( jqXHR, status );
		    }).always(function () {
                setTimeout(function () {
	                remove_icon( $cartBtn );
	                $cartBtn.find('span').not('.rox-icon').text(cartBtnText);
	                $cartBtn.removeClass('disabled');
                }, 2500 );
		    });
	    });
	    $(document).on('click', function( event ) {
	    	var $target = $( event.target );
	    	if( $target.parents('.rox-search-container').length == 0 ) {
			    if( $container.hasClass('open') ) {
				    $container.removeClass('open');
				    if( $searchEl.val().trim() == '' ) oldValue = '';
			    }
		    }
	    });
	    $container.click(function(event){
		    // event.stopImmediatePropagation();
		    // event.stopPropagation();
	    });
    };
    $(document).ready(function(){
	    var $searchForm = $('.rox_wcm_search'); // DOM Cache
	    $searchForm.each( function () {
		    rox_wcms( this );
	    } );
    });
})(jQuery, window, document, rox_woo_search);