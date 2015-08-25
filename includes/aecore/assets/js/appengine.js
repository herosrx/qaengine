// declare everything inside this object
window.AE = window.AE || {};

(function(AE, $, Backbone) {

	AE.Models = AE.Models || {};
	AE.Collections = AE.Collections || {};
	AE.Views = AE.Views || {};
	AE.Routers = AE.Routers || {};

	// the pub/sub object for managing event throughout the app
	AE.pubsub = AE.pubsub || {};
	_.extend(AE.pubsub, Backbone.Events);

	AE.globals = ae_globals;

	/**
	 * override backbone sync function
	 */
	Backbone.sync = function(method, model, options) {
		var data = model.attributes;
		data.action = model.action || 'ae-sync';

		switch (method) {
			case 'create':
				data.method = 'create';
				break;
			case 'update':
				data.method = 'update';
				break;

			case 'delete':
				data.method = 'remove';
				break;

			case 'read':
				data.method = 'read';
				break;
		}

		var ajaxParams = {
			type: 'POST',
			dataType: 'json',
			data: data,
			url: AE.globals.ajaxURL,
			contentType: 'application/x-www-form-urlencoded;charset=UTF-8'
		};

		ajaxParams = _.extend(ajaxParams, options);

		if (options.beforeSend !== 'undefined')
			ajaxParams.beforeSend = options.beforeSend;

		ajaxParams.success = function(result, status, jqXHR) {
			AE.pubsub.trigger('ae:success', result, status, jqXHR);

			// if(method == 'create' && status.success == true) {
			//     var id = (typeof result.data.ID !== 'undefined' )  ? result.data.ID : result.data.comment_ID;
			//     model.set('id', id);
			// }

			options.success(result, status, jqXHR);
			/**
			 * update model id
			 */

		};

		ajaxParams.error = function(jqXHR, status, errorThrown) {
			AE.pubsub.trigger('ae:error', jqXHR, status, errorThrown);
			options.error(jqXHR, status, errorThrown);
		};

		options || (options = {});

		$.ajax(ajaxParams);

	};

	/**
	 * override backbone collection sync
	 */
	Backbone.Collection.prototype.sync = function(method, collection, options) {
		var data = collection.getAction();
		var ajaxParams = {
			type: 'POST',
			dataType: 'json',
			data: data,
			url: AE.globals.ajaxURL,
			contentType: 'application/x-www-form-urlencoded;charset=UTF-8'
		};

		if (options.data !== 'undefined')
			options.data = _.extend(options.data, data);
		ajaxParams = _.extend(ajaxParams, options);

		console.log(method);
		/**
		 * add beforsend function
		 */
		if (options.beforeSend !== 'undefined')
			ajaxParams.beforeSend = options.beforeSend;
		/**
		 * success function
		 */
		ajaxParams.success = function(result, status, jqXHR) {
			AE.pubsub.trigger('ae:success', result, status, jqXHR);
			options.success(result, status, jqXHR);
			collection.paged++;
		};

		ajaxParams.error = function(jqXHR, status, errorThrown) {
			AE.pubsub.trigger('ae:error', jqXHR, status, errorThrown);
			options.error(jqXHR, status, errorThrown);
		};

		options || (options = {});

		$.ajax(ajaxParams);
		// console.log(collection.getAction());
	}

	/**
	 * override backbone model parse function
	 */
	Backbone.Model.prototype.parse = function(result) {
		if (_.isObject(result.data)) {
			//result.data.id  =   result.data.ID;
			return result.data;
		} else {
			return result;
		}
	};

	/**
	 * override backbone model parse function
	 */
	Backbone.Collection.prototype.parse = function(result) {
		if (_.isObject(result.data)) {
			return result.data;
		} else {
			return result;
		}
	};

	// create a shorthand for our pubsub
})(window.AE, jQuery, Backbone);


