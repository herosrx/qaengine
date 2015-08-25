<?php
    global $post;
    $answer          = QA_Answers::convert($post);
    $question        = QA_Questions::convert(get_post($answer->post_parent));
    $et_post_date    = et_the_time(strtotime($answer->post_date));
    $badge_points    = qa_get_badge_point();
    $category        = !empty($question->question_category[0]) ? $question->question_category[0]->name : __('No Category',ET_DOMAIN);
    $category_link   = !empty($question->question_category[0]) ? get_term_link( $question->question_category[0]->term_id, 'question_category' ) : '#';    
?>
<li <?php post_class( 'answer-item question-item' ); ?> data-id="<?php echo $post->ID ?>">
    <div class="col-md-8 q-left-content">
        <div class="q-ltop-content title-answer-style">
            <a href="<?php echo get_permalink($question->ID); ?>" class="question-title">
                <?php the_title() ?>
            </a>
        </div>
        <div class="q-lbtm-content">
            <div class="question-cat">
                <span class="author-avatar">
                <?php echo et_get_avatar( $answer->post_author, 30 ); ?>
                </span>
                <?php  qa_user_badge( $answer->post_author ); ?>
                <span class="question-time">
                    <?php printf( __( 'Asked %s in', ET_DOMAIN ),$et_post_date); ?>
                </span>
                <span class="question-category">
                    <a href="<?php echo $category_link ?>"><?php echo $category ?>.</a>
                </span>
                <ul class="question-tags">
                    <?php
                        foreach ($question->qa_tag as $tag) {
                    ?>
                    <li>
                        <a class="q-tag" href="<?php echo get_term_link($tag->term_id, 'qa_tag'); ?> ">
                            <?php echo $tag->name; ?>
                        </a>
                    </li>
                    <?php } ?>
                </ul>
            </div>   
        </div>
        <div class="quote-answer-style">
            <div>
                <span class="icon-quote"></span><?php the_content() ?>
            </div>
            <?php if( $answer->ID == $question->et_best_answer) {?>
            <p class="alert-stt-answer-style">
                <i class="fa fa-check-circle"></i>
                <?php _e("This answer accepted by", ET_DOMAIN) ?> <a href="javascript:void(0)"><?php the_author_meta( 'display_name', $question->post_author ); ?></a>. 
                <?php echo et_the_time( strtotime( get_post_meta( $answer->ID, 'et_is_best_answer', true ) ) );  ?>
                <?php printf(__("Earned %d points.", ET_DOMAIN), $badge_points->a_accepted) ?>
            </p>
            <?php } ?>
        </div>
    </div><!-- end left content -->
    <div class="col-md-4 q-right-content">
        <ul class="question-statistic">
            <li>
                <span class="question-views">
                    <?php echo $question->et_view_count ?>
                </span>
                <?php _e("views",ET_DOMAIN) ?>
            </li>
            <li class="<?php if($question->et_best_answer) echo 'active'; ?>">
                <span class="question-answers">
                    <?php echo $question->et_answers_count ?> 
                </span>
                <?php _e("answers",ET_DOMAIN) ?>
            </li>
            <li>
                <span class="question-votes">
                    <?php echo $question->et_vote_count ?> 
                </span>
                <?php _e("votes",ET_DOMAIN) ?>
            </li>
        </ul>
    </div><!-- end right content -->                    
</li>