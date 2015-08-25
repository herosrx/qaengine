<script type="text/template" id="mobile_answer_item">

	<div class="container">
        <div class="row">
        	<div class="col-md-12">
            	<div class="content-qna-wrapper">
                    <div class="avatar-user">
                        <a href="javascript:void(0)">
                            {{= avatar }}
                        </a>
                    </div>
                    <div class="info-user">
                        {{= user_badge }}
                    </div>
                    <div class="content-question">
                        <div class="details">
                        	{{= content_filter }}
                        </div>
                        <div class="info-tag-time">
                        	<span class="time-categories">
                                <?php _e("Answered by ", ET_DOMAIN) ?><a href="{{= author_url }}">{{= author_name }}</a> {{= human_date }}.
                            </span>
                        </div>
                        <div class="vote-wrapper">

                        	<a href="javascript:void(0)" data-name="vote_up" class="action vote vote-up">
                        		<i class="fa fa-angle-up"></i>
                        	</a>

                            <span class="number-vote">0</span>

                            <a href="javascript:void(0)" data-name="vote_down" class="action vote vote-down">
                            	<i class="fa fa-angle-down"></i>
                            </a>
                            <# if(currentUser.ID == parent_author) { #>
                            <a href="javascript:void(0)" data-name="accept-answer" class="action answer-active-label pending-answers"><?php _e("Accept", ET_DOMAIN) ?></a>
                            <# } #>
                        </div>
                    </div>
                </div>
                <!-- SHARE -->
                <div class="share">
                    <ul class="list-share">
                        <li>
                            <a class="share-social" href="javascript:void(0)" data-toggle="popover" data-placement="top" data-container="body" data-content="<ul class=&quot;socials-share&quot;><li><a href=&quot;https://www.facebook.com/sharer/sharer.php?u={{= guid }}&amp;t={{= post_title }}&quot; target=&quot;_blank&quot; class=&quot;btn-fb&quot;><i class=&quot;fa fa-facebook&quot;></i></a></li><li><a target=&quot;_blank&quot; href=&quot;http://twitter.com/share?text={{= post_title }}&amp;url={{= guid }}&quot; class=&quot;btn-tw&quot;><i class=&quot;fa fa-twitter&quot;></i></a></li><li class=&quot;ggplus&quot;><a target=&quot;_blank&quot;  href=&quot;https://plus.google.com/share?url={{= guid }}&quot; class=&quot;btn-gg&quot;><i class=&quot;fa fa-google-plus&quot;></i></a></li></ul>" data-html="true">
                                <?php _e("Share",ET_DOMAIN) ?> <i class="fa fa-share"></i>
                            </a>
                        </li>
                        <!--<li>
                            <a href="javascript:void(0)"><?php _e("Report", ET_DOMAIN) ?><i class="fa fa-flag"></i></a>
                        </li>-->
                        <li class="active-comment">
                            <a href="javascript:void(0)" class="mb-show-comments"><?php _e("Comment", ET_DOMAIN) ?>(0)&nbsp;<i class="fa fa-comment"></i></a>
                        </li>
                    </ul>
                </div>
                <!-- SHARE / END -->
                <!-- COMMENT IN COMMENT -->
                <div class="cmt-in-cmt-wrapper">
                	<ul class="mobile-comments-list"></ul>
                    <form class="form-post-answers create-comment collapse">
                        <input type="hidden" name="qa_nonce"        value="<?php echo wp_create_nonce( 'insert_comment' );?>" />
                        <input type="hidden" name="comment_post_ID" value="{{= ID  }}" />
                        <input type="hidden" name="comment_type"    value="answer" />
                        <input type="hidden" name="user_id"         value="{{= post_author  }}" />                     
                        <textarea name="post_content" id="post_content" rows="4" placeholder="<?php _e("Type your comment", ET_DOMAIN)?> "></textarea>
                        <input type="submit" class="btn-submit" name="submit" id="" value="<?php _e("Add comment", ET_DOMAIN)?>">
                        <a href="javascript:void(0)" class="close-form-post-answers"><?php _e("Cancel", ET_DOMAIN)?></a>
                    </form>                    
                    <a href="javascript:void(0)" class="add-cmt-in-cmt"><?php _e("Add comment", ET_DOMAIN) ?></a>
                </div>
                <!-- COMMENT IN COMMENT / END -->
            </div>
        </div>
    </div>

</script>