/**
 * This file adds some LIVE to the Theme Customizer live preview. To leverage
 * this, set your custom settings to 'postMessage' and then add your handling
 * here. Your javascript should grab settings from customizer controls, and 
 * then make any necessary changes to the page using jQuery.
 */
( function( $ ) {
	
	//Update site link color in real time...
	wp.customize( 'link_textcolor', function( value ) {
		value.bind( function( newval ) {
			$('.q-right-content .question-control ul li a.show-comments.active, a.add-comment, a.hide-comment, span.back a, .term-texts a, .widget a,.question-category a,.copyright a,.widget-menus ul li a').css('color', newval );
		} );
	} );

	wp.customize( 'main_action_color', function( value ) {
		value.bind( function( newval ) {
			$('.paginations ul li a:hover, .paginations ul li span.current, .submit-wrapper button, .ask-question, .modal-submit-questions .btn-submit-question, .question-main-content .vote-block span, #upload_images .button-event button').css('background-color', newval );
		} );
	} );

	wp.customize( 'sidebar_color', function( value ) {
		value.bind( function( newval ) {
			$('.right-sidebar').css('background-color', newval );
		} );
	} );

	wp.customize( 'header_color', function( value ) {
		value.bind( function( newval ) {
			$('#header,ul.dropdown-profile').css('background-color', newval );
		} );
	} );	

	wp.customize( 'header_menus_color', function( value ) {
		value.bind( function( newval ) {
			$('.header-menu').css('background-color', newval );
		} );
	} );	
	
} )( jQuery );