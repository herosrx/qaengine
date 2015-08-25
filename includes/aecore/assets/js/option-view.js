/**
 * backend models Options
 */

(function(Models, Views, $, Backbone) {
	/**
	 * model option
	 */
	Models.Options = Backbone.Model.extend({
		action: 'ae-option-sync',
		defaults: function() {
			return {
				name: "option_name",
				value: "option_value"
			};
		}
	});

	/**
	 * swith option view
	 */
	Views.switchOption = Backbone.View.extend({
		tag: 'div',
		className: '.switch',
		events: {
			'click a.deactive': 'disable',
			'click a.active': 'enable'
		},
		initialize: function() {
			this.model = new Models.Options();
			this.form = this.$el.parents('form');
			this.model.set('name', this.form.attr('data-name'));
			this.blockUi = new Views.BlockUi();
		},
		/**
		 * enable option
		 */
		enable: function(e) {
			e.preventDefault();
			var $target = $(e.currentTarget),
				view = this;
			this.$('input').val(1);
			/**
			 * try to set name of model
			*/
			this.model.set('name', this.form.attr('data-name'));
			// update model
			if (typeof this.model.get('name') !== 'undefined') {
				this.model.set('value', this.form.serialize());
				this.model.set('group', 1);
			} else {
				this.model.set('name', this.$('input').attr('name'))
				this.model.set('value', 1);
			}
			this.model.save('', '', {
				success: function() {
					view.blockUi.unblock();
					view.$('a').removeClass('selected');
					$target.addClass('selected');
				},
				beforeSend: function() {
					view.blockUi.block($target)
				}
			});
		},
		/**
		 * disable option
		 */
		disable: function(e) {
			e.preventDefault();
			var $target = $(e.currentTarget),
				view = this;
			this.$('input').val(0);
			/**
			 * try to set name of model
			*/
			this.model.set('name', this.form.attr('data-name'));
			// update model
			if (typeof this.model.get('name') !== 'undefined') {
				this.model.set('value', this.form.serialize());
				this.model.set('group', 1);
			} else {
				this.model.set('name', this.$('input').attr('name'))
				this.model.set('value', 0);
			}
			this.model.save('', '', {
				success: function() {
					view.blockUi.unblock();
					view.$('a').removeClass('selected');
					$target.addClass('selected');
				},
				beforeSend: function() {
					view.blockUi.block($target)
				}
			});
		}
	});

	/**
	 * options view
	 */
	Views.Options = Backbone.View.extend({
		events: {
			'change input.regular-text': 'textChange',
			'change textarea.regular-text': 'textChange',
			'click .inner-menu li a': 'changeMenu',
			'focusout  textarea.regular-editor': 'editorChange',
			'click div.field-desc .btn-template-help': 'toggleDesc',
			'click a.reset-default': 'resetOption'
			// 'click .switch a'				: 'switch'
		},

		initialize: function() {

			var view = this;
			this.switchopt = [];

			this.$el.find('.switch').each(function(index, element) {
				var el = view.$('.switch:eq(' + index + ')');
				view.switchopt[index] = new Views.switchOption({
					el: el
				});
			});

			this.$el.find('.editor').each(function() {
				if (typeof tinymce !== 'undefined') {
					tinymce.EditorManager.execCommand('mceAddEditor', true, $(this).attr('id'));
				}
			});


			this.option = this.model;
			this.blockUi = new Views.BlockUi();

			this.uploaders = [];
			cbBeforeSend = function(ele) {
				button = $(ele).find('.image');
				view.blockUi.block(button);
			},
			cbSuccess = function() {
				view.blockUi.unblock();
			};

			var uploaders = []
			$('.upload-logo').each(function() {
				var upload_id = $(this).attr('data-id');
				uploaders.push(upload_id);
			});

			for (var i = uploaders.length - 1; i >= 0; i--) {
				var upload_id = uploaders[i];
				console.log(upload_id);
				$container = view.$('#' + upload_id + '_container');

				view.uploaders[upload_id] = new Views.File_Uploader({
					el: $container,
					uploaderID: upload_id,
					thumbsize: 'thumbnail',
					multipart_params: {
						_ajax_nonce: $container.find('.et_ajaxnonce').attr('id'),
						action: 'et-change-branding',
						imgType: upload_id
					},
					cbUploaded: function(up, file, res) {
						if (res.success) {
							$('#' + this.container).parents('.desc').find('.error').remove();
						} else {
							$('#' + this.container).parents('.desc').append('<div class="error">' + res.msg + '</div>');
						}
					},
					beforeSend: cbBeforeSend,
					success: cbSuccess
				});

			};
		},
		// text input, textarea change update option
		textChange: function(e) {
			var $target = $(e.currentTarget),
				form = $target.parents('form');
			view = this;

			if (form.attr('data-name') !== 'undefined' && form.attr('data-name')) {
				view.option.set('name', form.attr('data-name'));
				view.option.set('group', 1);
				view.option.set('value', form.serialize());
			} else {
				view.option.set('name', $target.attr('name'));
				view.option.set('value', $target.val());
			}

			view.option.save('', '', {
				success: function(result, status, jqXHR) {
					view.blockUi.unblock();
					console.log(result);
					// check status and append tick success
					if (status.success) {
						$target.parent().append('<span class="icon form-icon" data-icon="3"></span>');
						setTimeout(function() {
							view.$('.form-icon').remove();
						}, 2000);
					} else {
						$target.parent().append('<span class="icon form-icon" data-icon="!"></span>');
						setTimeout(function() {
							view.$('.form-icon').remove();
						}, 2000);
					}
				},
				beforeSend: function() {
					view.blockUi.block($target);
				}
			});


		},

		// update text in editor
		editorChange: _.debounce(function(e) {
			var $target = $(e.currentTarget),
				$container = $target.parents('.form-item'),
				form = $target.parents('form');
			view = this;

			if (form.attr('data-name') !== 'undefined' && form.attr('data-name')) {
				this.option.set('name', form.attr('data-name'));
				this.option.set('group', 1);
				this.option.set('value', form.serialize());
			} else {
				this.option.set('name', $target.attr('name'));
				this.option.set('value', $target.val());
			}

			this.option.save('', '', {
				success: function(result, status, jqXHR) {
					view.blockUi.unblock();

					// check status and append tick success
					if (status.success) {
						$target.parent().append('<span class="icon form-icon" data-icon="3"></span>');
						setTimeout(function() {
							view.$('.form-icon').remove();
						}, 2000);
					} else {
						$target.parent().append('<span class="icon form-icon" data-icon="!"></span>');
						setTimeout(function() {
							view.$('.form-icon').remove();
						}, 2000);
					}
				},
				beforeSend: function() {
					view.blockUi.block($container);
				}
			});

		}, 500),

		/**
		 * toogle description file hidden content
		 */
		toggleDesc: function(e) {
			$(e.currentTarget).parent().find('.cont-template-help').toggle();
		},

		/**
		 * reset an option to default value
		 */
		resetOption: function(e) {
			e.preventDefault();
			var $target = $(e.currentTarget),
				$textarea = $target.parents('.form-item').find('textarea'),
				mail_type = $textarea.attr('name');

			$.ajax({
				url: ae_globals.ajaxURL,
				type: 'post',
				data: {
					option_name: mail_type,
					action: 'ae-reset-option'
				},
				beforeSend: function(event) {

				},
				success: function(response) {
					if (response && typeof response.msg !== 'undefined') {
						$textarea.val(response.msg);
						if ($textarea.hasClass('regular-editor')) {
							var ed = tinymce.EditorManager.get($textarea.attr('id'));
							ed.setContent(response.msg);
						}
					}

				}
			});
		},
		/**
		 * change menu view
		 */
		changeMenu: function(e) {
			e.preventDefault();
			var $target = $(e.currentTarget);
			this.$('.inner-content').hide();
			this.$('.inner-menu li a').removeClass('active');
			this.$($target.attr('href')).show();
			$target.addClass('active');
		}

	});

})(window.AE.Models, window.AE.Views, jQuery, Backbone);