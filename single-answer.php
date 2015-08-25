<?php
/**
 * The Template for displaying all single questions
 *
 * @package: QAEngine
 * @since: QnA Engine 1.0
 * @author: enginethemes
 */
global $post,$wp_rewrite,$current_user, $qa_question, $qa_answer;
the_post();
$answer          = QA_Questions::convert($post);
$question        = QA_Questions::convert(get_post($post->post_parent));
$et_post_date    = et_the_time(strtotime($question->post_date));
$category        = !empty($question->question_category[0]) ? $question->question_category[0]->name : __('No Category',ET_DOMAIN);
$category_link   = !empty($question->question_category[0]) ? get_term_link( $question->question_category[0]->term_id, 'question_category' ) : '#';

/**
 * global qa_question
*/
$qa_question    =   $question;
$qa_answer      =   $answer;
get_header();

$parent_comments       = get_comments( array( 
    'post_id'       => $question->ID,
    'parent'        => 0,
    'status'        => 'approve',
    'post_status'   => 'publish',
    'order'         => 'ASC',
    'type'          => 'question'
));

$qa_answer_comments   = get_comments( array( 
    'post_id'       => $qa_answer->ID,
    'parent'        => 0,
    'status'        => 'approve',
    'post_status'   => 'publish',
    'order'         => 'ASC',
    'type'          => 'answer'
));
$commentsData = array_merge($parent_comments, $qa_answer_comments);
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
                    <?php if(qa_user_can('edit_question')) { ?>
                    <li>
                        <a href="javascript:void(0)" data-name="edit" class="post-edit action">
                            <i class="fa fa-pencil"></i>
                        </a>
                    </li>
                    <?php } ?>
                    <?php if( current_user_can( 'manage_options' ) ){ ?>
                    <li>
                        <a href="javascript:void(0)" data-name="delete" class="post-delete action" >
                            <i class="fa fa-trash-o"></i>
                        </a>
                    </li>
                    <?php } ?>
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
                    <?php echo apply_filters('et_the_content', $question->post_content ); ?>
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
                            <li>
                                <a href="#container_<?php echo $question->ID ?>" class="show-comments <?php if(count($parent_comments) > 0) echo 'active'; ?>">
                                    <?php                                     
                                        printf( __( 'Comment(%d) ', ET_DOMAIN ), count($parent_comments)); 
                                    ?> <i class="fa fa-comment"></i>
                                </a>
                            </li>
                        </ul>
                    </div>                   
                </div>

                <div class="clearfix"></div>
                <div class="comments-container <?php if(count($parent_comments) == 0) echo 'collapse'; ?>" id="container_<?php echo $question->ID ?>">
                    <div class="comments-wrapper">
                        <?php      
                            if(!empty($parent_comments)){         
                                foreach ($parent_comments as $child) {
                                    qa_comments_loop($child) ;
                                }
                            }
                        ?>
                    </div>
                    <?php qa_comment_form($question); ?>          
                </div><!-- END COMMENTS CONTAINER -->             
            </div>
        </div><!-- END QUESTION-MAIN-CONTENT -->
        <div class="row answers-filter" id="answers_filter">
            <div class="max-col-md-8">
                <div class="col-md-6 col-xs-6">
                    <span class="answers-count"><span class="number"><?php echo et_count_answer($question->ID) ?></span> <?php _e("Answers",ET_DOMAIN) ?></span>
                </div>
                <div class="col-md-6 col-xs-6 sort-questions">
                    <ul>
                        <li>
                            <a class="<?php echo !isset($_GET['sort']) ? 'active' : ''; ?>" href="<?php echo get_permalink( $question->ID ); ?>"><?php _e("Votes",ET_DOMAIN) ?></a>
                        </li>
                        <li>
                            <a class="<?php echo isset($_GET['sort']) && $_GET['sort'] == 'oldest' ? 'active' : ''; ?>" href="<?php echo add_query_arg(array('sort' => 'oldest')); ?>"><?php _e("Oldest",ET_DOMAIN) ?></a>
                        </li>
                    </ul>
                </div>
            </div>           
        </div>

        
        <div id="answers_main_list">
            <div class="row question-main-content question-item answer-item" id="<?php echo $answer->ID ?>">
                <?php get_template_part( 'template/item', 'answer' ); ?>
            </div><!-- END REPLY-ITEM -->
        </div>

        <div class="row form-reply collapse">
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
    </div>
    <?php get_sidebar( 'right' ); ?>
    <script type="text/javascript">
        var answersData     = <?php echo json_encode( array($answer) ) ?>;
        var currentQuestion = <?php echo json_encode($question) ?>;
        var commentsData    = <?php echo json_encode($commentsData) ?>;
    </script>
<?php get_footer() ?>