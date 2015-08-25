<?php
    global $post;
    $question        = QA_Questions::convert($post);
    $et_post_date    = et_the_time(strtotime($question->post_date));
    $category        = !empty($question->question_category[0]) ? $question->question_category[0]->name : __('No Category',ET_DOMAIN);
    $category_link   = !empty($question->question_category[0]) ? get_term_link( $question->question_category[0]->term_id, 'question_category' ) : '#';
?>
<li <?php post_class( 'question-item pending-question' );?> data-id="<?php echo $post->ID ?>">
    <div class="col-md-8 col-xs-8 q-left-content">
        <div class="q-ltop-content">
            <a href="<?php the_permalink(); ?>" class="question-title">
                <?php the_title() ?>
            </a>
        </div>
        <div class="q-lbtm-content">
            <div class="question-excerpt">
                <?php the_excerpt(); ?>
            </div>
            <div class="question-cat">
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
                <div class="clearfix"></div>                
                <a href="<?php echo get_author_posts_url($question->post_author); ?>">
                    <span class="author-avatar">
                        <?php echo et_get_avatar( $question->post_author, 30 ); ?>
                    </span>
                    <span class="author-name"><?php echo $question->author_name; ?></span>
                </a>                    
                    <?php  qa_user_badge( $question->post_author ); ?>
                    <span class="question-time">
                        <?php printf( __( 'Asked %s in', ET_DOMAIN ),$et_post_date); ?>
                    </span>

                <span class="question-category">
                    <a href="<?php echo $category_link ?>"><?php echo $category ?>.</a>
                </span>
            </div>   
        </div>
    </div><!-- end left content -->
    <div class="col-md-4 col-xs-4 q-right-content">
        <ul class="question-statistic">
            <li>
                <a href="javascript:void(0)" class="action" data-name="approve">
                    <span class="question-views">
                        <?php //echo $question->et_view_count ?>
                        <i class="fa fa-check"></i>
                    </span>
                </a>
                <?php _e("Approve",ET_DOMAIN) ?>
            </li>
            <!-- <li>
                <a href="javascript:void(0)" class="action" data-name="delete">
                    <span class="question-answers">
                        <?php //echo $question->et_answers_count ?> 
                        <i class="fa fa-times"></i>
                    </span>
                </a>
                <?php _e("Reject",ET_DOMAIN) ?>
            </li> -->
            <li>
                <a href="javascript:void(0)" class="action" data-name="delete">
                    <span class="question-votes">
                        <?php //echo $question->et_vote_count ?> 
                        <i class="fa fa-trash-o"></i>
                    </span>
                </a>
                <?php _e("Delete",ET_DOMAIN) ?>
            </li>
        </ul>
    </div><!-- end right content -->                    
</li> 