// build basic view
(function(AE, $, Backbone) {
	// create a shorthand for the params used in most ajax request
	AE.ajaxParams = {
		type: 'POST',
		dataType: 'json',
		url: AE.globals.ajaxURL,
		contentType: 'application/x-www-form-urlencoded;charset=UTF-8'
	};

	var ajaxParams = AE.ajaxParams;

	/**
	 * loading effec view
	 */
	AE.Views.LoadingEffect = Backbone.View.extend({
		initialize: function() {},
		render: function() {
			this.$el.html(AE.globals.loadingImg);
			return this;
		},
		finish: function() {
			this.$el.html(AE.globals.loadingFinish);
			var view = this;
			setTimeout(function() {
				view.$el.fadeOut(500, function() {
					$(this).remove();
				});
			}, 1000);
		},
		remove: function() {
			view.$el.remove();
		}
	});
	/**
	 * blockui view
	 * block an Dom Element with loading image
	 */
	AE.Views.BlockUi = Backbone.View.extend({
		defaults: {
			image: AE.globals.imgURL + '/loading.gif',
			opacity: '0.5',
			background_position: 'center center',
			background_color: '#ffffff'
		},

		isLoading: false,

		initialize: function(options) {
			//var defaults = _.clone(this.defaults);
			options = _.extend(_.clone(this.defaults), options);

			var loadingImg = options.image;
			this.overlay = $('<div class="loading-blur loading"><div class="loading-overlay"></div><div class="loading-img"></div></div>');
			this.overlay.find('.loading-img').css({
				'background-image': 'url(' + options.image + ')',
				'background-position': options.background_position
			});

			this.overlay.find('.loading-overlay').css({
				'opacity': options.opacity,
				'filter': 'alpha(opacity=' + options.opacity * 100 + ')',
				'background-color': options.background_color
			});
			this.$el.html(this.overlay);

			this.isLoading = false;
		},

		render: function() {
			this.$el.html(this.overlay);
			return this;
		},

		block: function(element) {
			var $ele = $(element);
			// if ( $ele.css('position') !== 'absolute' || $ele.css('position') !== 'relative'){
			//         $ele.css('position', 'relative');
			// }
			this.overlay.css({
				'position': 'absolute',
				'z-index': 2000,
				'top': $ele.offset().top,
				'left': $ele.offset().left,
				'width': $ele.outerWidth(),
				'height': $ele.outerHeight()
			});

			this.isLoading = true;

			this.render().$el.show().appendTo($('body'));
		},

		unblock: function() {
			this.$el.remove();
			this.isLoading = false;
		},

		finish: function() {
			this.$el.fadeOut(500, function() {
				$(this).remove();
			});
			this.isLoading = false;
		}
	});

	AE.Views.LoadingButton = Backbone.View.extend({
		dotCount: 3,
		isLoading: false,
		initialize: function() {
			if (this.$el.length <= 0) return false;
			var dom = this.$el[0];
			//if ( this.$el[0].tagName != 'BUTTON' && (this.$el[0].tagName != 'INPUT') ) return false;

			if (this.$el[0].tagName == 'INPUT') {
				this.title = this.$el.val();
			} else {
				this.title = this.$el.html();
			}

			this.isLoading = false;
		},
		loopFunc: function(view) {
			var dots = '';
			for (i = 0; i < view.dotCount; i++)
				dots = dots + '.';
			view.dotCount = (view.dotCount + 1) % 3;
			view.setTitle(AE.globals.loading + dots);
		},
		setTitle: function(title) {
			if (this.$el[0].tagName === 'INPUT') {
				this.$el.val(title);
			} else {
				this.$el.html(title);
			}
		},
		loading: function() {
			//if ( this.$el[0].tagName != 'BUTTON' && this.$el[0].tagName != 'A' && (this.$el[0].tagName != 'INPUT') ) return false;
			this.setTitle(AE.globals.loading);

			this.$el.addClass('disabled');
			var view = this;

			view.isLoading = true;
			view.dots = '...';
			view.setTitle(AE.globals.loading + view.dots);

			this.loop = setInterval(function() {
				if (view.dots === '...') view.dots = '';
				else if (view.dots === '..') view.dots = '...';
				else if (view.dots === '.') view.dots = '..';
				else view.dots = '.';
				view.setTitle(AE.globals.loading + view.dots);
			}, 500);
		},
		finish: function() {
			var dom = this.$el[0];
			this.isLoading = false;
			clearInterval(this.loop);
			this.setTitle(this.title);
			this.$el.removeClass('disabled');
		}
	});

	// View: Modal Box
	AE.Views.Modal_Box = Backbone.View.extend({
		defaults: {
			top: 100,
			overlay: 0.5
		},
		$overlay: null,

		initialize: function() {
			// bind all functions of this object to itself
			//_.bindAll(this.openModal);
			// update custom options if having any
			this.options = $.extend(this.defaults, this.options);
		},

		openModal: function() {
			var view = this;
			this.$el.modal('toggle');
		},

		closeModal: function(time, callback) {
			var modal = this;
			modal.$el.modal('toggle');
			return false;
		}
	});

	/*
    /*AE File uploader
    */
	AE.Views.File_Uploader = Backbone.View.extend({
		//options            : [],
		initialize: function(options) {
			_.bindAll(this, 'onFileUploaded', 'onFileAdded', 'onFilesBeforeSend', 'onUploadComplete');

			this.options = options;
			this.uploaderID = (this.options.uploaderID) ? this.options.uploaderID : 'et_uploader';
			// console.log(this.uploaderID);
			this.config = {
				runtimes: 'gears,html5,flash,silverlight,browserplus,html4',
				multiple_queues: true,
				multipart: true,
				urlstream_upload: true,
				multi_selection: false,
				upload_later: false,
				container: this.uploaderID + '_container',
				browse_button: this.uploaderID + '_browse_button',
				thumbnail: this.uploaderID + '_thumbnail',
				thumbsize: 'thumbnail',
				file_data_name: this.uploaderID,
				max_file_size: '1mb',
				//chunk_size                         : '1mb',
				// this filters is an array so if we declare it when init Uploader View, this filters will be replaced instead of extend
				filters: [{
					title: 'Image Files',
					extensions: 'jpg,jpeg,gif,png'
				}],
				multipart_params: {
					fileID: this.uploaderID
				}
			};

			jQuery.extend(true, this.config, AE.globals.plupload_config, this.options);

			this.controller = new plupload.Uploader(this.config);
			this.controller.init();

			this.controller.bind('FileUploaded', this.onFileUploaded);
			this.controller.bind('FilesAdded', this.onFileAdded);
			this.controller.bind('BeforeUpload', this.onFilesBeforeSend);
			this.bind('UploadSuccessfully', this.onUploadComplete);

			if (typeof this.controller.settings.onProgress === 'function') {
				this.controller.bind('UploadProgress', this.controller.settings.onProgress);
			}
			if (typeof this.controller.settings.onError === 'function') {
				this.controller.bind('Error', this.controller.settings.onError);
			} else {
				this.controller.bind('Error', this.errorLog);
			}
			if (typeof this.controller.settings.cbRemoved === 'function') {
				this.controller.bind('FilesRemoved', this.controller.settings.cbRemoved);
			}

		},

		errorLog: function(e, b) {

		},

		onFileAdded: function(up, files) {
			if (typeof this.controller.settings.cbAdded === 'function') {
				this.controller.settings.cbAdded(up, files);
			}
			if (!this.controller.settings.upload_later) {
				up.refresh();
				up.start();
				console.log('start');
			}
		},

		onFileUploaded: function(up, file, res) {

			res = $.parseJSON(res.response);
			if (typeof this.controller.settings.cbUploaded === 'function') {
				this.controller.settings.cbUploaded(up, file, res);
			}
			if (res.success) {

				this.updateThumbnail(res.data);
				this.trigger('UploadSuccessfully', res);
			}

		},

		updateThumbnail: function(res) {
			var that = this,
				$thumb_div = this.$('#' + this.controller.settings['thumbnail']),
				$existing_imgs, thumbsize;

			if ($thumb_div.length > 0) {

				$existing_imgs = $thumb_div.find('img'),
				thumbsize = this.controller.settings['thumbsize'];
				console.log('length' + $existing_imgs.length);

				if ($existing_imgs.length > 0) {

					$existing_imgs.fadeOut(100, function() {
						$existing_imgs.remove();
						if (_.isArray(res[thumbsize])) {
							that.insertThumb(res[thumbsize][0], $thumb_div);
						}
					});
				} else if (_.isArray(res[thumbsize])) {
					this.insertThumb(res[thumbsize][0], $thumb_div);
				}
			}
		},

		insertThumb: function(src, target) {
			jQuery('<img>').attr({
				'id': this.uploaderID + '_thumb',
				'src': src
			})
			// .hide()
			.appendTo(target)
				.fadeIn(300);
		},

		updateConfig: function(options) {
			if ('updateThumbnail' in options && 'data' in options) {
				this.updateThumbnail(options.data);
			}
			$.extend(true, this.controller.settings, options);
			this.controller.refresh();
		},

		onFilesBeforeSend: function() {
			if ('beforeSend' in this.options && typeof this.options.beforeSend === 'function') {
				this.options.beforeSend(this.$el);
			}
		},
		onUploadComplete: function(res) {
			if ('success' in this.options && typeof this.options.success === 'function') {
				this.options.success(res);
			}
		}

	});



	/**
	 * USER VIEW
	 */
	/**
	 * User item
	 */
	AE.Views.UserItem = Backbone.View.extend({
		tagName: 'li',
		className: 'et-member',
		template: '',
		/**
		 * this view content model user
		 */
		model: [],
		/**
		 * initialize view
		 */
		events: {
			/**
			 * trigger action on model, link should contain attribute data-name and data-value
			 * name value pair for model example model.set(a.attr('data-name') , a.attr('data-value')) then a.save();
			 */

			'click a.action': 'acting',

			/**
			 * input regular change update model
			 */
			'change .regular-input': 'change',
			/**
			 * ban & unban a user
			 */
			'click .et-act-ban' 		: 'renderBanForm',
			'click .et-act-unban' 		: 'unbanUser',
			/**
			 * change user role, this option should be use in admin setting
			 */
			// 'change select.role-change' : 'changeRole'

		},
		/**
		 * initialize view
		 */
		initialize: function() {
			this.listenTo(this.model, 'change', this.render);
			this.listenTo(this.model, 'destroy', this.remove);

			/**
			 * can override template by change template content, but should keep the template id
			 */
			if ($('#user-item-template').length > 0) {
				this.template = _.template($('#user-item-template').html());
			}

			this.blockUi = new AE.Views.BlockUi();
		},

		/**
		 * render view fill template with model data
		 */
		render: function() {
			if (this.template) {
				this.$el.html(this.template(this.model.toJSON()));
			}
			return this;
		},
		/**
		 * action on model
		 */
		acting: function(e) {
			e.preventDefault();
			var target = $(e.currentTarget),
				action = target.attr('data-act'),
				view 	= this;
			if(action == "confirm"){
				this.model.save('register_status', '', {
					beforeSend: function() {
						view.blockUi.block(view.$el);
					},
					success: function(result, status, xhr) {
						view.blockUi.unblock();
						view.$el.find('a.et-act-confirm').fadeOut();
					}
				});
			}
		},

		/**
		 * update user role
		 */
		change: function(e) {
			console.log(this.model);
			var $target = $(e.currentTarget);
			name = $target.attr('name'),
			val  = $target.val(),
			view = this;

			this.model.save(name, val, {
				beforeSend: function() {
					view.blockUi.block(view.$el);
				},
				success: function(result, status, xhr) {
					view.blockUi.unblock();
				}
			});
		},
		/**
		 * set user_id to form
		 */
		renderBanForm: function(e){
			var form = $('#form_ban_user');
			$('#ban_modal .modal-header .display-name').text(this.model.get('display_name'));
			$('#form_ban_user input[name=id]').val( this.model.get('id') );
		},
		/**
		 * unban a user
		 */
		unbanUser: function(e){
			e.preventDefault();
			var element = $(e.currentTarget).closest('.et-member');
			var view 	= this;

			$.ajax({
				url: ae_globals.ajaxURL,
				type: 'POST',
				data: {
					action: 'et_user_sync',
					method: 'unban',
					content: {
						ID: this.model.get('ID')
					}
				},
				beforeSend: function(){
					view.blockUi.block(element);
				},
				success: function(resp){
					if ( resp.success ){
						// reset model
						view.model.set( resp.data.user );
						// re-render
						view.render();
					}
				},
				complete: function(resp){
					view.blockUi.unblock();
				}
			});
		},
		/**
		 * render user item
		 */
		render: function(){
			var template = _.template( $('#user-item-template').html() );

			// generate html
			this.$el.html(template( this.model.attributes )).attr('data-id', this.model.attributes.ID);

			return this;
		},
	});

	/**
	 * view of users list
	 */
	AE.Views.ListUsers = Backbone.View.extend({
	});
	// USER VIEW

	/**
	 * POST VIEW
	 */
	/**
	 * view of post item
	 */

	AE.Views.PostItem = Backbone.View.extend({
	});
	/**
	 * view of posts list
	 */
	AE.Views.ListPosts = Backbone.View.extend({
	});
	// POST VIEW
})(window.AE, jQuery, Backbone);

// build basic model
(function(AE, $, Backbone) {

	AE.Models.User = Backbone.Model.extend({
		action: 'ae-sync-user',
		initialize: function() {

		}
	});

	AE.Models.Post = Backbone.Model.extend({
		action: 'ae-post-sync',
		initialize: function() {

		}
	});

})(window.AE, jQuery, Backbone);

// build basic collection
(function(AE, $, Backbone) {

	AE.Collections.Users = Backbone.Collection.extend({
		model: AE.Models.User,
		url: 'ae-fetch-users',
		initialize: function() {
			this.paged = 1;
		},

		getAction: function() {
			return {
				action: 'ae-fetch-users',
				paged: this.paged
			};
		}
	});

	AE.Collections.Posts = Backbone.Collection.extend({
		model: AE.Models.Post,
		url: 'ae-fetch-posts',
		initialize: function() {
			this.paged = 1;
		},
		getAction: function() {
			return {
				action: 'ae-fetch-posts',
				paged: this.paged
			};
		}
	});

})(window.AE, jQuery, Backbone);