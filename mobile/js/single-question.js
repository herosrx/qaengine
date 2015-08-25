(function (Views, Models, $, Backbone) {
	Views.MobileSingleQuestion 	= Backbone.View.extend({
		el : 'body.single-question',
		events : {
			'submit form#insert_answer'     	: 'insertAnswer',
			'click 	a.btn-post-answers' 		: 'showFormReply',
			'click  a#close_reply_form'  		: 'cancelFormReply',
			'change select#move_to_order' 		: 'orderAnswers'
		},
		initialize: function(){
			var question = new Models.Post(currentQuestion);
			this.blockUi	=	new AE.Views.BlockUi();
			this.question 	= 	new Views.PostListItem({
				el: $("#question_content"),
				model: question
			});

			$('.answer-item').each(function(index){
				var element = $(this);
				if ( answersData ) {
					var model	= new Models.Post(answersData[index]);
					var answer	=	new Views.PostListItem({
						el: element,
						model: model
					});
				}
					
			});
			$('.share-social').popover({ html : true});	
		},
		orderAnswers: function(event){
			event.preventDefault();
			var target = $(event.currentTarget);
			if(target.val())
				window.location.href = target.val();
		},
		showFormReply: function(event){
			event.preventDefault();
			var target = $(event.currentTarget);
			target.fadeOut('normal', function() {
				$('form#insert_answer').slideDown().find("textarea").focus();
				$('html, body').animate({ scrollTop: 60000 }, 'slow');
			});

		},
		cancelFormReply: function(event){
			event.preventDefault();
			var target = $(event.currentTarget);
			target.fadeIn('normal', function() {
				$('form.form-post-answers').slideUp();
				$('.btn-post-answers').fadeIn();
			});
		},
		insertAnswer: function(event){
			event.preventDefault();

			var form 	 = $(event.currentTarget),
				$button  = form.find("input.btn-submit"),
				textarea = form.find("textarea"),
				data 	 = form.serializeObject(),
				answers  = parseInt(this.$("span.number").text()),
				view 	 = this;

			if(textarea.val() == '')
				return;

			if(ae_globals.user_confirm && currentUser.register_status == "unconfirm"){
				alert( qa_front.texts.confirm_account );				
				return false;
			}

			answer = new Models.Post();
			answer.set('content',data);
			answer.save('','',{
				beforeSend:function(){
					view.blockUi.block($button);
				},
				success : function (result, status, jqXHR) {
					view.blockUi.unblock();
					if(status.success){
						viewPost = new Views.PostListItem({
							id: result.get('ID'),
							model: result
						});
						textarea.val('').focusout();

						if(ae_globals.pending_answers !== 1){
							$("#answers_main_list").append(viewPost.render(result));
							$("span.answers-count span.number").text(answers+1);
						} else {
							alert(status.msg);
						}

					}
				}
			});			
		}
	});	
})(QAEngine.Views, QAEngine.Models, jQuery, Backbone);