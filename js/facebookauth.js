window.fbAsyncInit = function() {
	// init the FB JS SDK
	FB.init({
		appId      : facebook_auth.appID,
		status     : true,
		cookie     : true,
		xfbml      : true
	});
};

// Load the SDK asynchronously
(function(d, s, id){
	var js, fjs = d.getElementsByTagName(s)[0];
	if (d.getElementById(id)) {return;}
	js = d.createElement(s); js.id = id;
	js.src = "//connect.facebook.net/en_US/all.js";
	fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

(function($){
	$('#facebook_auth_btn').bind('click', function(event){
		// loading facebook immediately
		var button = $('#facebook_auth_btn');
		event.preventDefault();
		if ( FB ){
			FB.login(function(response) {
				if (response.authResponse) {

					access_token = response.authResponse.accessToken; //get access token
					user_id      = response.authResponse.userID; //get FB UID

					FB.api('/me', function(response) {
						user_email = response.email; //get user email
						// you can store this data into your database

						var params = {
							url 	: ae_globals.ajaxURL,
							type 	: 'post',
							data 	: {
								action: 'et_facebook_auth',
								content: response
							},
							beforeSend: function(){
							},
							success: function(resp){
								if ( resp.success && typeof resp.data.redirect_url != 'undefined' ){
									window.location = resp.data.redirect_url;
								}
								else if ( resp.success && typeof resp.data.user != 'undefined' ){
									// assign current user
									var model = new QAEngine.Models.User(resp.data.user);
									QAEngine.App.currentUser = model;

									// trigger events
									var view 	= QAEngine.App.authModal;
									if(typeof view != 'undefined'){
										view.trigger('response:login', resp);
										AE.pubsub.trigger('ae:response:login', model);
										AE.pubsub.trigger('ae:notification', {
											msg: resp.msg,
											notice_type: 'success',
										});

										view.$el.on('hidden.bs.modal', function(){
											AE.pubsub.trigger('ae:auth:afterLogin', model);
											view.trigger('afterLogin', model);
											if ( view.options.enableRefresh == true){
												window.location.reload(true);
											} else {
											}
										});

										view.closeModal();
									}
									else{
										AE.pubsub.trigger('ae:notification', {
											msg: resp.msg,
											notice_type: 'success',
										});
										window.location.reload(true);
									}
								} else if ( resp.msg ) {
									AE.pubsub.trigger('ae:notification', {
									msg: msg,
									notice_type: 'error',
								});
								}
							},
							complete: function(){
							}
						}
						jQuery.ajax(params);
					});
				} else {
					//user hit cancel button
					AE.pubsub.trigger('ae:notification', {
						msg: qa_front.texts.cancel_auth,
						notice_type: 'error',
					});
				}
			}, {
				scope: 'email,user_about_me'
			});
		}
	});
})(jQuery);