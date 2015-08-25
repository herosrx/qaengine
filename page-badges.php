<?php
/**
 * Template Name: Badges List Template
 * version 1.0
 * @author: enginethemes
 **/
get_header();

$badge_points = qa_get_badge_point();
$levels       = qa_get_privileges();
?>
    <?php get_sidebar( 'left' ); ?>
    <div class="col-md-8 main-content">
        <div class="row select-category">
            <div class="col-md-12 current-category">
                <span><?php _e("Badges", ET_DOMAIN) ?></span>
            </div>          
        </div><!-- END SELECT-CATEGORY -->
        <div class="row points-system">
            <div class="col-md-12">
                <h3><?php _e("Points System", ET_DOMAIN) ?></h3>
                <p><?php _e("You earn reputation when people vote on your posts", ET_DOMAIN) ?></p>
            </div>
            <div class="clearfix"></div>
            <ul class="points-define">
                    <li class="col-md-3">
                        <div>
                            <span class="points-count">
                                +<?php echo $badge_points->create_question ? $badge_points->create_question : 0  ?>
                            </span>
                            <span class="star">
                                <i class="fa fa-star"></i><br>
                                <?php _e("create a question", ET_DOMAIN) ?>
                            </span>
                        </div>
                    </li>             
                    <li class="col-md-3">
                        <div>
                            <span class="points-count">
                                +<?php echo $badge_points->q_vote_up ? $badge_points->q_vote_up : 0  ?>
                            </span>
                            <span class="star">
                                <i class="fa fa-star"></i><br>
                                <?php _e("question is voted up", ET_DOMAIN) ?>
                            </span>
                        </div>
                    </li>    
                    <li class="col-md-3">    
                        <div>
                            <span class="points-count">
                                +<?php echo $badge_points->a_vote_up ? $badge_points->a_vote_up : 0  ?>
                            </span>
                            <span class="star">
                                <i class="fa fa-star"></i><br>
                                <?php _e("answer is voted up", ET_DOMAIN) ?>
                            </span>
                        </div>
                    </li> 
                    <li class="col-md-3">    
                        <div>
                            <span class="points-count">
                                +<?php echo $badge_points->a_accepted ? $badge_points->a_accepted : 0  ?>
                            </span>
                            <span class="star">
                                <i class="fa fa-star"></i><br>
                                <?php _e("answer is accepted", ET_DOMAIN) ?>
                            </span>
                        </div>
                    </li>                                                    
            </ul>
            
        </div><!-- END POINTS-SYSTEM -->
        <div class="row badges-system">
            <div class="col-md-12">
                <h3><?php _e("Badges System", ET_DOMAIN) ?></h3>
                <p><?php _e("You earn reputation when people vote on your posts", ET_DOMAIN) ?></p>
            </div>
            <?php
                $badges     =   QA_Pack::query(array());
                while( $badges->have_posts() ) { $badges->the_post();
                    global $post;
                    $pack        =  QA_Pack::qa_convert($post);
            ?>
            <div class="col-md-12 badge-content">
                <div class="border">
                    <div class="col-md-3 question-cat">
                        <span class="user-badge" style="background:<?php echo $pack->qa_badge_color ?>;">
                            <?php echo $pack->post_title ?>
                        </span><br>
                        <span class="points-count">
                            <?php echo $pack->qa_badge_point ?>
                        </span>
                        <span class="star">
                            <i class="fa fa-star"></i><br>
                            <?php _e("points require", ET_DOMAIN) ?>
                        </span>
                    </div>
                    <div class="col-md-4">
                        <span><?php _e("With you can do:", ET_DOMAIN) ?></span>
                        <p>
                            <i class="fa fa-<?php echo $pack->qa_badge_point >= $levels->edit_question ? 'check' : 'ban' ?>"></i>
                            <?php _e("Edit other people's questions", ET_DOMAIN) ?>
                        </p>
                        <p>
                            <i class="fa fa-<?php echo $pack->qa_badge_point >= $levels->add_comment ? 'check' : 'ban' ?>"></i>
                            <?php _e("Vote to close, reopen, or migrate questions", ET_DOMAIN) ?>
                        </p>
                    </div>
                    <div class="col-md-3">
                        <p>
                            <i class="fa fa-<?php echo $pack->qa_badge_point >= $levels->edit_answer ? 'check' : 'ban' ?>"></i>
                            <?php _e("Edit other people's answers", ET_DOMAIN) ?>
                        </p>
                        <p>
                            <i class="fa fa-<?php echo $pack->qa_badge_point >= $levels->vote_down ? 'check' : 'ban' ?>"></i>
                            <?php _e("Vote down (costs 1 point on answers)", ET_DOMAIN) ?>
                        </p>                    
                    </div>
                    <div class="col-md-2">
                        <p>
                            <i class="fa fa-<?php echo $pack->qa_badge_point >= $levels->add_comment ? 'check' : 'ban' ?>"></i>
                            <?php _e("Leave comments", ET_DOMAIN) ?>
                        </p>
                        <p>
                            <i class="fa fa-<?php echo $pack->qa_badge_point >= $levels->vote_up ? 'check' : 'ban' ?>"></i>
                            <?php _e("Vote up", ET_DOMAIN) ?>
                        </p>                  
                    </div>
                </div>
            </div>
            <?php 
                }
                wp_reset_query();
            ?>                        
        </div><!-- END BADGES-SYSTEM -->     
    </div>
    <?php get_sidebar( 'right' ); ?>
<?php get_footer() ?>