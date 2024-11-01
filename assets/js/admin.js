var rox_woo_search_admin = rox_woo_search_admin || {};
;(function($, window, document, opts) {
	"use strict";
	var $document = $(document),
		$window = $(window);
	$(document).ready(function(){
		var $body = $('body');
		$document.on('click', '.rox_colorpickpreview', function(event) {
			$(this).parent().find('.rox_colorpick').focus();
		});
		$body.find('.rox_colorpick').each(function () {
			// svg icon preview
			var $svg = $(this).data('svg_icon');
			if( $svg && $svg.length > 0 ) {
				$svg = $( $svg[0] );
				$svg.find('svg.icon').css('fill', $(this).val());
				$(this).after( $svg );
			}
		});
		/**
		 * Irish Color Picker
		 * @link http://automattic.github.io/Iris/
		 */
		$('.rox_colorpick').iris({
			change: function( event, ui ) {
				var $parent = $( this ).parent();
				$parent.find( '.rox_colorpickpreview' ).css( 'backgroundColor', ui.color.toString());
				$parent.find('svg.icon').css('fill', ui.color.toString() );
			},
			hide: true,
			border: true,
			palettes: ['#56b1f1', '#7f7f7f', '#00ff00', '#ab0', '#de3', '#f0f'],
		}).on( 'click focus', function( event ) {
			event.stopPropagation();
			$( '.iris-picker' ).hide();
			$( this ).closest( 'td' ).find( '.iris-picker' ).show();
			$( this ).data( 'original-value', $( this ).val() );
		}).on( 'change', function() {
			if ( $( this ).is( '.iris-error' ) ) {
				var original_value = $( this ).data( 'original-value' );
				if ( original_value.match( /^\#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/ ) ) {
					$( this ).val( $( this ).data( 'original-value' ) ).change();
				} else {
					$( this ).val('').change();
				}
			}
		});
	});
})( jQuery, window, document, rox_woo_search_admin );