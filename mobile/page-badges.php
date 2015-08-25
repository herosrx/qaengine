<?php
/**
 * Template: BADGES/POINTS LISTING
 * version 1.0
 * @author: ThaiNT
 **/
	et_get_mobile_header();
$badge_points = qa_get_badge_point();
$levels       = qa_get_privileges();
?>
<!-- CONTAINER -->
<div class="wrapper-mobile">
	<!-- BADGES BAR -->
    <section class="tag-bar bg-white">
    	<div class="container">
            <div class="row">
            	<div class="col-md-4 col-xs-4">
                	<h1 class="title-page"><?php _e('Badges', ET_DOMAIN) ?></h1>
                </div>
                <div class="col-md-8 col-xs-8 collapse">
                	<form class="find-tag-form">
                    	<i class="fa fa-chevron-circle-right"></i>
                    	<input type="text" name="" id="" placeholder="Find a user">
                    </form>
                </div>
            </div>
        </div>
    </section>
    <!-- BADGES BAR / END -->
    
    <!-- POINT SYSTEM -->
    <section class="point-system-wrapper">
    	<div class="container">
            <div class="row">
            	<div class="col-md-12">
                	<div class="point-system">
                    	<h1><?php _e("Points System", ET_DOMAIN) ?></h1>
                        <span><?php _e("You earn reputation when people vote on your posts", ET_DOMAIN) ?></span>
                        <ul class="list-point">
                       		<li>
                            	<span class="point-circle">+ <?php echo $badge_points->q_vote_up ? $badge_points->q_vote_up : 0  ?></span>
                            	<p class="point-text"><i class="fa fa-star"></i><?php _e("question is voted up", ET_DOMAIN) ?></p>
                            </li>
                            <li>
                            	<span class="point-circle">+ <?php echo $badge_points->a_vote_up ? $badge_points->a_vote_up : 0  ?></span>
                            	<p class="point-text"><i class="fa fa-star"></i><?php _e("answer is voted up", ET_DOMAIN) ?></p>
                            </li>
                            <li>
                            	<span class="point-circle">+ <?php echo $badge_points->a_accepted ? $badge_points->a_accepted : 0  ?></span>
                            	<p class="point-text"><i class="fa fa-star"></i><?php _e("answer is accepted", ET_DOMAIN) ?></p>
                            </li>
                            <!-- <li>
                            	<span class="point-circle">+ <?php //echo $badge_points->e_accepted ? $badge_points->e_accepted : 0  ?></span>
                            	<p class="point-text"><i class="fa fa-star"></i><?php //_e("edit approved", ET_DOMAIN) ?></p>
                            </li> -->
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- POINT SYSTEM / END -->
    
    <!-- POINT SYSTEM -->
    <section class="point-system-wrapper">
    	<div class="container">
            <div class="row">
            	<div class="col-md-12">
                	<div class="point-system">
                    	<h1><?php _e("Badges System", ET_DOMAIN) ?></h1>
                        <span style="margin-bottom:20px;"><?php _e("At the highest levels, you'll have access to special moderation tools. You'll be able to work alongside our community moderators to keep the site focused and helpful.", ET_DOMAIN) ?></span>
                        <?php
                            $badges     =   QA_Pack::query(array());
                            while( $badges->have_posts() ) { $badges->the_post();
                                global $post;
                                $pack        =  QA_Pack::qa_convert($post);
                        ?>
						<div class="badges-point-system">
                            <div class="list-bag-profile-wrapper user-list">
                                <!-- <span class="badges-profile">Professor</span> -->
                                <span class="user-badge" style="background:<?php echo $pack->qa_badge_color ?>;">
                                    <?php echo $pack->post_title ?>
                                </span>                        
                                <span class="point-profile">
                                    <span><?php echo $pack->qa_badge_point ?><i class="fa fa-star"></i></span><?php _e("points require", ET_DOMAIN) ?>
                                </span>
                            </div>
                            <h2 class="title-system"><?php _e("With you can do:", ET_DOMAIN) ?></h2>

                            <span class="role-user-system">
                                <i class="fa fa-<?php echo $pack->qa_badge_point >= $levels->edit_question ? 'check' : 'ban' ?>"></i><?php _e("Edit other people's questions", ET_DOMAIN) ?>
                            </span>

                            <span class="role-user-system">
                                <i class="fa fa-<?php echo $pack->qa_badge_point >= $levels->edit_answer ? 'check' : 'ban' ?>"></i><?php _e("Edit other people's answers", ET_DOMAIN) ?>
                            </span> 

                            <span class="role-user-system">
                                <i class="fa fa-<?php echo $pack->qa_badge_point >= $levels->vote_down ? 'check' : 'ban' ?>"></i><?php _e("Vote down (costs 1 point on answers)", ET_DOMAIN) ?>
                            </span>

                            <span class="role-user-system">
                                <i class="fa fa-<?php echo $pack->qa_badge_point >= $levels->add_comment ? 'check' : 'ban' ?>"></i><?php _e("Leave comments", ET_DOMAIN) ?>
                            </span>

                            <span class="role-user-system">
                                <i class="fa fa-<?php echo $pack->qa_badge_point >= $levels->vote_up ? 'check' : 'ban' ?>"></i><?php _e("Vote up", ET_DOMAIN) ?>
                            </span>

                        </div>
                        <?php 
                            }
                            wp_reset_query();
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- POINT SYSTEM / END -->
</div>
<!-- CONTAINER / END -->
<?php
	et_get_mobile_footer();
?>