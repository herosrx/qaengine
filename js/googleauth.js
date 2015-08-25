(function() {
    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
    po.src = 'https://apis.google.com/js/client:plusone.js?onload=gplus_render';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
})();

/* Executed when the APIs finish loading */
function gplus_render() {

	// Additional params including the callback, the rest of the params will
	// come from the page-level configuration.
	var additionalParams = {
		'callback': signinCallback,
		'clientid': ae_globals.gplus_client_id,
		'cookiepolicy': 'single_host_origin',
		'requestvisibleactions': 'http://schema.org/AddAction',
		'scope': 'https://www.googleapis.com/auth/plus.login'
	};

	// Attach a click listener to a button to trigger the flow.
	if(currentUser.ID == 0){
		var signinButton = document.getElementById('signinButton');
		signinButton.addEventListener('click', function() {
			gapi.auth.signIn(additionalParams); // Will use page level configuration
		});
	}
}

function signinCallback(authResult) {
	//console.log(authResult);
	blockUi = new AE.Views.BlockUi();
	if (authResult['g-oauth-window']) {
		gapi.client.load('oauth2', 'v2', function()
		{
			gapi.client.oauth2.userinfo.get()
				.execute(function(response)
				{
				// Shows user email
				//console.log(response);
				var params = {
					url 	: ae_globals.ajaxURL,
					type 	: 'post',
					data 	: {
						action: 'et_google_auth',
						content: response
					},
					beforeSend: function(){
					},
					success: function(resp){
						console.log(resp);
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

							//window.location = resp.data.redirect_url;
						} else if ( resp.msg ) {
							AE.pubsub.trigger('ae:showNotice', resp.msg , 'error');
						}
					},
					complete: function(){
					}
				}
				jQuery.ajax(params);
			});
		});

	} else {
		//console.log('Sign-in state: ' + authResult['error']);
	}
}