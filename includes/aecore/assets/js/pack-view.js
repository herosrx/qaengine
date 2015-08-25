/**
 * control AE Pack settings
 * use AE.Collections.Posts, AE.Models.Post
*/
(function (Models, Views, Collections, $, Backbone) {

	Models.Pack			=	Backbone.Model.extend({
		action : 'ae-pack-sync',
        initialize : function () {

        }
	});

	Collections.Packs	=	Backbone.Collection.extend({
		model : Models.Pack,
	});

	Views.PackItem		=	Backbone.View.extend({

		tagName 	: 'li',
		className 	: 'pack-item',
		template 	: '',

		events : {
			'click a.act-edit' : 'editPlan',
			'click a.act-del'  : 'removePlan'
		},

		initialize: function(){
			_.bindAll(this, 'render', 'fadeOut' );
			//console.log(this.$el);
			this.template	=	_.template( $('#ae-post-item').html() );

			this.model.bind('updated', this.render, this );
			this.model.on('change', this.render, this );
			this.model.bind('destroy', this.fadeOut, this);

			if( this.$('#confirm_delete_pack').length > 0 )
				this.confirm_delete_pack	=	this.$('#confirm_delete_pack').val();
			else
				this.confirm_delete_pack	= 'Are you sure you want to delete this badge?';

			console.log(this.confirm_delete_pack);
		},

		render : function(){
			this.$el.html( this.template( this.model.toJSON()) ).attr('data', this.model.id ).attr('id', 'payment_' + this.model.id);
			return this;
		},

		blockItem : function(){
			this.blockUi = new Views.BlockUi();
			this.blockUi.block(this.$el);
		},

		unblockItem: function(){
			this.blockUi.unblock();
		},

		editPlan : function(event){
			event.preventDefault();

			if ( this.editForm && this.$el.find('.engine-payment-form').length > 0 ){
				this.editForm.closeForm(event);
			}
			else{
				this.editForm = new Views.PackForm({ model: this.model, parent: this.$el });
			}

		},

		removePlan : function(event){
			// ask user if he really want to delete
			if ( !confirm(this.confirm_delete_pack) ) return false;
			
			event.preventDefault();
			var view = this;

			// call delete request
			this.model.destroy({
				beforeSend: function(){
					view.blockItem();
				},
				success: function(resp){
					view.unblockItem();
				}
			});
		},

		fadeOut : function(){
			this.$el.fadeOut(function(){ $(this).remove(); });
		}
	});
	
	Views.PackForm		=	Backbone.View.extend({
		tagName : 'div',
		events : {
			'submit form.edit-plan' : 'savePlan',
			'click .cancel-edit' 	: 'cancel'
		},
		template : '', //_.template( $('#template_edit_form').html() ),
		render : function(){
			this.$el.html( this.template( this.model.toJSON() ) );
			return this;
		},

		initialize : function(options){

			_.bindAll(this, 'closeForm');
			// apply template for view
			if ( $('#template_edit_form').length > 0 )
				this.template = _.template( $('#template_edit_form').html() );
			this.options	=	options;

			this.model.bind('update', this.closeForm, this);
			this.appear();

			this.blockUi	=	new Views.BlockUi();

			var $color	=	this.$('.color-picker')
			$color.ColorPicker({
				onChange: function(hsb, hex, rgb ) {
					$color.val('#'+ hex);
					//$this.css('color' , '#'+hex );
					$color.css('background' , '#'+hex );
					// $this.ColorPickerHide();
				},
				onBeforeShow: function () {
					$(this).ColorPickerSetColor(this.value);
				}
			});
		

		},

		appear : function(){
			this.render().$el.hide().appendTo( this.options.parent ).slideDown();
		},

		savePlan : function(event) {
			event.preventDefault();
			var $form 	= $(event.currentTarget),
				button 	= $form.find('.engine-submit-btn'),
				view 	= this;

			if ( $form.valid() ){
				/**
				 * get name value pair input set to model
				*/
				$form.find('input,textarea,select').each(function(){
					view.model.set( $(this).attr('name') , $(this).val() );
				});

				view.model.save('', '', {
					beforeSend : function(){
						view.blockUi.block($form);
					},
					success : function(result, status, xhr){
						view.blockUi.unblock();
						view.closeForm();
					}
				});				
			}
		},

		cancel : function(event){
			event.preventDefault();
			this.closeForm();
		},
		closeForm : function(){
			this.$el.slideUp( 500, function(){ $(this).remove(); });
		}
	});


	Views.Pack = Backbone.View.extend({
		
		events : {
			'submit form.add-pack-form' : 'submitPaymentForm'
		},

		initialize : function () {

			_.bindAll(this, 'addOne', 'addAll', 'render');

			var view 	=	this;
			/**
			 * init collection data
			*/
			if($('#ae_pack_list').length > 0 ) {
				var packs	=	JSON.parse( $('#ae_pack_list').html() );
				this.Packs	=	new Collections.Packs( packs );
			} else {
				this.Packs	=	new Collections.Packs();
			}

			this.pack_view	=	[];
			/**
			 * init UserItem view
			*/
			this.Packs.each(function (pack, index, col ){
				var el	=	$('li.pack-item').eq(index);
				view.pack_view.push( new Views.PackItem( { el: el, model : pack } ) );
			});

			// bind event to collection users
			this.listenTo(this.Packs, 'add', this.addOne);
			this.listenTo(this.Packs, 'reset', this.addAll);
			this.listenTo(this.Packs, 'all', this.render);

			this.blockUi	=	new Views.BlockUi();

		},
		/**
		 * add one 
		*/
		addOne : function (pack) {
			// console.log('add one');
			var Item	=	new Views.PackItem({ model : pack });
			this.pack_view.push(Item);

			this.$('ul.pay-plans-list').append(Item.render().el);
		}, 

		/**
		 * add all
		*/
		addAll : function () {
			
			for( var i = 0 ; i < this.pack_view.length - 1 ; i ++ ) {
				this.pack_view[i].remove();
			}

			this.$('ul').html('');
			this.pack_view	=	[];
			this.Packs.each(this.addOne, this);
		},

		// event handle: Submit Pack form
		submitPaymentForm : function(event){
			event.preventDefault();

			var $form 	= $(event.currentTarget),
				button 	= $form.find('.engine-submit-btn'),
				view 	= this;

			if ( $form.valid() ){
				var model = new Models.Pack();

				$form.find('input,textarea,select').each(function(){
					model.set( $(this).attr('name') , $(this).val() );
				});


				model.save('', '', {
					beforeSend : function(){
						view.blockUi.block($form);
					},
					success : function(result, status, xhr){
						view.blockUi.unblock();
						$form.find('input,textarea').val('');
						view.Packs.add(result);
					}
				});
				
			}
		}

	});

	$(document).ready(function() {
		new Views.Pack({ el : $('#badge') });
	});
})( window.AE.Models, window.AE.Views, window.AE.Collections,  jQuery, Backbone );