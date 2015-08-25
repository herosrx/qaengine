(function (Models, Views, $, Backbone) {
	Views.Single_Question = Backbone.View.extend({
		el: "body.single-question",
		events : {
			'submit form#form_reply'  : 'insertAnswer',
		},
		initialize : function () {
			var question 	= new Models.Post(currentQuestion);
			this.blockUi	=	new AE.Views.BlockUi();
			this.question 	= 	new Views.PostListItem({
				el: $("#question_content"),
				model: question
			});

			$('.answer-item').each(function(index){
				var element = $(this);
				if ( typeof answersData !== "undefined" ) {
					var model	= new Models.Post(answersData[index]);
					var answer	=	new Views.PostListItem({
						el: element,
						model: model
					});
				}

			});
			$('.comment-item').each(function(index){
				var element = $(this);
				if ( typeof commentsData !== "undefined" ) {
					var model	= new Models.Post(commentsData[index]);
					var comment	=	new Views.CommentItem({
						el: element,
						model: model
					});
				}

			});

			this.initBoostrapJS();
			//render code
			SyntaxHighlighter.all();
		},

		initBoostrapJS : function() {
			$('html').click(function(e) {
			    $('.vote-block a, .add-comment').popover('hide');
			})
			$('.vote-block li,a.action').tooltip();
			$('.vote-block a, .add-comment').popover();
			$('.share-social').popover({ html : true});
		},

		insertAnswer: function(event){
			event.preventDefault();

			var form = $(event.currentTarget),
				$button = form.find("button.btn-submit"),
				data    = form.serializeObject(),
				answers = parseInt($("span.answers-count span.number").text()),
				view    = this;

			if(currentUser.ID == 0){
				if(typeof this.authModal === "undefined"){
					this.authModal = new Views.AuthModal({
						el: $('#login_register')
					});
				}
				this.authModal.openModal();
			}

			if(ae_globals.user_confirm && currentUser.register_status == "unconfirm"){
				AE.pubsub.trigger('ae:notification', {
					msg: qa_front.texts.confirm_account,
					notice_type: 'error',
				});
				return false;
			}

			if(tinymce.activeEditor.getContent() == '')
				return;

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

						//reset tinymce to blank
						tinymce.activeEditor.setContent('');
						var caps = currentUser.cap;
						if(ae_globals.pending_answers !== 1 || caps['approve_answer'] == true){

							$("#answers_main_list").append(viewPost.render(result));
							SyntaxHighlighter.highlight();
							$('.share-social').popover({ html : true});
							$("span.answers-count span.number").text(answers+1);

							// add status followed after insert answer
							var target = view.$el.find('ul.post-controls li.follow-question a.follow');
							target.attr('data-original-title', 'Unfollow').attr('data-name', 'unfollow').removeClass('follow').addClass('followed');
							target.find('i').removeClass('fa-plus-square').addClass('fa-minus-square');
						} else {
							AE.pubsub.trigger('ae:notification', {
								msg: status.msg,
								notice_type: 'success',
							});
						}
					} else {
						AE.pubsub.trigger('ae:notification', {
							msg: status.msg,
							notice_type: 'error',
						});
					}
				}
			});
		}
	});
})( window.QAEngine.Models, window.QAEngine.Views, jQuery, Backbone );