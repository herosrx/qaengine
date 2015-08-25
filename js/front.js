(function(Views, Models, $, Backbone) {

	Views.UploadImagesModal = AE.Views.Modal_Box.extend({
		events: {
			'click button.close': 'resetUploader',
			'click a.btn-cancel': 'resetUploader',
			'click button#insert': 'startUploadImg'
		},
		initialize: function() {
			AE.Views.Modal_Box.prototype.initialize.call();
			this.blockUi = new AE.Views.BlockUi();

			var $images_upload = $('#images_upload_container'),
				view = this;

			this.uploader = new AE.Views.File_Uploader({
				el: $images_upload,
				uploaderID: 'images_upload',
				multi_selection: false,
				unique_names: false,
				upload_later: true,
				filters: [{
					title: "Image Files",
					extensions: 'gif,jpg,png'
				}, ],
				multipart_params: {
					_ajax_nonce: $images_upload.find('.et_ajaxnonce').attr('id'),
					action: 'et_upload_images'
				},

				cbAdded: function(up, files) {
					var i;

					if (up.files.length > 1) {
						while (up.files.length > 1) {
							up.removeFile(up.files[0]);
						}
					}

					for (i = 0; i < up.files.length; i++) {
						$("span.filename").text(up.files[i].name);
					}
				},

				cbUploaded: function(up, file, res) {
					if (res.success) {
						tinymce.activeEditor.execCommand('mceInsertContent', false, "[img]" + res.data + "[/img]");
						view.closeModal();
						$("span.filename").text(qa_front.texts.no_file_choose);
						up.splice();
						up.refresh();
						up.destroy();
					} else {
						AE.pubsub.trigger('ae:notification', {
							msg: res.msg,
							notice_type: 'error',
						});
						view.closeModal();
						$("span.filename").text(qa_front.texts.no_file_choose);
						up.splice();
						up.refresh();
						up.destroy();
						$("button#insert").prop('disabled', false);
					}
				},
				beforeSend: function() {
					$("button#insert").prop('disabled', true);
				},
				success: function() {
					$("button#insert").prop('disabled', false);
				}
			});
		},
		resetUploader: function() {
			this.uploader.controller.splice();
			this.uploader.controller.refresh();
			this.uploader.controller.destroy();
			$("span.filename").text(qa_front.texts.no_file_choose);
		},
		startUploadImg: function(event) {
			event.preventDefault();

			var input = $("input#external_link"),
				view = this;

			if (currentUser.ID === 0 && input.val() == "")
				return false;

			if (this.uploader.controller.files.length > 0) {

				hasUploadError = false;
				this.uploader.controller.start();

			} else if (input.val() != "") {

				tinymce.activeEditor.execCommand('mceInsertContent', false, "[img]" + input.val() + "[/img]");
				view.closeModal();

				this.uploader.controller.splice();
				this.uploader.controller.refresh();
				this.uploader.controller.destroy();

				$("input#external_link").val("");
				$("span.filename").text(qa_front.texts.no_file_choose);
			}
		}
	});

	Views.AuthModal = AE.Views.Modal_Box.extend({
		options: {
				enableRefresh : true
		},
		events: {
			'submit form#signin_form': 'doLogin',
			'submit form#signup_form': 'doRegister',
			'submit form#forgotpass_form': 'doSendPassword',
			'click  button.close': 'resetAuthForm'
		},
		initialize: function() {
			AE.Views.Modal_Box.prototype.initialize.call();
			this.blockUi = new AE.Views.BlockUi();
			this.user    = new Models.User();
			//add new rule :)
			$.validator.addMethod("username", function(value, element) {
			    return /^[a-zA-Z0-9_]+$/i.test(value);
			}, "Invalid username!");
		},
		resetAuthForm: function(event) {
			event.preventDefault();
			$("form#signin_form").fadeIn();
			$('.modal-title-sign-in').empty().text(qa_front.texts.sign_in);
			$("form#signup_form").hide();
			$("form#forgotpass_form").hide();
		},
		doLogin: function(event) {
			event.preventDefault();
			event.stopPropagation();
			this.login_validator = $("form#signin_form").validate({
				rules: {
					username: "required",
					password: "required",
				},
				messages: {
					username: qa_front.form_auth.error_msg,
					password: qa_front.form_auth.error_msg,
				}
			});

			var form 	 = $(event.currentTarget),
				username = form.find('input#username').val(),
				password = form.find('input#password').val(),
				button   = form.find('input.btn-submit'),
				remember = form.find('input#remember').val() ? form.find('input#remember').val() : 1,
				view     = this;

			if (this.login_validator.form()) {
				this.user.login(username, password, remember, {
					beforeSend: function() {
						view.blockUi.block(button);
					},
					success: function(user, status, jqXHR) {
						view.blockUi.unblock();
						view.closeModal();
						if (status.success) {
							AE.pubsub.trigger('ae:notification', {
								msg: status.msg,
								notice_type: 'success',
							});
							window.location.reload();//href = status.redirect;
						} else {
							AE.pubsub.trigger('ae:notification', {
								msg: status.msg,
								notice_type: 'error',
							});
						}
					}
				});
			}
		},
		doRegister: function(event) {
			event.preventDefault();
			event.stopPropagation();

			this.register_validator = $("form#signup_form").validate({
				rules: {
					username: {
						required: true,
						username: true
					},
					password: "required",
					email: {
						required: true,
						email: true
					},
					re_password: {
						required: true,
						equalTo: "#password1"
					}
				},
				messages: {
					username: {
						required: qa_front.form_auth.error_msg,
						username: qa_front.form_auth.error_username,
					},
					password: qa_front.form_auth.error_msg,
					email: {
						required: qa_front.form_auth.error_msg,
						email: qa_front.form_auth.error_email,
					},
					re_password: {
						required: qa_front.form_auth.error_msg,
						equalTo: qa_front.form_auth.error_repass,
					}
				}
			});

			var form 	 = $(event.currentTarget),
				username = form.find('input#username').val(),
				email    = form.find('input#email').val(),
				button   = form.find('input.btn-submit'),
				password = form.find('input#password1').val(),
				data     = form.serializeObject(),
				view     = this;

			if (this.register_validator.form() && !form.hasClass('processing')) {
				this.user.register(data, {
					beforeSend: function() {
						view.blockUi.block(button);
						form.addClass('processing');
					},
					success: function(user, status, jqXHR) {
						view.blockUi.unblock();
						form.removeClass('processing');
						view.closeModal();
						if (status.success) {
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
		},
		doSendPassword: function(event) {
			event.preventDefault();
			event.stopPropagation();
			this.forgot_validator = $("form#forgotpass_form").validate({
				rules: {
					email: {
						required: true,
						email: true
					},
				},
				messages: {
					email: {
						required: qa_front.form_auth.error_msg,
						email: qa_front.form_auth.error_email,
					},
				}
			});

			var form = $(event.currentTarget),
				email  = form.find('input#email').val(),
				button = form.find('input.btn-submit'),
				view   = this;

			if (this.forgot_validator.form()) {
				this.user.forgot(email, {
					beforeSend: function() {
						view.blockUi.block(button);
					},
					success: function(user, status, jqXHR) {
						view.blockUi.unblock();
						view.closeModal();
						var success = status.success ? 'success' : 'error';
						AE.pubsub.trigger('ae:notification', {
							msg: status.msg,
							notice_type: success,
						});
					}
				});
			}
		}
	});

	Views.ResetPassModal = AE.Views.Modal_Box.extend({
		events: {
			'submit form#resetpass_form': 'doResetPassword',
		},
		initialize: function() {
			AE.Views.Modal_Box.prototype.initialize.call();
			this.blockUi = new AE.Views.BlockUi();
			this.user    = new Models.User();
		},
		doResetPassword: function(event) {
			event.preventDefault();

			this.reset_validator = $("form#resetpass_form").validate({
				rules: {
					new_password: "required",
					re_new_password: {
						required: true,
						equalTo: "#new_password"
					}
				},
				messages: {
					new_password: qa_front.form_auth.error_msg,
					re_new_password: {
						required: qa_front.form_auth.error_msg,
						equalTo: qa_front.form_auth.error_repass,
					}
				}
			});

			var form = $(event.currentTarget),
				username = form.find('input#user_login').val(),
				user_key = form.find('input#user_key').val(),
				new_password = form.find('input#new_password').val(),
				button = form.find('input.btn-submit'),
				view = this;

			if (this.reset_validator.form()) {
				this.user.resetpass(username, new_password, user_key, {
					beforeSend: function() {
						view.blockUi.block(button);
					},
					success: function(user, status, jqXHR) {
						view.blockUi.unblock();
						view.closeModal();
						if (status.success) {
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

	Views.EditQuestionModal = AE.Views.Modal_Box.extend({
		events: {
			// user submit question info
			'submit form#submit_question'	: 'saveQuestion',
			// user hit enter to add new tag
			'keypress input#question_tags'	: 'onAddTag',
		},
		// initialize modal question

		initialize: function() {

			var view = this;
			/**
			 * call parent initialize
			 */
			AE.Views.Modal_Box.prototype.initialize.call();
			/**
			 * tags list container
			 */
			this.tag_list = this.$('ul.tags-list');
			/**
			 * block ui view use to block element in ajax
			 */
			this.blockUi = new AE.Views.BlockUi();
			/**
			 * init editor control to question content
			 */
			tinymce.EditorManager.execCommand("mceAddEditor", false, "insert_question");

			/**
			 * type ahead to get suggestion
			 */
			view.tags = {};

			$('#question_tags').typeahead({
				minLength: 0,
				items: 99,
				source: function(query, process) {
					if (view.tags.length > 0) return view.tags;

					return $.getJSON(
						ae_globals.ajaxURL, {
							action: 'qa_get_tags'
						},
						function(data) {
							//console.log(data);
							view.tags = data;
							return process(data);
						});

				},
				updater: function(item) {
					//console.log(item);
					view.addTag(item);
				}
			});
		},
		/**
		 * on edit a model
		 */
		onEdit: function(model) {
			this.model = model;
			this.initContent(model);
			this.openModal();
		},

		/**
		 * init modal content if model have data
		 */
		initContent: function(model) {

			var $forminfo = this.$("form#submit_question"),
				// question tags
				tags     = this.model.get("qa_tag"),
				// question category
				category = this.model.get('question_category'),
				view     = this;

			/**
			 * set timeout to have a delay when init form data
			 */
			setTimeout(function() {
				/**
				 * add content to all element in form
				 */
				$forminfo.find('input, textarea, select').each(function() {
					var value = view.model.get($(this).attr('name'));
					if ($(this).attr('type') !== 'hidden' && value != '') $(this).val(value);
				});
				/**
				 * set question category
				 */
				if (typeof category !== 'undefined' && category.length > 0) {
					view.$('#question_category').val(category[0].slug);
				}
				/**
				 * setup edit content
				 */
				if (model.get('content_edit')) {
					tinymce.get('insert_question').setContent(model.get('content_edit'));
				} else {
					tinymce.get('insert_question').setContent('');
				}


				/**
				 * init tags
				 */
				view.tag_list.html('');
				if (typeof tags !== 'undefined') {
					for (var i = 0; i < tags.length; i++) {
						view.addTag(tags[i].name);
					};
				}

			}, 500);

		},

		validate: function() {
			/**
			 * set validate form condition
			 */
			this.submit_validator = $("form#submit_question").validate({
				ignore: "",
				rules: {
					post_title: "required",
					question_category: "required",
					post_content: "required",
				},
				messages: {
					post_title: qa_front.form_auth.error_msg,
					question_category: qa_front.form_auth.error_msg,
					post_content: qa_front.form_auth.error_msg,
				}
			});
		},

		saveQuestion: function(event) {
			event.preventDefault();

			this.validate();

			var form = $(event.currentTarget),
				$button = form.find("button#btn_submit_question"),
				data = form.serializeObject(),
				view = this;

			// if (this.tag_list.find('li').length == 0) { // user should enter at least on tag
			// 	$("input#question_tags").attr('placeholder', 'Please insert at least one tag.').css('border', '1px solid red');
			// }

			if (this.submit_validator.form() && tinymce.get('insert_question').getContent() != "" /*&& this.tag_list.find('li').length > 0*/ ) {

				this.model.set('content', data);
				this.model.save('do_action', 'saveQuestion', {
					beforeSend: function() {
						view.blockUi.block($button);
						//console.log('chay');
					},
					success: function(result, status, jqXHR) {
						view.blockUi.unblock();
						if (status.success) {
							view.closeModal();
							AE.pubsub.trigger('ae:notification', {
								msg: status.msg,
								notice_type: 'success',
							});
							if(ae_globals.pending_questions)
								redirectTimeout(status.redirect, 2000);
							else
								window.location.href = status.redirect;
						} else {
							view.closeModal();
							AE.pubsub.trigger('ae:notification', {
								msg: status.msg,
								notice_type: 'error',
							});
						}
					}
				});
			}
		},

		/**
		 * add tag to modal, render tagItem base on in put tag
		 */
		addTag: function(tag) {

			var duplicates = this.tag_list.find('input[type=hidden][value="' + tag + '"]'),
				count = this.tag_list.find('li');
			if (duplicates.length == 0 && tag != '' && count.length < 5) {
				var data = {
					'name': tag
				};
				var tagView = new Views.TagItem({
					model: new Backbone.Model(data)
				});
				this.tag_list.append(tagView.render().$el);
				$('input#question_tags').val('').css('border', '1px solid #dadfea');;
			}
		},

		/**
		 * catch event user enter in tax input, call function addTag to render tag item
		 */
		onAddTag: function(event) {
			var val = $(event.currentTarget).val();
			console.log('keypress');
			if (event.which == 13) {
				/**
				 * check current user cap can add_tag or not
				 */
				var caps = currentUser.cap;
				if (typeof caps['create_tag'] === 'undefined' && $.inArray(val, this.tags) == -1) {
					this.$('#question_tags').popover({
						content: this.$('#add_tag_text').val(),
						container: '#modal_submit_questions'
					});
					return false;
				}
				/**
				 * add tag
				 */
				this.addTag(val);
			}
			return event.which != 13;
		}
	});
	if ( typeof( AE.Views.ReportModal ) == 'undefined' ){
	Views.ReportModal = AE.Views.Modal_Box.extend({
		events: {
			'submit form#report_form'  : 'submitReport'
		},
		// initialize modal question

		initialize: function() {
			this.blockUi = new AE.Views.BlockUi();

		},
		submitReport: function(event){
			event.preventDefault();

			var view    = this,
				form    = $(event.currentTarget),
				message = form.find('textarea#txt_report').val(),
				data    = form.serializeObject(),
				$button = form.find("input.btn");

			this.model.set('do_action', 'report');
			this.model.save('data',data, {
				beforeSend: function() {
					if( message == '' ){
						AE.pubsub.trigger('ae:notification', {
							msg: qa_front.form_auth.error_msg,
							notice_type: 'error',
						});
						return false;
					}
					view.blockUi.block($button);
				},
				success: function(result, status, jqXHR) {
					view.blockUi.unblock();
					if(status.success)	{
						view.closeModal();
						AE.pubsub.trigger('ae:afterReport', {
							msg: status.msg,
							notice_type: 'success'
						});
						view.stopListening(AE.pubsub, 'ae:afterReport');
					}
					else{
						AE.pubsub.trigger('ae:notification', {
							msg: status.msg,
							notice_type: 'error',
						});
					}
					$("form#report_form")[0].reset();
				},
			});
		},
		setModel : function (model) {
			this.model = model;
		},
	});
	}
	if ( typeof( AE.Views.ContactModal ) == 'undefined' ){
	Views.ContactModal = AE.Views.Modal_Box.extend({
		events: {
			'submit form#contact_form'  : 'submitContact'
		},
		// initialize modal Contact
		initialize: function() {
			this.blockUi = new AE.Views.BlockUi();
		},
		validate: function(){
			this.submit_validator = $("form#contact_form").validate({
				rules: {
					txt_msg: "required",
				},
				messages: {
					txt_msg: qa_front.form_auth.error_msg
				}
			});
		},
		submitContact: function(event){
			event.preventDefault();
			this.validate();
			var view    = this,
				form    = $(event.currentTarget),
				message = form.find('textarea#txt_msg').val(),
				$button = form.find("input.btn"),
				user_id = form.find("#user_id").val();
			if( this.submit_validator.form() ){
				$.ajax({
					url: ae_globals.ajaxURL,
					type: 'POST',
					data: {
						action: 'et_user_sync',
						method: 'inbox',
						content: {
							user_id: user_id,
							message: message
						}
					},
					beforeSend: function() {
						if( message == '' ){
							AE.pubsub.trigger('ae:notification', {
								msg: qa_front.form_auth.error_msg,
								notice_type: 'error',
							});
							return false;
						}
						view.blockUi.block($button);
					},
					success: function(status) {
						view.blockUi.unblock();
						if(status.success)	{
							view.closeModal();
							AE.pubsub.trigger('ae:notification', {
								msg: status.msg,
								notice_type: 'success',
							});
						} else {
							AE.pubsub.trigger('ae:notification', {
								msg: status.msg,
								notice_type: 'error',
							});
						}
						$("form#contact_form")[0].reset();
					}
				});
			}
		}
	});
	}
	Views.PostListItem = Backbone.View.extend({
		tagName: 'div',
		className: 'row question-main-content question-item answer-item',
		model: [],
		events: {
			'click a.action': 'doAction',
			'click a.add-comment': 'showCommentForm',
			'click a.show-comments': 'showListComment',
			'click a.hide-comment': 'hideCommentForm',
			'submit form.edit-post': 'onSubmitEdit',
			'submit form.child-reply': 'insertComment',
		},
		initialize: function() {
			if ($('#answer_item').length > 0) {
				this.template = _.template($('#answer_item').html());
			}
			this.blockUi = new AE.Views.BlockUi();
		},
		render: function(model) {
			return this.$el.html(this.template(model.toJSON()));
			this.delegateEvents();
		},
		showListComment: function(event) {
			event.preventDefault();
			var target = $(event.currentTarget),
				wrapId     = target.attr("href"),
				editorId   = "insert_answer_" + this.model.get('id'),
				editorWrap = "#editor_wrap_" + this.model.get('id'),
				countCmt   = this.$('.comments-wrapper .comment-item').length;

			target.toggleClass('active');

			if (countCmt == 0) {

				$('a.add-comment').addClass('clicked').text(qa_front.texts.add_comment).show().removeClass('clicked');
				this.$('a.add-comment').hide().addClass('clicked').text(qa_front.texts.cancel);
				$("div.child-answer-wrap").slideUp('fast');

				//create new tinymce
				tinymce.EditorManager.execCommand("mceAddEditor", false, editorId);
				if( currentUser.id !== 0 )
					tinymce.activeEditor.execCommand('mceSetContent', false, '');
				$(editorWrap).slideDown();
			}

			$(wrapId).slideToggle();
			return false;
		},
		doAction: function(event) {
			event.preventDefault();
			var target = $(event.currentTarget),
				action = target.attr('data-name'),
				view = this;
			//user not yet login
			if (currentUser.ID == 0) {
				AE.pubsub.trigger('ae:notification', {
					msg: qa_front.texts.require_login,
					notice_type: 'error',
				});
				return false;
			}
			//user has been banned
			if (currentUser.is_ban) {
				AE.pubsub.trigger('ae:notification', {
					msg: qa_front.texts.banned_account,
					notice_type: 'error',
				});
				return false;
			}
			//user not yet confirm
			if(ae_globals.user_confirm && currentUser.register_status == "unconfirm"){
				AE.pubsub.trigger('ae:notification', {
					msg: qa_front.texts.confirm_account,
					notice_type: 'error',
				});
				return false;
			}
			//get all privileges of user
			var userCaps = currentUser.cap;

			if (action != 'edit' && typeof userCaps[action] === 'undefined' &&
				// check action not in privileges
				!(action == 'unfollow' || action == 'follow' || action == 'delete' || action == 'accept-answer' || action == 'un-accept-answer' || action == "cancel-post-edit" || action == "report" || action == "approve") ) return false;

			if (target.hasClass('loading'))
				return false;

			/* ========== ON VOTEs ========== */
			if (action == "vote_up" || action == "vote_down") {

				if (currentUser.ID == this.model.get("post_author") || target.hasClass('disabled'))
					return;

				if (target.hasClass('active')) {
					target.removeClass('active');
					view.$el.find('div.vote-block a.vote').removeClass('disabled');
				} else {
					view.$el.find('div.vote-block a.vote').removeClass('active').addClass('disabled');
					target.addClass('active').removeClass('disabled');
				}
				/* ========== ON MARK ACCEPT ========== */
			} else if (action == "accept-answer" || action == "un-accept-answer") {
				if (action == "un-accept-answer" && currentUser.ID != currentQuestion.post_author)
					return false;
				if (target.hasClass('active')) {
					target.removeClass('active')
				} else {
					$('a.accept-answer').removeClass('active');
					target.addClass('active');
				}
				/* ========== ON EDIT POST ========== */
			} else if (action == "edit") {
				if (this.model.get('post_type') == 'question') {

					if ( typeof userCaps['edit_question'] === 'undefined' && currentUser.ID != this.model.get("post_author") )
						return false;

					if (typeof this.Modal_Edit === 'undefined') {
						this.Modal_Edit = new Views.EditQuestionModal({
							model: this.model,
							el: $('#modal_submit_questions')
						});
					}

					this.Modal_Edit.onEdit(this.model);

					return false;
				}

				if ( typeof userCaps['edit_answer'] === 'undefined' && currentUser.ID != this.model.get("post_author") ) return false;

				var txtID = view.$el.find('div.post-content-edit textarea').attr('id'),
					content = this.model.get('content_edit'); //this.model.get('post_content');

				view.$el.find('div.cat-infomation').fadeOut();
				view.$el.find('div.question-content').fadeOut('fast', function() {
					tinymce.EditorManager.execCommand("mceAddEditor", false, txtID);
					tinymce.activeEditor.execCommand('mcesetContent', false, content);
					tinyMCE.activeEditor.selection.select(tinyMCE.activeEditor.getBody(), true);
					tinyMCE.activeEditor.selection.collapse(false);
					view.$el.find('div.post-content-edit').fadeIn('fast', function() {
						tinymce.activeEditor.execCommand('mceAutoResize');
					});
				});
				return false;
				/* ========== ON CANCEL EDIT POST ========== */
			} else if (action == "cancel-post-edit") {
				var txtID = view.$el.find('div.post-content-edit textarea').attr('id');

				view.$el.find('div.post-content-edit').fadeOut('fast', function() {
					view.$el.find('div.question-content').fadeIn();
					view.$el.find('div.cat-infomation').fadeIn();
					tinymce.EditorManager.get(txtID).remove();
				});
				return false;
			} else if (action == "delete") {
				this.model.destroy({
					beforeSend: function() {
						target.addClass('loading');
						view.blockUi.block(view.$el);
					},
					success: function(result, status, jqXHR) {
						view.blockUi.unblock();
						target.removeClass('loading');
						if (status.success) {

							//update html
							var count = parseInt($('.answers-count .number').text());
								count--;
							$('.answers-count .number').text(count >= 1 ? count : 0);


							if (result.get('post_type') == "question")
								window.location.href = status.redirect;
							else
								view.$el.fadeOut();
						} else {
							AE.pubsub.trigger('ae:notification', {
								msg: status.msg,
								notice_type: 'error',
							});
						}
					}
				});
				return false;
			} else if ( action == "follow" || action == "unfollow" ) {
				if(action == "follow"){
					target.attr('data-original-title', 'Unfollow')
						.attr('data-name', 'unfollow')
						.removeClass('follow')
						.addClass('followed');
					target.find('i')
						.removeClass('fa-plus-square')
						.addClass('fa-minus-square');
				} else {
					target.attr('data-original-title', 'Follow')
						.attr('data-name', 'follow')
						.removeClass('followed')
						.addClass('follow');
					target.find('i')
						.removeClass('fa-minus-square')
						.addClass('fa-plus-square');
				}
			}
			else if (action == "report") {
				if (this.model.get('post_type') == 'question' || this.model.get('post_type') == 'answer') {
					this.onReport(event);
					return false;
				}

				if (typeof userCaps['edit_answer'] === 'undefined') return false;

				var txtID = view.$el.find('div.post-content-edit textarea').attr('id'),
					content = this.model.get('content_edit'); //this.model.get('post_content');

				view.$el.find('div.cat-infomation').fadeOut();
				view.$el.find('div.question-content').fadeOut('fast', function() {
					tinymce.EditorManager.execCommand("mceAddEditor", false, txtID);
					tinymce.activeEditor.execCommand('mcesetContent', false, content);
					tinyMCE.activeEditor.selection.select(tinyMCE.activeEditor.getBody(), true);
					tinyMCE.activeEditor.selection.collapse(false);
					view.$el.find('div.post-content-edit').fadeIn('fast', function() {
						tinymce.activeEditor.execCommand('mceAutoResize');
					});
				});
				return false;
			}
			//approve pending answer
			else if (action == "approve") {
				//check if answer only
				this.model.set('do_action', action);
				this.model.save('', '', {
					beforeSend: function() {
						target.addClass('loading');
						view.blockUi.block(view.$el);
					},
					success: function(result, status, jqXHR) {
						view.blockUi.unblock();
						target.removeClass('loading');
						if (status.success) {
							target.remove();
							view.$el.find('.top-content').remove();

							if(result.get('post_type') == "question" ){
								AE.pubsub.trigger('ae:notification', {
									msg: status.msg,
									notice_type: 'success',
								});
								window.location.reload();
							}

						} else {
							AE.pubsub.trigger('ae:notification', {
								msg: status.msg,
								notice_type: 'error',
							});
						}
					}
				});
				return false;
			}

			this.model.set('do_action', action);
			this.model.save('', '', {
				beforeSend: function() {
					target.addClass('loading');
				},
				success: function(result, status, jqXHR) {
					target.removeClass('loading');
					if (status.success) {
						if(action != "follow" && action != "unfollow"){
							view.$el.find('span.vote-count').text(result.get('et_vote_count'));
						} else {
							// bootbox.hideAll();
							// bootbox.alert(status.msg);
							AE.pubsub.trigger('ae:notification', {
								msg: status.msg,
								notice_type: 'success',
							});
						}
					} else {
						// bootbox.hideAll();
						// bootbox.alert(status.msg);
						AE.pubsub.trigger('ae:notification', {
							msg: status.msg,
							notice_type: 'error',
						});
					}
				}
			});
		},
		onSubmitEdit: function(event) {
			event.preventDefault();
			var view = this,
				form = $(event.currentTarget),
				$button = form.find("button.btn-submit"),
				txtID = view.$el.find('div.post-content-edit textarea').attr('id'),
				new_content = tinymce.EditorManager.get(txtID).getContent();
			this.model.set('post_content', new_content);
			this.model.set('do_action', 'savePost');
			this.model.save('', '', {
				beforeSend: function() {
					view.blockUi.block($button);
				},
				success: function(result, status, jqXHR) {
					view.blockUi.unblock();
					if (status.success) {
						view.$el.find('div.post-content-edit').fadeOut('fast', function() {
							view.$el.find('div.question-content').html(result.get('content_filter'));
							SyntaxHighlighter.highlight();
							view.$el.find('div.question-content').fadeIn();
							view.$el.find('div.cat-infomation').fadeIn();
							tinymce.EditorManager.get(txtID).remove();
						});
					} else {
						//bootbox.alert(status.msg);
						AE.pubsub.trigger('ae:notification', {
							msg: status.msg,
							notice_type: 'error',
						});
					}
				}
			});
		},
		hideCommentForm: function(event) {
			event.preventDefault();

			var target = $(event.currentTarget),
				editorId = "insert_answer_" + this.model.get('id'),
				wrapId = "#editor_wrap_" + this.model.get('id');

			this.$('a.add-comment').text(qa_front.texts.add_comment).removeClass('clicked');

			$(wrapId).slideUp('fast', function() {
				if (typeof tinymce.activeEditor != "undefined")
					tinymce.EditorManager.get(editorId).remove(); //tinymce.activeEditor.remove();
			});

			this.$('a.add-comment').show();
		},
		showCommentForm: function(event) {
			event.preventDefault();
			var target = $(event.currentTarget),
				id = target.attr('data-id'),
				editorId = "insert_answer_" + id,
				wrapId = "#editor_wrap_" + id;

			if (currentUser.ID == 0) {
				//bootbox.alert(qa_front.texts.require_login);
				AE.pubsub.trigger('ae:notification', {
					msg: qa_front.texts.require_login,
					notice_type: 'error',
				});
				return false;
			}

			//var userCaps = this.currentUser.get('cap');
			var userCaps = currentUser.cap;

			if (typeof userCaps['add_comment'] === 'undefined') return false;

			//destroy all active tinymce
			$('a.add-comment').text(qa_front.texts.add_comment).show();
			$("div.child-answer-wrap").slideUp('fast');

			if (target.hasClass('clicked')) {
				target.removeClass('clicked');
				$('a.add-comment').text(qa_front.texts.add_comment);
				$(wrapId).slideUp('fast', function() {
					if (typeof tinymce.activeEditor != "undefined")
						tinymce.activeEditor.remove();
				});
			} else {
				$('a.add-comment').removeClass('clicked');
				target.addClass('clicked')
				//create new tinymce
				target.text(qa_front.texts.cancel);
				tinymce.EditorManager.execCommand("mceAddEditor", false, editorId);
				tinymce.activeEditor.execCommand('mceSetContent', false, '');
				$(wrapId).slideDown();
			}
			target.hide();
			return false;
		},
		insertComment: function(event) {
			event.preventDefault();

			if(ae_globals.user_confirm && currentUser.register_status == "unconfirm"){
				//bootbox.alert(qa_front.texts.confirm_account);
				AE.pubsub.trigger('ae:notification', {
					msg: qa_front.texts.confirm_account,
					notice_type: 'error',
				});
				return false;
			}

			var form = $(event.currentTarget),
				$button = form.find("button.btn-submit"),
				data = form.serializeObject(),
				view = this;

			if (currentUser.ID == 0) {
				//bootbox.alert(qa_front.texts.require_login);
				AE.pubsub.trigger('ae:notification', {
					msg: qa_front.texts.require_login,
					notice_type: 'error',
				});
				return false;
			}

			if (tinymce.activeEditor.getContent() == '')
				return;

			answer = new Models.Post();
			answer.set('content', data);
			answer.save('', '', {
				beforeSend: function() {
					view.blockUi.block($button);
				},
				success: function(result, status, jqXHR) {
					view.blockUi.unblock();
					if (status.success) {
						viewPost = new Views.CommentItem({
							id: result.get('comment_ID'),
							model: result
						});
						view.$('.child-answer-wrap').slideUp();
						view.$('a.add-comment').text(qa_front.texts.add_comment).removeClass('clicked').show();
						tinymce.activeEditor.setContent('');
						view.$el.find('.comments-wrapper').append(viewPost.render(result));
						SyntaxHighlighter.highlight();
					} else {
						//bootbox.alert(status.msg);
						AE.pubsub.trigger('ae:notification', {
							msg: status.msg,
							notice_type: 'error',
						});
					}
				}
			});
		},
		onReport: function(event) {
			var view = this
			var Modal_Report = QAEngine.App.getReportModal(this.model);
			Modal_Report.openModal(false);
			view.listenTo(AE.pubsub, 'ae:afterReport', function(){
					view.$('.report').remove();
				});
		},
		afterReport: function(model){
		},
	});

	Views.CommentItem = Views.PostListItem.extend({
		className: 'row comment-item',
		events: {
			'click a.action': 'doAction',
			'click a.edit-comment': 'editComment',
			'click a.delete-comment': 'deleteComment',
			'click a.cancel-comment': 'cancelComment',
			'submit form.edit-comment': 'updateComment',
		},
		initialize: function() {
			Views.PostListItem.prototype.initialize.call();
			this.model.set('id', this.model.get('comment_ID'));
			if ($('#comment_item').length > 0) {
				this.template = _.template($('#comment_item').html());
			}
			this.blockUi = new AE.Views.BlockUi();
		},
		deleteComment: function(event) {
			event.preventDefault();
			var view = this;
			this.model.set('do_action','deleteComment');
			this.model.destroy({
				beforeSend: function() {
					view.blockUi.block(view.$el);
				},
				success: function(result, status, jqXHR) {
					view.blockUi.unblock();
					if (status.success) {
						view.$el.fadeOut();
					} else {
						//bootbox.alert(status.msg);
						AE.pubsub.trigger('ae:notification', {
							msg: status.msg,
							notice_type: 'error',
						});
					}
				}
			});
			return false;
		},
		editComment: function(event) {
			event.preventDefault();
			//console.log('edit comment');
			var view = this,
				txtID = view.$el.find('div.cm-content-edit textarea').attr('id'),
				content = this.model.get('content_edit'); //this.model.get('comment_content');

			view.$el.find('div.cm-content-wrap').fadeOut('fast', function() {
				tinymce.EditorManager.execCommand("mceAddEditor", false, txtID);
				tinymce.activeEditor.execCommand('mcesetContent', false, content);
				tinyMCE.activeEditor.selection.select(tinyMCE.activeEditor.getBody(), true);
				tinyMCE.activeEditor.selection.collapse(false);
				view.$el.find('div.cm-content-edit').fadeIn('fast', function() {
					tinymce.activeEditor.execCommand('mceAutoResize');
				});
			});
		},
		cancelComment: function(event) {
			event.preventDefault();
			//console.log('cancel comment');
			var view = this,
				txtID = view.$el.find('div.cm-content-edit textarea').attr('id');

			view.$el.find('div.cm-content-edit').fadeOut('fast', function() {
				view.$el.find('div.cm-content-wrap').fadeIn();
				tinymce.EditorManager.get(txtID).remove();
			});
		},

		/**
		 * update comment model.save
		 */
		updateComment: function(event) {
			event.preventDefault();
			var $target = $(event.currentTarget);
			view = this,
			txtID = view.$el.find('div.cm-content-edit textarea').attr('id'),
			new_content = tinymce.EditorManager.get(txtID).getContent();

			this.model.set('comment_content', new_content);
			this.model.set('do_action', 'saveComment');

			this.model.save('', '', {
				beforeSend: function() {
					view.blockUi.block($target);
				},
				success: function(result, status, jqXHR) {
					view.blockUi.unblock();
					if (status.success) {
						view.$el.find('div.cm-content-edit').fadeOut('fast', function() {
							view.$el.find('div.cm-content-wrap .cm-wrap').html(result.get('content_filter'));
							SyntaxHighlighter.highlight();
							view.$el.find('div.cm-content-wrap').fadeIn();
							tinymce.EditorManager.get(txtID).remove();
						});
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
	});

	Views.TagItem = Backbone.View.extend({

		'tagName': 'li',
		'className': 'tag-item',
		events: {
			'click a.delete': 'deleteItem'
		},
		//template 	: _.template( $('#tag_item').html() ),
		initialize: function() {
			if ($('#tag_item').length > 0)
				this.template = _.template($('#tag_item').html());
		},
		render: function() {
			this.$el.html(this.template(this.model.toJSON()));
			return this;
		},
		deleteItem: function(event) {
			event.preventDefault();
			this.$el.fadeOut('normal', function() {
				$(this).remove();
			});
		}
	});

	Views.EditProfileModal = AE.Views.Modal_Box.extend({
		events: {
			'submit form#submit_edit_profile': 'saveProfile',
			'submit form#submit_edit_password': 'changePassword',
			'click button.close': 'resetUploader',
		},
		initialize: function() {
			AE.Views.Modal_Box.prototype.initialize.call();
			this.blockUi = new AE.Views.BlockUi();
			this.user = new Models.User(currentUser);

			var $container = $("#user_avatar_container"),
				view = this;
			if (typeof this.avatar_uploader === "undefined") {
				this.avatar_uploader = new AE.Views.File_Uploader({
					el: $container,
					uploaderID: 'user_avatar',

					thumbsize: 'thumbnail',
					multipart_params: {
						_ajax_nonce: $container.find('.et_ajaxnonce').attr('id'),
						action: 'et_user_sync',
						method: 'change_logo',
						author: this.user.get('ID')
					},
					cbUploaded: function(up, file, res) {
						if (res.success) {
							$('#' + this.container).parents('.desc').find('.error').remove();
						} else {
							$('#' + this.container).parents('.desc').append('<div class="error">' + res.msg + '</div>');
						}
					},
					beforeSend: function(ele) {
						button = $(ele).find('.image');
						view.blockUi.block(button);
					},
					success: function() {
						view.blockUi.unblock();
						window.location.reload();
					}
				});
			}
		},
		resetUploader: function() {
			this.avatar_uploader.controller.splice();
			this.avatar_uploader.controller.refresh();
			this.avatar_uploader.controller.destroy();
		},
		saveProfile: function(event) {
			event.preventDefault();

			this.submit_validator = $("form#submit_edit_profile").validate({
				rules: {
					display_name: "required",
					// user_location: "required",
					user_email: {
						required: true,
						email: true
					},
					user_facebook: {
						url: true
					},
					user_twitter: {
						url: true
					},
					user_gplus: {
						url: true
					}
				},
				messages: {
					display_name: qa_front.form_auth.error_msg,
					// user_location: qa_front.form_auth.error_msg,
					user_email: {
						required: qa_front.form_auth.error_msg,
						email: qa_front.form_auth.error_email,
					}
				}
			});

			var form = $(event.currentTarget),
				$button = form.find("input.btn-submit"),
				data = form.serializeObject(),
				view = this;

				data.user_interest = view.tags;

			if (this.submit_validator.form()) {

				this.user.set('content', data);
				this.user.save('do_action', 'saveProfile', {
					beforeSend: function() {
						view.blockUi.block($button);
						//console.log('chay');
					},
					success: function(result, status, jqXHR) {
						if (status.success) {
							window.location.href = status.redirect;
						} else {
							view.closeModal();
							AE.pubsub.trigger('ae:notification', {
								msg: status.msg,
								notice_type: 'error',
							});
						}
						view.blockUi.unblock();
					}
				});
			}
		},
		changePassword: function(event) {
			event.preventDefault();

			this.change_pass_validator = this.$("form#submit_edit_password").validate({
				rules: {
					old_password: "required",
					new_password: "required",
					re_password: {
						required: true,
						equalTo: "#new_password1"
					},
				},
				messages: {
					old_password: qa_front.form_auth.error_msg,
					new_password: qa_front.form_auth.error_msg,
					re_password: {
						required: qa_front.form_auth.error_msg,
						equalTo: qa_front.form_auth.error_repass,
					}
				}
			});

			var form = $(event.currentTarget),
				$button = form.find("input.btn-submit"),
				data = form.serializeObject(),
				view = this;

			if (this.change_pass_validator.form()) {

				this.user.set('content', data);
				this.user.save('do_action', 'changePassword', {
					beforeSend: function() {
						view.blockUi.block($button);
						//console.log('chay');
					},
					success: function(result, status, jqXHR) {
						if (status.success) {
							window.location.href = status.redirect;
						} else {
							view.closeModal();
							AE.pubsub.trigger('ae:notification', {
								msg: status.msg,
								notice_type: 'error',
							});
						}
						view.blockUi.unblock();
					}
				});
			}
		}
	});

	Views.Front = Backbone.View.extend({
		el: 'body',
		defaults: {
			AuthModal: false
		},
		currentUser: [],
		events: {
			'click button.ask-question'			: 'openSubmitModal',
			'click a.login-url'					: 'openAuthModal',
			'click a.edit_profile'				: 'openEditProfileModal',
			'change select#filter-numbers'		: 'sortPostNumber',
			'change select#move_to_category'	: 'moveToCategory',
			'keyup #header_search input' 		: 'onSearch',
			'focus #header_search input' 		: 'showSearchPreview',
			'click div.wp-link-backdrop'		: 'preventDefault'
		},
		initialize: function() {

			this.blockUi        = new AE.Views.BlockUi();
			this.searchDebounce = _.debounce(this.searchAjax, 500);

			this.noti_templates = new _.template(
				'<div class="pubsub-notification autohide {{= type }}-bg">' +
				'<div class="main-center">' +
				'{{= msg }}' +
				'</div>' +
				'</div>'
			);
			//validate tinymce
			$("#insert_question").on('change',function(event) {
				$(this).valid();
			});
			// catch event nofifications
			AE.pubsub.on('ae:notification', this.showNotice, this);
			AE.pubsub.on('ae:afterReport',this.showNotice, this);
		},
		preventDefault: function(event){
			event.preventDefault();
			return false;
		},
		/*
		 * Show notification
		 */
		showNotice: function(params) {
			var view = this;
			// remove existing notification
			$('div.notification').remove();

			var notification = $(view.noti_templates({
				msg: params.msg,
				type: params.notice_type
			}));

			if ($('#wpadminbar').length !== 0) {
				notification.addClass('having-adminbar');
			}

			notification.hide().prependTo('body')
				.fadeIn('fast')
				.delay(1000)
				.fadeOut(7000, function() {
					$(this).remove();
				});
		},
		searchAjax: function(){
			var input 			= $('#header_search input'),
				icon 			= $('#header_search i'),
				searchValue 	= input.val(),
				source 			= $('#search_preview_template').html(),
				template 		= _.template(source),
				outputContainer = $('#search_preview'),
				content 		= {
									's' : searchValue
								},
				view 			= this;

			if ( searchValue == '' ){
				$('#search_preview').addClass('empty');
				return false;
			}

			if ( $("#fe_search_form #thread_category").length > 0 ){
				content.thread_category = $("#fe_search_form #thread_category").val()
			}

			var params 	= {
				url 	: ae_globals.ajaxURL,
				type 	: 'post',
				data 	: {
					'action'   	: 'et_search',
					'content' 	: content
				},
				beforeSend: function(){
					icon.attr('class', 'fa fa-refresh fa-spin');
				},
				success: function(resp){
					icon.attr('class', 'fa fa-search');
					if ( resp.success ){
						var data = resp.data;

						var output = template(resp.data);
						outputContainer.html(output).removeClass('empty').fadeIn();

						view.onShowSearchPreview();
					}
				},
				complete: function(){

				}
			};
			$.ajax(params);
		},
		onSearch:function(event){
			var element = event.currentTarget,
				keyCode	= event.which;

			this.searchDebounce();
		},
		onShowSearchPreview: function(e){
			var outputContainer = $('#search_preview');
			var input 			= $('#header_search input').get(0);
			$('body').bind('click', function(e){
				if ( !$.contains( outputContainer.get(0), e.target) && e.target != input ){
					outputContainer.hide();
					//$('body').unbind('click');
				}
			});
		},

		showSearchPreview: function(event){
			var outputContainer = $('#search_preview'),
				view = this;
			if ( !outputContainer.hasClass('empty') ){

				outputContainer.show();

				view.onShowSearchPreview();
			}
		},
		openEditProfileModal: function(event) {
			event.preventDefault();
			if (typeof this.editProfilemodal === 'undefined') {
				this.editProfilemodal = new Views.EditProfileModal({
					el: $("#edit_profile")
				});
			}
			this.editProfilemodal.openModal();
		},
		openAuthModal: function(event) {
			event.preventDefault();
			if(typeof this.authModal === "undefined"){
				this.authModal = new Views.AuthModal({
					el: $('#login_register')
				});
			}
			this.authModal.openModal();
		},
		moveToCategory: function(event) {
			event.preventDefault();
			var target = $(event.currentTarget);
			if (target.val() != "")
				window.location.href = target.val();
		},
		sortPostNumber: function(event) {
			event.preventDefault();
			var target = $(event.currentTarget);
			if (target.val() != "")
				window.location.href = target.val();
		},
		openAgain: function(){
			//console.log('open div');
			$('#modal_submit_questions').css('display','block');
		},
		openSubmitModal: function(event) {
			event.preventDefault();

			if (currentUser.ID == 0) {
				if(typeof this.authModal === "undefined"){
					this.authModal = new Views.AuthModal({
						el: $('#login_register')
					});
				}
				this.authModal.openModal();
				return false;
			}

			if(ae_globals.user_confirm && currentUser.register_status == "unconfirm"){
				AE.pubsub.trigger('ae:notification', {
					msg: qa_front.texts.confirm_account,
					notice_type: 'error',
				});
				return false;
			}

			// var model 			= new Q
			if (typeof this.submitModal === 'undefined') {
				this.submitModal = new Views.EditQuestionModal({
					model: new Models.Post(),
					el: $('#modal_submit_questions')
				});
			}
			/**
			 * setup content to model
			 */

			this.submitModal.onEdit(new Models.Post());
		},
		getReportModal: function(model){
			if (typeof this.Modal_Report === 'undefined') {
				this.Modal_Report = new Views.ReportModal({
					el: $('#reportFormModal')
				});
			}
			this.Modal_Report.setModel(model);
			return this.Modal_Report;
		},
	});

})(QAEngine.Views, QAEngine.Models, jQuery, Backbone);