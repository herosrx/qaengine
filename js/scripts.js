jQuery(document).ready(function($) {

	if( $("#answers_filter").length > 0 ){
		$('#answers_filter').waypoint('sticky', {
			stuckClass: 'stuck-sticky',
			wrapper: '<div class="sticky-wrapper" />'
		});	
	}

	if( $("#question_filter").length > 0 ){
		$('#question_filter').waypoint(function(direction) {
			//console.log('aaa');
			if(direction == "down")
				$('#q_filter_waypoints').fadeIn();
			else 
				$('#q_filter_waypoints').fadeOut('fast');

		}, { offset: 0 });	
	}

	$("input.submit-input").on({
		keyUp: function() {
			hasChange = true;
		},
		change: function() {
			hasChange = true;
		}
	});

	$('form').submit(function() {
		$(window).unbind("beforeunload");
	});

	$(window).bind('beforeunload', function() {
		if (typeof hasChange == "undefined") {
			hasChange = false;
		}
		if (hasChange) {
			return qa_front.texts.close_tab;
		}
	});
	
    var ua = window.navigator.userAgent;
    var msie = ua.indexOf("MSIE ");

    if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)){
		$(".adject").textrotator({
			animation: "dissolve", 
			separator: "|", 
			speed: 2000 
		});
	} else {
		$(".adject").textrotator({
			animation: "flipUp", 
			separator: "|", 
			speed: 2000 
		});
	}

	$(".to_register").click(function(event) {
		//event.preventDefault();
		$(".group-btn-intro").find('.to_register').removeClass('active');
		$(this).addClass('active');

		if( $(this).attr('href') == "#tologin" )
			$(".sign-in-social").css('top', "130px");
		else
			$(".sign-in-social").css('top', "160px");
	});

	$("a.your-remember").click(function(event) {
		event.preventDefault();
		if (!$(this).hasClass('clicked')) {
			$(this).addClass('clicked');
			$("input#remember").val(1);
		} else {
			$(this).removeClass('clicked');
			$("input#remember").val(0);
		}
	});

	$("#header_search input").focus(function(event) {
		$(this).css('width', '400px');
	});

	$("#header_search input").blur(function(event) {
		$(this).css('width', '350px');
	});

	$(".link_sign_up").click(function(event) {
		event.preventDefault();
		$('#signin_form').fadeOut("slow", function() {
			$(this).css({
				'z-index': 1
			});
			$('.modal-title-sign-in').empty().text(qa_front.texts.sign_up);
			$('#signup_form').fadeIn(500).css({
				'z-index': 2
			});
		});
	});

	$(".link_sign_in").click(function(event) {
		event.preventDefault();
		$('#signup_form').fadeOut("slow", function() {
			$(this).css({
				'z-index': 1
			});
			$('.modal-title-sign-in').empty().text(qa_front.texts.sign_in);
			$('#signin_form').fadeIn(500).css({
				'z-index': 2
			});
		});
	});

	$(".link_forgot_pass").click(function(event) {
		event.preventDefault();
		$('#signin_form').fadeOut("slow", function() {
			$(this).css({
				'z-index': 1
			});
			$('.modal-title-sign-in').empty().text(qa_front.texts.forgotpass);
			$('#forgotpass_form').fadeIn(500).css({
				'z-index': 2
			});
		});
	});

	$(".link_change_password").click(function(event) {
		event.preventDefault();
		$(this).fadeOut("slow", function() {
			$('.link_change_profile').fadeIn(500);
		});
		$('.edit_profile_form').fadeOut("slow", function() {
			$(this).css({
				'z-index': 1
			});
			$('.edit_password_form').fadeIn(500).css({
				'z-index': 2
			});
		});
	});
	$(".link_change_profile").click(function(event) {
		event.preventDefault();
		$(this).fadeOut("slow", function() {
			$('.link_change_password').fadeIn(500);
		});
		$('.edit_password_form').fadeOut("slow", function() {
			$(this).css({
				'z-index': 1
			});
			$('.edit_profile_form').fadeIn(500).css({
				'z-index': 2
			});
		});
	});
	if( $('#wp-link-wrap').length > 0 ){
		$('#wp-link-wrap').addClass('modal fade in');
	} 
	// if ($('#wp-link-wrap input#link-target-checkbox').length > 0)
	// 	$('#wp-link-wrap input#link-target-checkbox').prop('checked', true);

	// PUSH MENU
	var menuLeft 	  = document.getElementById('cbp-spmenu-s1'),
		menuRight     = document.getElementById('cbp-spmenu-s2'),
		showLeftPush  = document.getElementById('showLeftPush'),
		showRightPush = document.getElementById('showRightPush'),
		body          = document.body;
		
	if( $('#showRightPush').length > 0 )
		showLeftPush.onclick = function() {
			$('#showRightPush').removeClass('active');
			$('.cbp-spmenu-right').removeClass('cbp-spmenu-open');
			$('body').removeClass('cbp-spmenu-push-toleft');
			classie.toggle(this, 'active');
			classie.toggle(body, 'cbp-spmenu-push-toright');
			classie.toggle(menuLeft, 'cbp-spmenu-open');
		};
	if( $('#showRightPush').length > 0 )
		showRightPush.onclick = function() {
			$('#showLeftPush').removeClass('active');
			$('.cbp-spmenu-left').removeClass('cbp-spmenu-open');
			$('body').removeClass('cbp-spmenu-push-toright');
			classie.toggle(this, 'active');
			classie.toggle(body, 'cbp-spmenu-push-toleft');
			classie.toggle(menuRight, 'cbp-spmenu-open');
		};
	
	// INTRO PAGE
	var window_height = $(window).height();
	$('.intro-page-wrapper').height(window_height);

	// ================== HEART BEAT ================== //
	function send_popup( title, text, popup_class, delay ) {
		
		// Initialize parameters
		title = title !== '' ? '<span class="title">' + title + '</span>' : '';
		text = text !== '' ? text : '';
		popup_class = popup_class !== '' ? popup_class : 'update';
		delay = typeof delay === 'number' ? delay : 10000;
		
		var object = $('<div/>', {
		    class: 'notification ' + popup_class,
		    html: title + text + '<span class="close"><i class="fa fa-times"></i></span>'
		});
		
		$('#popup_container').prepend(object);
		
		$(object).hide().fadeIn(500);
		//$('html, body').animate({ scrollTop: 60000 }, 'slow'); 
		
		setTimeout(function() {
			
			$(object).fadeOut(500);	

		}, delay);
	
	}
	
	$('<div/>', { id: 'popup_container' } ).appendTo('body');
	$('body').on('click', 'span.close', function () { $(this).parent().fadeOut(200); });	

	var check;
	
    $(document).on( 'heartbeat-tick', function( e, data ) {
        
		//console.log(data);
        
        if ( !data['message'] )
        	return;

		$.each( data['message'], function( index, notification ) {
			if ( index != check ){
				send_popup( notification['title'], notification['content'], notification['type'] );
			}
			check = index;
		});
        
    });
	// ================== HEART BEAT ================== //
	var config = {
      '.chosen-select'           : {},
    }
    for (var selector in config) {
      $(selector).chosen(config[selector]);
    }
    // ================== CLEAN URL ================== //
    var url = String(document.location.href);
    if( url.indexOf("#") > -1 ){
    	var new_url = url.split('#');
    	document.location.href = new_url[0];
    }
});