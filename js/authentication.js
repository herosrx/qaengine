(function($){
$(document).ready(function(){
	var view = new QAEngine.Views.SocialAuth();
});

QAEngine.Views.SocialAuth = Backbone.View.extend({
	el: 'body.page-template-page-social-connect-php',
	events: {
		'submit #form_auth' 	: 'authenticate',
		'submit #form_username' : 'confirm_username'
	},
	initialize: function(){
	this.blockUi = new AE.Views.BlockUi();
	},

	authenticate: function(event){
		event.preventDefault();
		var form = $(event.currentTarget);
		var view = this;

		var params = {
			url: 	ae_globals.ajaxURL,
			type: 	'post',
			data: {
				action: ae_auth.action_auth,
				content: form.serializeObject()
			},
			beforeSend: function(){
				//submit
				  var button = form.find('input[type=submit]')
				  view.blockUi.block(button);
			}, 
			success: function(resp){
				if ( resp.success ){
					if ( resp.data.status == 'wait' ){
						view.$('.social-auth-step1').fadeOut('fast', function(){
							view.$('.social-auth-step2').fadeIn();	
						});
					} else if ( resp.data.status == 'linked' ){
						//window.location = ae_globals.homeURL;
						window.location.reload();
					}
				}
				else{
					msg = 'ERROR!';
					if(resp != 0){
						msg = resp.msg;
					}
					AE.pubsub.trigger('ae:notification', {
						msg: msg,
						notice_type: 'error',
					});				
				}
			}, 
			complete: function(){
				view.blockUi.unblock();
			}
		}
		$.ajax(params);
	},
	
	confirm_username: function(event){
		event.preventDefault();
		var form = $(event.currentTarget);
		var view = this;

		var params = {
			url: 	ae_globals.ajaxURL,
			type: 	'post',
			data: {
				action: ae_auth.action_confirm,
				content: form.serializeObject()
			},
			beforeSend: function(){
				//form.find('input[type=submit]').loader('load');
				var button = form.find('input[type=submit]');
				view.blockUi.block(button);
			}, 
			success: function(resp){
				//console.log(resp);
				if ( resp.success == true ){
					window.location = ae_globals.homeURL;
				} else {
					alert(resp.msg);
				}
			}, 
			complete: function(){
				//form.find('input[type=submit]').loader('unload');
				view.blockUi.unblock();
			}
		}
		$.ajax(params);
	}
})
})(jQuery);