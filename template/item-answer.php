<?php
	global $qa_answer, $qa_question, $qa_answer_comments, $current_user ;

	$question	=	$qa_question;

    get_template_part( 'template/item', 'vote' );
?>

<div class="col-md-9 col-xs-9 q-right-content">
	<!-- control tool for admin, moderate -->
    <ul class="post-controls">
        <?php
        //answer status is pending & current user is admin
        if( $qa_answer->post_status == "pending" && ( current_user_can( 'manage_options' ) || qa_user_can('approve_answer') )  ) {
        ?>
        <li>
            <a href="javascript:void(0);" data-toggle="tooltip" data-original-title="<?php _e("Approve", ET_DOMAIN) ?>" data-name="approve" class="post-edit action">
                <i class="fa fa-check"></i>
            </a>
        </li>
        <?php
        }
        // user can control option or have qa cap edit question/answer
        if($current_user->ID == $qa_answer->post_author || current_user_can( 'manage_options' ) || qa_user_can('edit_answer')) {
        ?>
        <li>
            <a href="javascript:void(0);" data-toggle="tooltip" data-original-title="<?php _e("Edit", ET_DOMAIN) ?>" data-name="edit" class="post-edit action">
                <i class="fa fa-pencil"></i>
            </a>
        </li>
        <?php } ?>
        <?php if( $current_user->ID == $qa_answer->post_author || current_user_can( 'manage_options' ) ){ ?>
        <li>
            <a href="javascript:void(0);" data-toggle="tooltip" data-original-title="<?php _e("Delete", ET_DOMAIN) ?>" data-name="delete" class="post-delete action" >
                <i class="fa fa-trash-o"></i>
            </a>
        </li>
        <?php } ?>
         <?php if(is_user_logged_in() && !$qa_answer->reported){ ?>
        <li>
         <a href="javascript:void(0);" data-toggle="tooltip" data-original-title="<?php _e("Report", ET_DOMAIN) ?>" data-name="report" class="action report" >
              <i class="fa fa-exclamation-triangle"></i>
        </a>
        </li>
         <?php } ?>
    </ul>
    <!--// control tool for admin, moderate -->
    <div class="top-content">
        <?php if($qa_question->et_best_answer == $qa_answer->ID){ ?>
        <span class="answered best-answer">
            <i class="fa fa-check"></i> <?php _e("Best answer", ET_DOMAIN) ?>
        </span>
        <?php } ?>
        <?php if($qa_answer->post_status == "pending"){ ?>
        <span class="answered best-answer">
            <?php _e("Pending", ET_DOMAIN) ?>
        </span>
        <?php } ?>
    </div>
    <div class="clearfix"></div>

    <div class="question-content">
        <?php the_content(); ?>
    </div>

    <div class="post-content-edit collapse">
        <form class="edit-post">
            <input type="hidden" name="qa_nonce" value="<?php echo wp_create_nonce( 'edit_answer' );?>" />
            <div class="wp-editor-container">
                <textarea name="post_content" id="edit_post_<?php echo $qa_answer->ID ?>"></textarea>
            </div>
            <div class="row submit-wrapper">
                <div class="col-md-2 col-xs-2">
                    <button id="submit_reply" class="btn-submit"><?php _e("Update",ET_DOMAIN) ?></button>
                </div>
                <div class="col-md-2 col-xs-2">
                    <a href="javascript:void(0);" data-name="cancel-post-edit" class="action cancel-edit-post">
                        <?php _e("Cancel",ET_DOMAIN) ?>
                    </a>
                </div>
            </div>
        </form>
    </div><!-- END EDIT POST FORM -->

    <div class="row cat-infomation">
    	<!-- Answer owner infomation -->
        <div class="col-md-8 col-xs-8 question-cat">
            <a href="<?php echo get_author_posts_url($qa_answer->post_author); ?>">
                <span class="author-avatar">
                    <?php echo et_get_avatar( $qa_answer->post_author , 30 ); ?>
                </span>
                <span class="author-name"><?php echo $qa_answer->author_name; ?></span>
            </a>
                <?php  qa_user_badge( $qa_answer->post_author ); ?>
                <span class="question-time">
                    <?php printf( __( 'Answered %s.', ET_DOMAIN ), et_the_time(strtotime($qa_answer->post_date))); ?>
                </span>
        </div>
		<!--// Answer owner infomation -->

        <div class="col-md-4 col-xs-4 question-control">
        	<!-- share comment , report -->
            <ul>
                <li>
                    <a class="share-social" href="javascript:void(0);" data-toggle="popover" data-placement="top" data-container="body" data-content='<?php echo qa_template_share($qa_answer->ID); ?>' data-html="true">
                        <?php _e("Share",ET_DOMAIN) ?> <i class="fa fa-share"></i>
                    </a>
                </li>
                <!-- <li>
                    <a href="javascript:void(0)">
                        <?php _e("Report",ET_DOMAIN) ?> <i class="fa fa-flag"></i>
                    </a>
                </li> -->
                <!-- comment count -->
                <li>
                    <a href="#container_<?php echo $qa_answer->ID ?>" class="show-comments <?php if(count($qa_answer_comments)>0) echo 'active';?>">
                        <?php
                        	printf( __( 'Comment(%d) ', ET_DOMAIN ), count($qa_answer_comments));
                        ?> <i class="fa fa-comment"></i>
                    </a>
                </li>
            </ul>
        </div>
        <!--// share comment , report -->
    </div>
    <div class="clearfix"></div>
    <div class="comments-container <?php if(count($qa_answer_comments)==0) echo 'collapse';?>" id="container_<?php echo $qa_answer->ID ?>">
		<div class="comments-wrapper">
		<?php
			/**
			 * render comment loop
			*/
            if(!empty($qa_answer_comments)){
				foreach ($qa_answer_comments as $comment) {
					qa_comments_loop( $comment );
				}
			}
		 ?>
		</div>

        <?php qa_comment_form( $qa_answer, 'answer' ); ?>

    </div>
</div>