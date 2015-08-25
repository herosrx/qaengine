/**
 * render option view to control option settings, badge settings
*/

(function (Models, Views, $, Backbone) {

	/**
	 * model option
	*/
	Models.Badges = Backbone.Model.extend({
		action	: 'ae-badge-sync',
		defaults: function() {
			return {
				name	: "option_name",
				value	: "option_value"
			};
		}
	});

	$(document).ready(function() {

		var options	=	new Models.Options();

		if($('#settings').length > 0 ) {
			new Views.Options({el : '#settings' , model : options  });
		}
		if($('#badge').length > 0 ) {
			new Views.Options({el : '#badge' , model : new Models.Badges() });
		}

		new Views.LanguageList({ el : $('#language-settings'), model : options });

		if(typeof Views.UserList !== 'undefined') {
			new Views.UserList( { el : $('.user-container') } );
		}

		$('.color-picker').each (function () {
			var $this	=	$(this);
			$this.ColorPicker({
				onChange: function(hsb, hex, rgb ) {
					$this.val('#'+ hex);
					//$this.css('color' , '#'+hex );
					$this.css('background' , '#'+hex );
					// $this.ColorPickerHide();
				},
				onBeforeShow: function () {
					$(this).ColorPickerSetColor(this.value);
				}
			});
		});
	});

})( window.AE.Models, window.AE.Views, jQuery, Backbone );