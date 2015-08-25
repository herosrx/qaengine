
/**
 * backend languge settings view
 * Use: AE.Models.Options , AE.Views.BlockUi, BackBone
 * required options-view.js
 * this view contain event call ajax to class-ae-languages.php
 * @author Dakachi
*/

(function (Models, Views, $, Backbone) {
	/**
	 * Model language
	*/	
	Models.Language	=	Backbone.Model.extend({
		// action : 'ae-language-sync',
		initialize : function () {

		}
	});
	/**
	 * view control how user set language for web site
	 * also you can add new language as you want	
	*/
	Views.LanguageList	=	Backbone.View.extend({

		template : _.template(	'<li><a class="actives" href="et-change-language" rel="{{= lang_name }}">' +
								'{{= lang_name }}</a></li>'
							),
		events : {
			// catch event add lang button and open add new language form
			'click 	button.add-lang' 			: 'onAdd',
			// catch event user enter input to add new language
			'keyup  input.input-new-lang'		: 'add',
			// catch event user click on language name to set site language
			'click 	ul.list-language a'  		: 'changeLanguage',
			// call load translate form when user select a language name
			'change select#base-language'		: 'loadTranslate',
			//  update translated string
			'click 	button#save-language' 		: 'saveTranslation',
			// mark which string is translated
			'change textarea'					: 'markLanguageChange'
		},

		initialize : function () {
			// new Model Language to handle language change
			this.language	=	new Models.Language();
			// new blockui to block element when call ajax
			this.blockUi	=	new Views.BlockUi();
		},

		/*
		 * open form the add new language
		*/
		onAdd : function (e) {
			e.preventDefault();
			var $target		=	$(e.currentTarget),
				$container 	= 	$target.parent();

			$target.fadeOut(300, function(){
				$container.find('.input-new-lang').fadeIn(300).focus();
			});
		},

		/**
		 * add new language, fire an ajax request to server and create new language file
		*/
		add : function(e){
			var $target 		= $(e.currentTarget),
				$containter 	= $target.parent(),
				view			= this;

			view.language.action	=	'ae-language-sync';
			if ( e.which == 13 ) { // save the new lang
				view.language.save( 'lang_name', $target.val(), 
								{
									beforeSend : function(){
										view.blockUi.block( $containter ); // block the input field
									},
									success : function(result, res, xhr){
										view.blockUi.unblock();
										// update view translation
										
										if(res.success) {					
											//$container.find('.active').removeClass('active');
											view.$('ul.list-language').prepend( view.template( res.data ) );
											view.$('#base-language').append('<option value="'+res.data.lang_name+'">'+res.data.lang_name+'</option>');
											// hide the form
											view.$('li.new-language button').show();
											view.$('li.new-language input').val('').hide();

										} else {
											alert('Fail to add new language');
										}
									}

								} );
			}

			if( e.which == 27 ){ // escape, cancel the new lang form
				view.$('li.new-language input').val('').fadeOut(300, function(){
					view.$('li.new-language button').fadeIn(300);
				});
			}

		},

		/**
		 * ajax request to change site language and refesh site to view the change
		*/
		changeLanguage : function (e) {
			e.preventDefault();
			var $target	=	$(e.currentTarget),
				name	=	$target.attr('rel'),
				view	=	this;

			view.language.action	=	'ae-language-change';

			view.language.save('lang_name', name, {
				beforeSend : function(){
					view.blockUi.block($target);
				},
				success : function(result, response, xhr){
					view.blockUi.unblock();
					if( response.success ) {
						window.location.reload();
					}				
				}
			});
		},

		/**
		 * trigger ajax to request string to translate 
		*/
		loadTranslate : function (e) {
			var $target		=	$(e.currentTarget),
				$form 		=	this.$('div#translate-form'),
				lang_name	=	$target.val(),
				view		=	this;

			/**
			 * invalide language name 
			*/
			if ( lang_name == '' ) return false;

			$.ajax ({
				url  : ae_globals.ajaxURL,
				type : 'post',
				data : {
					lang_name : lang_name,
					action    : 'et-load-translation-form'
				}
				,
				beforeSend : function(){
					view.blockUi.block( $form );
				},
				success : function(reponse){
					// update view translation
					view.blockUi.unblock()
					if(reponse.success) {
						$form.html (reponse.data);

					} else {

					}
				}
			});	
		},

		/**
		 * mark textarea has changed
		*/
		markLanguageChange : function (e) {
			e.preventDefault ();
			var $target		=	$(e.currentTarget),
				$container	=	$target.parent();

			$target.addClass('changed');
			$container.find('input').addClass('changed');
			/**
			 * autor save if use have changed 20 strings
			*/
			if( $('textarea.changed').length == 20 ) this.saveTranslation();
		},

		/**
		 * save translation string
		*/
		saveTranslation : function (event) {
			event.preventDefault ();
			var button 		= 	this.$('#save-language'),
				form 		=	this.$('#translate-form'),
				lang_name	=	this.$('select#base-language').val (),
				view 		= 	this,
				data		= 	'';
			if(view.timeout) {
				clearTimeout (view.timeout);
			}
			/**
			 * find changed field and add it to data
			*/
			form.find('.changed').each (function () {			
				data 	= 	data + $(this).attr('name')+'='+encodeURIComponent($(this).val())+'&';			
			});
			/**
			 * ajax call to save changed field
			*/
			$.ajax ({
				url  : ae_globals.ajaxURL,
				type : 'post',
				data : 
					// append action to data string
					data + 'action=et-save-language&lang_name='+lang_name				
				,
				beforeSend : function(){
					this.isSaveingLanguage = true;
					view.blockUi.block(button);
				},
				success : function(response){
					view.blockUi.unblock();
					// update view
					if(response.success) {
						view.$('.changed').removeClass('changed');
						button.parents('.btn-language').append('<span class="icon form-icon" data-icon="3"></span>');
						// $target.parent().append('<span class="icon form-icon" data-icon="3"></span>');
						view.timeout	=	setTimeout(function() { view.$('.form-icon').remove(); } , 2000);
					}else {
						button.parents('.btn-language').append('<span class="icon form-icon" data-icon="!"></span>');
						// $target.parent().append('<span class="icon form-icon" data-icon="3"></span>');
						view.timeout	=	setTimeout(function() { view.$('.form-icon').remove(); } , 2000);
					}						
				}
			});	
		}

	});

})( window.AE.Models, window.AE.Views, jQuery, Backbone );