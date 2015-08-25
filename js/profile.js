(function(Views, Models, $, Backbone) {

	Views.UserProfile = Backbone.View.extend({
		el: 'body',

		events: {
			'click a.show-edit-form': 'openEditProfileForm',
			'click .inbox': 'openContactModal',
		},

		initialize: function() {
			this.user = new Models.User(currentUser);
			this.blockUi = new AE.Views.BlockUi();
		},
		openEditProfileForm: function(event) {
			event.preventDefault();
			var modal = new Views.EditProfileModal({
				el: $("#edit_profile")
			});
			modal.openModal();
		},
		openContactModal: function(event) {
			event.preventDefault();
			var modal = new Views.ContactModal({
				el: $("#contactFormModal")
			});
			modal.openModal();
		}
	});

})(QAEngine.Views, QAEngine.Models, jQuery, Backbone);