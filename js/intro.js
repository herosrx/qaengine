(function (Views, Models, $, Backbone) {

	Views.Intro = Backbone.View.extend({
		el : 'body',

		events : {
			'submit form#sign_in' : 'doLogin',
			'submit form#sign_up' : 'doRegister',
			'click a.your-fogot-pass' : 'showForgotPassForm'
		},

		initialize : function(){
			this.blockUi	=	new AE.Views.BlockUi();
			this.user = new Models.User();
		},
		showForgotPassForm: function(event){
			event.preventDefault();
			if(typeof this.authModal === "undefined" ){
				this.authModal 	= new Views.AuthModal({
					el : $('#login_register')
				});
			}
			$("form#signin_form").hide();
			$('.modal-title-sign-in').empty().text( qa_front.texts.forgotpass );
			$("form#signup_form").hide();
			$("form#forgotpass_form").show();

			this.authModal.openModal();
		},
		doLogin: function(event){
			event.preventDefault();

			this.login_validator = 	 $("form#sign_in").validate({
				rules	: {
					username		: "required",
					password		: "required",
				},
				messages: {
					username	: qa_front.form_auth.error_msg,
					password 	: qa_front.form_auth.error_msg,
				}
			});

			var form 	 = $(event.currentTarget),
				username = form.find('input#username').val(),
				password = form.find('input#password').val(),
				remember = form.find('input#remember').val(),
				button   = form.find('input.btn-submit'),
				view 	 = this;

			if(this.login_validator.form()){
				this.user.login(username, password, remember, {
					beforeSend:function(){
						view.blockUi.block(button);
					},
					success : function (user, status, jqXHR) {
						view.blockUi.unblock();
						if(status.success){
							//bootbox.alert(status.msg);
							AE.pubsub.trigger('ae:notification', {
								msg: status.msg,
								notice_type: 'success',
							});
							window.location.href = status.redirect;
						} else {
							//bootbox.alert(status.msg);
							AE.pubsub.trigger('ae:notification', {
								msg: status.msg,
								notice_type: 'error',
							});
						}
					}
				});
			}
		},
		doRegister: function(event){
			event.preventDefault();

			this.register_validator = 	 $("form#sign_up").validate({
				rules	: {
					username		: "required",
					password		: "required",
					email			: {
						required: true,
						email: true
					},
					re_password 	: {
						required: true,
						equalTo: "#password1"
					}
				},
				messages: {
					username : qa_front.form_auth.error_msg,
					password : qa_front.form_auth.error_msg,
					email 	 : {
						required : qa_front.form_auth.error_msg,
						email : qa_front.form_auth.error_email,
					},
					re_password: {
						required: qa_front.form_auth.error_msg,
						equalTo: qa_front.form_auth.error_repass,
					}
				}
			});

			var form 	 = $(event.currentTarget),
				username = form.find('input#username').val(),
				email	 = form.find('input#email').val(),
				button   = form.find('input.btn-submit'),
				password = form.find('input#password1').val(),
				data     = form.serializeObject(),
				view 	 = this;

			if(this.register_validator.form()){
				this.user.register(data, {
					beforeSend:function(){
						view.blockUi.block(button);
					},
					success : function (user, status, jqXHR) {
						view.blockUi.unblock();
						if(status.success){
							AE.pubsub.trigger('ae:notification', {
								msg: status.msg,
								notice_type: 'success',
							});
							window.location.href = status.redirect;
						} else {
							AE.pubsub.trigger('ae:notification', {
								msg: status.msg,
								notice_type: 'error',
							});
						}
					}
				});
			}

		}
	});

})(QAEngine.Views, QAEngine.Models, jQuery, Backbone);