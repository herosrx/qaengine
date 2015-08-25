<script type="text/template" id="answer_item">
	
	    <div class="col-md-2 col-xs-2 vote-block">
	        <ul>
	            <li>
	            	<a href="javascript:void(0)" data-name="vote_up" class="action vote vote-up img-circle">
	            		<i class="fa fa-chevron-up"></i>
	            	</a>
	            </li>
	            <li>
	            	<span class="vote-count">{{= et_vote_count }}</span>
	            </li>
	            <li>
	            	<a href="javascript:void(0)" data-name="vote_down" class="action vote vote-down img-circle">
	            		<i class="fa fa-chevron-down"></i>
	            	</a>
	            </li>
	            <# if(currentUser.ID == parent_author) { #>
	            <li>
	            	<a href="javascript:void(0)" data-name="accept-answer" class="action accept-answer img-circle">
	            		<i class="fa fa-check"></i>
	            	</a>
	            </li>
	            <# } #>
	        </ul>
	    </div>
	    <div class="col-md-9 col-xs-9 q-right-content">
            <ul class="post-controls">
                <li>
                    <a href="javascript:void(0)" data-name="edit" class="post-edit action">
                        <i class="fa fa-pencil"></i>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)" data-name="delete" class="post-delete action" >
                        <i class="fa fa-trash-o"></i>
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0)" data-name="report" class="report action" >
                        <i class="fa fa-exclamation-triangle"></i>
                    </a>
                </li>
            </ul>		    
	        <div class="clearfix"></div>
	        <div class="question-content">
	            {{= content_filter }}
	        </div>
	        <div class="post-content-edit collapse">
	            <form class="edit-post">
	                <input type="hidden" name="qa_nonce" value="<?php echo wp_create_nonce( 'edit_answer' );?>" />
	                <div class="wp-editor-container">
	                    <textarea name="post_content" id="edit_post_{{= ID }}"></textarea>
	                </div>
	                <div class="row submit-wrapper">
	                    <div class="col-md-2">
	                        <button id="submit_reply" class="btn-submit"><?php _e("Update",ET_DOMAIN) ?></button>
	                    </div>
	                    <div class="col-md-2">
	                        <a href="javascript:void(0)" data-name="cancel-post-edit" class="action cancel-edit-post"><?php _e("Cancel",ET_DOMAIN) ?></a>
	                    </div>                                        
	                </div>                                    
	            </form>
	        </div><!-- END EDIT POST FORM --> 		        
	        <div class="row cat-infomation">
	            <div class="col-md-8 col-xs-8 question-cat">
	                <span class="author-avatar">
	                    {{= avatar }}
	                </span>
	                <span class="author-name">{{= author_name }}</span>
	                {{= user_badge }}
	                <span class="question-time">
	                    <?php _e("Answered", ET_DOMAIN) ?> {{= human_date }}
	                </span>
	            </div>
	            <div class="col-md-4 col-xs-4 question-control">
	                <ul>
	                    <li>
							<a class="share-social" href="javascript:void(0)" data-toggle="popover" data-placement="top" data-container="body" data-content="<ul class=&quot;socials-share&quot;><li><a href=&quot;https://www.facebook.com/sharer/sharer.php?u={{= guid }}&amp;t={{= post_title }}&quot; target=&quot;_blank&quot; class=&quot;btn-fb&quot;><i class=&quot;fa fa-facebook&quot;></i></a></li><li><a target=&quot;_blank&quot; href=&quot;http://twitter.com/share?text={{= post_title }}&amp;url={{= guid }}&quot; class=&quot;btn-tw&quot;><i class=&quot;fa fa-twitter&quot;></i></a></li><li class=&quot;ggplus&quot;><a target=&quot;_blank&quot;  href=&quot;https://plus.google.com/share?url={{= guid }}&quot; class=&quot;btn-gg&quot;><i class=&quot;fa fa-google-plus&quot;></i></a></li></ul>" data-html="true">
		                        <?php _e("Share",ET_DOMAIN) ?> <i class="fa fa-share"></i>
		                    </a>	                        
	                    </li>
	                    <!--<li>
	                        <a href="javascript:void(0)">
	                            <?php _e("Report",ET_DOMAIN) ?> <i class="fa fa-flag"></i>
	                        </a>
	                    </li>-->
	                    <li>
	                        <a href="#container_{{= ID }}" class="show-comments">
	                            {{= comments }} <i class="fa fa-comment"></i>
	                        </a>
	                    </li>
	                </ul>
	            </div>                   
	        </div>
	        <div class="clearfix"></div>
	        <div class="comments-container collapse" id="container_{{= ID }}">
	        	<div class="comments-wrapper"></div>
	            <a class="add-comment" data-id="{{= ID }}" href="javascript:void(0)"><?php _e("Add Comment",ET_DOMAIN) ?></a>
	            <div class="clearfix"></div>
	            <form class="child-reply" method="POST">
	                <input type="hidden" name="qa_nonce" value="<?php echo wp_create_nonce( 'insert_comment' );?>" />
	                <input type="hidden" name="comment_post_ID" value="{{= ID  }}" />  
	                <input type="hidden" name="comment_type"    value="answer" />            
	                <input type="hidden" name="user_id" value="{{= post_author  }}" />              
		            <div id="editor_wrap_{{= ID }}" class="child-answer-wrap collapse">
		            	<div class="wp-editor-container">
		            		<textarea name="post_content" id="insert_answer_{{= ID }}"></textarea>
		            	</div>
			          	<div class="row submit-wrapper">
		                    <div class="col-md-3 col-xs-3">
		                        <button id="submit_reply" class="btn-submit">
		                            <?php _e("Add comment",ET_DOMAIN) ?>
		                        </button>
		                    </div>
			                <div class="col-md-9 col-xs-9">
			                    <a href="javascript:void(0)" class="hide-comment"><?php _e("Cancel",ET_DOMAIN) ?></a>
			                </div>
		                </div>
		            </div>
	            </form>            
	        </div>
	    </div>
	
</script>