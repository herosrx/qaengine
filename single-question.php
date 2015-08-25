<?php
/**
 * The Template for displaying all single questions
 *
 * @package: QAEngine
 * @since: QnA Engine 1.0
 * @author: enginethemes
 */
global $post, $wp_rewrite, $current_user, $qa_question, $wp_query;
the_post();
$question        = QA_Questions::convert($post);
$et_post_date    = et_the_time(strtotime($question->post_date));
$category        = !empty($question->question_category[0]) ? $question->question_category[0]->name : __('No Category',ET_DOMAIN);
$category_link   = !empty($question->question_category[0]) ? get_term_link( $question->question_category[0]->term_id, 'question_category' ) : '#';
/**
 * global qa_question
*/
$qa_question    =   $question;
get_header();

$parent_comments       = get_comments( array(
    'post_id'       => $post->ID,
    'parent'        => 0,
    'status'        => 'approve',
    'post_status'   => 'publish',
    'order'         => 'ASC',
    'type'          => 'question'
) );
?>
    <?php get_sidebar( 'left' ); ?>
    <div class="col-md-8 main-content single-content">
        <div class="row select-category single-head">
            <div class="col-md-2 col-xs-2">
                <span class="back">
                    <i class="fa fa-angle-double-left"></i> <a href="<?php echo home_url(); ?>"><?php _e("Home", ET_DOMAIN); ?></a>
                </span>
            </div>
            <div class="col-md-8 col-xs-8">
                <h3><?php the_title(); ?></h3>
            </div>
        </div><!-- END SELECT-CATEGORY -->
        <div id="question_content" class="row question-main-content question-item" data-id="<?php echo $post->ID; ?>">
            <!-- Vote section -->
            <?php get_template_part( 'template/item', 'vote' ); ?>
            <!--// Vote section -->
            <div class="col-md-9 col-xs-9 q-right-content">

                <!-- admin control -->
                <ul class="post-controls">
                    <?php if($current_user->ID == $qa_question->post_author || qa_user_can('edit_question')) { ?>
                    <li>
                        <a href="javascript:void(0)" data-toggle="tooltip" data-original-title="<?php _e("Edit", ET_DOMAIN) ?>" data-name="edit" class="post-edit action">
                            <i class="fa fa-pencil"></i>
                        </a>
                    </li>
                    <?php } ?>
                    <?php if( current_user_can( 'manage_options' ) ){ ?>
                    <li>
                        <a href="javascript:void(0)" data-toggle="tooltip" data-original-title="<?php _e("Delete", ET_DOMAIN) ?>" data-name="delete" class="post-delete action" >
                            <i class="fa fa-trash-o"></i>
                        </a>
                    </li>
                    <?php } ?>
                    <!-- Follow Action -->
                    <?php
                        $user_following = explode(',', $question->et_users_follow);
                        $is_followed    = in_array($current_user->ID, $user_following);
                        if(!$is_followed){
                    ?>
                    <li>
                        <a href="javascript:void(0)" data-toggle="tooltip" data-original-title="<?php _e("Follow", ET_DOMAIN) ?>" data-name="follow" class="action follow" >
                            <i class="fa fa-plus-square"></i>
                        </a>
                    </li>
                    <?php } else { ?>
                    <li>
                        <a href="javascript:void(0)" data-toggle="tooltip" data-original-title="<?php _e("Unfollow", ET_DOMAIN) ?>" data-name="unfollow" class="action followed" >
                            <i class="fa fa-minus-square"></i>
                        </a>
                    </li>
                    <?php } ?>
                    <!-- // Follow Action -->
                    <!-- report Action -->
                    <?php if(is_user_logged_in() && !$question->reported && $question->post_status != "pending"){ ?>
                     <li>
                        <a href="javascript:void(0)" data-toggle="tooltip" data-original-title="<?php _e("Report", ET_DOMAIN) ?>" data-name="report" class="action report" >
                            <i class="fa fa-exclamation-triangle"></i>
                        </a>
                    </li>
                    <?php } else if( current_user_can( 'manage_options' ) ) { ?>
                    <li>
                        <a href="javascript:void(0)" data-toggle="tooltip" data-original-title="<?php _e("Approve", ET_DOMAIN) ?>" data-name="approve" class="action approve" >
                            <i class="fa fa-check"></i>
                        </a>
                    </li>
                    <?php } ?>
                    <!--// Report Action -->
                </ul>
                <!--// admin control -->
                <!-- question tag -->
                <div class="top-content">
                    <?php if($question->et_best_answer){ ?>
                    <span class="answered"><i class="fa fa-check"></i> <?php _e("Answered", ET_DOMAIN) ?></span>
                    <?php } ?>
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
                <!--// question tag -->
                <div class="clearfix"></div>

                <div class="question-content">
                    <?php the_content() ?>
                </div>

                <div class="row">
                    <div class="col-md-8 col-xs-8 question-cat">
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
                    <div class="col-md-4 col-xs-4 question-control">
                        <ul>
                            <li>
                                <a class="share-social" href="javascript:void(0)" data-toggle="popover" data-placement="top"  data-container="body" data-content='<?php echo qa_template_share($question->ID); ?>' data-html="true">
                                    <?php _e("Share",ET_DOMAIN) ?> <i class="fa fa-share"></i>
                                </a>
                            </li>
                            <!-- <li class="collapse">
                                <a href="javascript:void(0)">
                                    <?php _e("Report",ET_DOMAIN) ?> <i class="fa fa-flag"></i>
                                </a>
                            </li> -->
                            <li>
                                <a href="#container_<?php echo $post->ID ?>" class="show-comments <?php if(count($parent_comments) > 0) echo 'active'; ?>">
                                    <?php
                                        printf( __( 'Comment(%d) ', ET_DOMAIN ), count($parent_comments));
                                    ?> <i class="fa fa-comment"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="clearfix"></div>

                <div class="comments-container <?php if(count($parent_comments) == 0) echo 'collapse'; ?>" id="container_<?php echo $post->ID ?>">
                    <div class="comments-wrapper">
                        <?php
                            if(!empty($parent_comments)){
                                foreach ($parent_comments as $child) {
                                    qa_comments_loop($child) ;
                                }
                            }
                        ?>
                    </div>
                    <?php qa_comment_form($post); ?>
                </div><!-- END COMMENTS CONTAINER -->
            </div>
        </div><!-- END QUESTION-MAIN-CONTENT -->

        <?php if( is_active_sidebar( 'qa-content-question-banner-sidebar' ) ){ ?>
        <div class="row">
            <div class="col-md-12 ads-wrapper">
                <?php dynamic_sidebar( 'qa-content-question-banner-sidebar' ); ?>
            </div>
        </div><!-- END WIDGET BANNER -->
        <?php } ?>

        <div class="row answers-filter" id="answers_filter">
            <div class="max-col-md-8">
                <div class="col-md-6 col-xs-6">
                    <span class="answers-count"><span class="number">
                        <?php echo $question->et_answers_count ?></span> <?php _e("Answer(s)",ET_DOMAIN) ?>
                    </span>
                </div>
                <div class="col-md-6 col-xs-6 sort-questions">
                    <ul>
                        <li>
                            <a class="<?php echo !isset($_GET['sort']) ? 'active' : ''; ?>" href="<?php echo get_permalink( $question->ID ); ?>"><?php _e("Votes",ET_DOMAIN) ?></a>
                        </li>
                        <!-- <li>
                            <a class="<?php echo isset($_GET['sort']) && $_GET['sort'] == 'active' ? 'active' : ''; ?>" href="<?php echo add_query_arg(array('sort' => 'active')); ?>"><?php _e("Active",ET_DOMAIN) ?></a>
                        </li> -->
                        <li>
                            <a class="<?php echo isset($_GET['sort']) && $_GET['sort'] == 'oldest' ? 'active' : ''; ?>" href="<?php echo add_query_arg(array('sort' => 'oldest')); ?>"><?php _e("Oldest",ET_DOMAIN) ?></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <?php qa_answers_loop(); ?>

        <?php if(is_active_sidebar( 'qa-btm-single-question-banner-sidebar' )){ ?>
        <div class="row">
            <div class="col-md-12 ads-wrapper answers-ad-wrapper">
                <?php dynamic_sidebar( 'qa-btm-single-question-banner-sidebar' ); ?>
            </div>
        </div>
        <?php } ?>

        <div class="row form-reply">
            <div class="col-md-12">
                <h3><?php _e("Your Answer",ET_DOMAIN) ?></h3>
                <form id="form_reply" method="POST">
                    <input type="hidden" name="qa_nonce" value="<?php echo wp_create_nonce( 'insert_answer' );?>" />
                    <input type="hidden" name="post_parent" value="<?php echo $post->ID ?>" />
                    <?php wp_editor( '', 'post_content', editor_settings() ); ?>
                    <div class="row submit-wrapper">
                        <div class="col-md-2">
                            <button id="submit_reply" class="btn-submit">
                                <?php _e("Post answer",ET_DOMAIN) ?>
                            </button>
                        </div>
                        <div class="col-md-10 term-texts">
                            <?php qa_tos("answer"); ?>
                        </div>
                    </div>
                </form>
            </div>
        </div><!-- END FORM REPLY -->

        <div class="clearfix"></div>

        <?php do_action( 'qa_btm_quetions_listing' ); ?>

    </div>
    <?php get_sidebar( 'right' ); ?>
    <script type="text/javascript">
        currentQuestion = <?php echo defined('JSON_HEX_QUOT') ? json_encode( $question, JSON_HEX_QUOT ) : json_encode( $question ) ?>;
    </script>
<?php get_footer() ?>