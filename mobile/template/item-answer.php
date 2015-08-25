<?php
    global $post,$current_user;
    $answer          = QA_Answers::convert($post);
    $question        = QA_Questions::convert(get_post($answer->post_parent));
    $et_post_date    = et_the_time(strtotime($answer->post_date));
    $badge_points    = qa_get_badge_point();
    $category        = !empty($question->question_category[0]) ? $question->question_category[0]->name : __('No Category',ET_DOMAIN);
    $category_link   = !empty($question->question_category[0]) ? get_term_link( $question->question_category[0]->term_id, 'question_category' ) : '#'; 

    $vote_up_class  =  'action vote vote-up ' ;
    $vote_up_class  .= ($answer->voted_up) ? 'active' : '';
    $vote_up_class  .= ($answer->voted_down) ? 'disabled' : ''; 

    $vote_down_class = 'action vote vote-down ';
    $vote_down_class .= ($answer->voted_down) ? 'active' : '';
    $vote_down_class .= ($answer->voted_up) ? 'disabled' : '';

    $qa_answer_comments = get_comments( array( 
            'post_id'       => $answer->ID,
            'parent'        => 0,
            'status'        => 'approve',
            'post_status'   => 'publish',
            'order'         => 'ASC',
            'type'          => 'answer'
        ) );           
?>
<!-- CONTENT ANSWERS -->
<section class="list-answers-wrapper answer-item">
	<div class="container">
        <div class="row">
        	<div class="col-md-12">
            	<div class="content-qna-wrapper">
                    <div class="avatar-user">
                        <a href="<?php echo get_author_posts_url( $question->post_author ); ?>">
                            <?php echo et_get_avatar($answer->post_author, 55) ?>
                        </a>
                    </div>
                    <div class="info-user">
                        <?php qa_user_badge($answer->post_author, true, true) ?>
                    </div>
                    <div class="content-question">
                        <?php if($answer->post_status == "pending"){ ?>
                        <span class="pending-ans"><?php _e("Pending Answer", ET_DOMAIN) ?></span>
                        <?php } ?>                        
                        <div class="details">
                        	<?php the_content(); ?>
                        </div>
                        <div class="info-tag-time">
                        	<span class="time-categories">
                                <?php 
                                    $author = '<a href="'.get_author_posts_url( $answer->post_author ).'">'.$answer->author_name.'</a>';
                                    printf(__("Answered by %s %s.", ET_DOMAIN), $author, $et_post_date)
                                ?>.
                            </span>
                        </div>
                        <div class="vote-wrapper">

                        	<a href="javascript:void(0)" data-name="vote_up" class="<?php echo $vote_up_class ?>">
                        		<i class="fa fa-angle-up"></i>
                        	</a>

                            <span class="number-vote"><?php echo $answer->et_vote_count ?></span>

                            <a href="javascript:void(0)" data-name="vote_down" class="<?php echo $vote_down_class ?>">
                            	<i class="fa fa-angle-down"></i>
                            </a>
                            
                            <?php if($answer->ID == $question->et_best_answer) {?>
                            <a href="javascript:void(0)" data-name="un-accept-answer" class="action answer-active-label best-answers">
                                <i class="fa fa-check"></i><?php _e("Best answer", ET_DOMAIN) ?>
                            </a>
                            <?php } elseif($current_user->ID == $question->post_author) {?>
                            <a href="javascript:void(0)" data-name="accept-answer" class="action answer-active-label pending-answers">
                                <?php _e("Accept", ET_DOMAIN) ?>
                            </a>
                            <?php } ?>

                            <?php if($answer->post_status == "pending" && current_user_can( 'manage_options' )) {?>
                            <a href="javascript:void(0)" data-name="approve" class="action answer-active-label pending-answers">
                                <?php _e("Approve", ET_DOMAIN) ?>
                            </a>
                            <?php } ?>
                            
                        </div>
                    </div>
                </div>
                <!-- SHARE -->
                <div class="share">
                    <ul class="list-share">
                        <li>
                            <a class="share-social" href="javascript:void(0)" rel="popover" data-container="body" data-content='<?php echo qa_template_share($answer->ID); ?>' data-html="true">
                                <?php _e("Share",ET_DOMAIN) ?> <i class="fa fa-share"></i>
                            </a>                            
                        </li>
                        <!-- <li>
                            <a href="javascript:void(0)"><?php _e("Report", ET_DOMAIN) ?><i class="fa fa-flag"></i></a>
                        </li> -->
                        <li>
                            <a href="javascript:void(0)" class="mb-show-comments"><?php _e("Comment", ET_DOMAIN) ?>(<?php echo count($qa_answer_comments) ?>)&nbsp;<i class="fa fa-comment"></i></a>
                        </li>
                    </ul>
                </div>
                <!-- SHARE / END -->
                <!-- COMMENT IN COMMENT -->
                <div class="cmt-in-cmt-wrapper">
                	<ul class="mobile-comments-list">
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
                    </ul>
                    <?php qa_mobile_comment_form($answer, 'answer') ?>
                    <a href="javascript:void(0)" class="add-cmt-in-cmt"><?php _e("Add comment", ET_DOMAIN) ?></a>
                </div>
                <!-- COMMENT IN COMMENT / END -->
            </div>
        </div>
    </div>
</section>
<!-- CONTENT ANSWERS / END -->