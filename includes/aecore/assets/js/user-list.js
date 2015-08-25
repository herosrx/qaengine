/**
 * backend user, control user list in user manage list
 * search user by name
 * filter user by role
 * filter user by another data
 * use Collections.Users , View.UserItem, Models.User
 * use Views.BlockUi add view block loading
 */
(function(Models, Views, Collections, $, Backbone) {
	Views.UserList = Backbone.View.extend({
		events: {
			'click .load-more': 'loadMore',
			'change input.user-search': 'search',
			'change select.et-input': 'search',
			'submit .et-member-search form': 'submit',
			'change input.point' : 'changePoints',
			'submit #form_ban_user' : 'banUser',
		},

		initialize: function() {
			_.bindAll(this, 'addAll', 'addOne');

			var view = this;
			/**
			 * init collection data
			 */
			if ($('#ae_users_list').length > 0) {
				var users = JSON.parse($('#ae_users_list').html());
				this.Users = new Collections.Users(users.users);
				this.pages = users.pages;
			} else {
				this.Users = new Collections.Users();
			}

			this.paged = 1;

			this.user_view = [];
			/**
			 * init UserItem view
			 */
			this.Users.each(function(user, index, col) {
				var el = $('li.et-member').eq(index);
				view.user_view.push(new Views.UserItem({
					el: el,
					model: user
				}));
			});

			// bind event to collection users
			this.listenTo(this.Users, 'add', this.addOne);
			this.listenTo(this.Users, 'reset', this.addAll);
			this.listenTo(this.Users, 'all', this.render);

			this.blockUi = new Views.BlockUi();

		},
		/**
		 * add one
		 */
		addOne: function(user) {
			console.log('add one');
			var userItem = new Views.UserItem({
				model: user
			});
			this.user_view.push(userItem);

			this.$('ul.users-list').append(userItem.render().el);
		},

		/**
		 * add all
		 */
		addAll: function() {
			for (var i = 0; i < this.user_view.length - 1; i++) {
				// this.user_view[i].$el.remove();
				this.user_view[i].remove();
			}

			this.$('ul').html('');
			this.user_view = [];
			this.Users.each(this.addOne, this);
		},
		/**
		 * build ajax params for ajax
		 */
		buildParams: function(reset) {

			var view = this,
				keywork = this.$('input.user-search').val(),
				loadmore = view.$('.load-more'),
				role = this.$('select.et-input').val(),
				// get ajax params from AE globals
				ajaxParams = AE.ajaxParams;

			if (!reset) {
				$target = this.$('.load-more');
			} else {
				$target = this.$('ul');
			}

			ajaxParams.success = function(result, status, jqXHR) {
				var data = result.data;
				view.blockUi.unblock();
				if (result.pages <= result.paged) {
					loadmore.hide();
				} else {
					loadmore.show();
				}

				if (reset) view.Users.reset();
				view.Users.set(data);

				if (data.length == 0) view.$('ul').append('<li class="user-not-found">' + result.msg + '</li>');

			}

			ajaxParams.beforeSend = function() {
				view.paged++;
				view.blockUi.block($target);
			}
			/**
			 * filter param
			 */
			ajaxParams.data = {
				search: keywork,
				paged: view.paged
			};
			if (role != '') ajaxParams.data.role = role;

			ajaxParams.data.action = 'ae-fetch-users';

			return ajaxParams
		},

		/**
		 * load more user event
		 */
		loadMore: function(event) {
			var view = this,
				$target = $(event.currentTarget);


			var ajaxParams = this.buildParams(false)

			$.ajax(ajaxParams);

		},
		/**
		 * search user
		 */
		search: function(e) {
			this.paged = 0;
			var ajaxParams = this.buildParams(true);
			$.ajax(ajaxParams);
		},

		submit: function(event) {
			event.preventDefault();
		},

		banUser: function(event){
			event.preventDefault();
			var form = $(event.currentTarget),
			id       = form.find('input[name=id]').val(),
			btn      = form.find('button[type="submit"]'),
			view     = this,
			params   = {
				url: ae_globals.ajaxURL,
				type: 'post',
				data: {
					action: 'et_user_sync',
					method: 'ban',
					content: form.serialize()
				},
				beforeSend: function(){
					view.blockUi.block(btn);
				},
				success: function(resp){
					//reset form
					form[0].reset();
					if ( resp.success ){
						// re-render
						_.each( view.user_view, function(e){
							if ( e.model.get('id') == id ){
								e.model.set( resp.data.user );
								// perform render
								e.render();
							}
						});

						// close modal
						$('#ban_modal').modal('hide');
					}
				},
				complete: function(){
					view.blockUi.unblock();
				}
			}
			$.ajax(params);
		}

	});

})(window.AE.Models, window.AE.Views, window.AE.Collections, jQuery, Backbone);