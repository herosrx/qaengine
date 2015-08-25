<?php
    global $post;
    $question        = QA_Questions::convert($post);
    $et_post_date    = et_the_time(strtotime($question->post_date));
    $category        = !empty($question->question_category[0]) ? $question->question_category[0]->name : __('No Category',ET_DOMAIN);
    $category_link   = !empty($question->question_category[0]) ? get_term_link( $question->question_category[0]->term_id, 'question_category' ) : '#';
?>
<li <?php post_class( 'question-item' );?> data-id="<?php echo $post->ID ?>">
    <div class="avatar-user">
        <a href="<?php the_permalink(); ?>">
            <?php echo et_get_avatar($post->post_author, 55) ?>
        </a>
    </div>
    <div class="info-user">
        <?php qa_user_badge($post->post_author,true,true) ?>
        <ul class="info-review-question">
            <li>
                <?php echo $question->et_view_count ?><i class="fa fa-eye"></i>
            </li>
            <?php if($question->et_best_answer){ ?>
            <li class="active">
                <?php echo $question->et_answers_count ?><i class="fa fa-check-circle-o"></i>
            </li>
            <?php } else { ?>
            <li>
                <?php echo $question->et_answers_count ?><i class="fa fa-comments"></i>
            </li>
            <?php } ?>
            <li>
                <?php echo $question->et_vote_count ?><i class="fa fa-chevron-circle-up"></i>
            </li>
        </ul>
    </div>
    <div class="content-question">
        <h2 class="title-question">
            <a href="<?php the_permalink(); ?>"><?php the_title() ?></a>
        </h2>
        <div class="info-tag-time">
            <ul class="list-tag collapse">
                <?php
                    foreach ($question->qa_tag as $tag) {
                ?>
                <li>
                    <a href="<?php echo get_term_link($tag->term_id, 'qa_tag'); ?> ">
                        <?php echo $tag->name; ?>
                    </a>
                </li>
                <?php } ?>
            </ul>
            <span class="time-categories">
                <?php
                    $author = '<a href="'.get_author_posts_url( $question->post_author ).'">'.$question->author_name.'</a>';
                    printf( __( 'Asked by %s %s in', ET_DOMAIN ), $author, $et_post_date);
                ?>
                <a href="<?php echo $category_link ?>"><?php echo $category ?></a>.
            </span>
        </div>
    </div>
</li>