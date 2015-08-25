<?php
/**
 * Template: Author Page
 * version 1.0
 * @author: enginethemes
 **/
global $wp_query, $wp_rewrite, $current_user;

$user = get_user_by( 'id', get_query_var( 'author' ) );
$user = QA_Member::convert($user);

get_header();
?>
    <?php get_sidebar( 'left' ); ?>
    <div class="col-md-8 main-content">
        <div class="row select-category">
            <div class="col-md-6 col-xs-6 current-category">
                <span>
                    <?php
                        printf(__("%s's Profile",ET_DOMAIN), esc_attr($user->display_name) );
                    ?>
                </span>
            </div>
            <?php
                if($current_user->ID == $user->ID){
            ?>
            <div class="col-md-6 col-xs-6 user-controls">
                <ul>
                    <li>
                        <a href="javascript:void(0)" data-toggle="modal" class="show-edit-form">
                            <?php _e("Edit",ET_DOMAIN) ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo wp_logout_url(home_url()); ?>">
                            <?php _e("Logout",ET_DOMAIN) ?>
                        </a>
                    </li>
                </ul>
            </div>
            <?php } ?>
        </div><!-- END SELECT-CATEGORY -->
        <div class="row user-statistic highlight">
            <div class="col-md-5 col-xs-12 user-info">
                <span class="avatar-80">
                    <?php echo et_get_avatar( $user->ID, 80); ?>
                </span>
                <?php if($current_user->ID != $user->ID){ ?>
                <p class="contact-block">
                    <button class="inbox" id="inbox"><?php _e('Contact', ET_DOMAIN) ?></button>
                    <!-- <button class="follow" id="follow"><?php _e('Follow', ET_DOMAIN) ?></button> -->
                </p>
                <?php } ?>
                <ul>
                    <li class="name">
                        <?php echo esc_attr($user->display_name);  ?>
                    </li>
                    <li class="location">
                        <i class="fa fa-map-marker"></i>
                        <?php echo $user->user_location ? esc_attr($user->user_location) : __('Earth', ET_DOMAIN) ?>
                    </li>
                    <li class="email">
                        <i class="fa fa-envelope"></i>
                        <?php echo $user->show_email == "on" ? esc_attr($user->user_email) : __('Email is hidden.', ET_DOMAIN); ?>
                    </li>
                    <?php if($user->user_facebook){ ?>
                    <li class="location">
                        <i class="fa fa-facebook"></i>
                        <a target="_blank" href="<?php echo $user->user_facebook ?>"><?php echo esc_attr($user->user_facebook) ?></a>
                    </li>
                    <?php } ?>
                    <?php if($user->user_twitter){ ?>
                    <li class="location">
                        <i class="fa fa-twitter"></i>
                        <a target="_blank" href="<?php echo $user->user_twitter ?>"><?php echo esc_attr($user->user_twitter) ?></a>
                    </li>
                    <?php } ?>
                    <?php if($user->user_gplus){ ?>
                    <li class="location">
                        <i class="fa fa-google"></i>
                        <a target="_blank" href="<?php echo $user->user_gplus ?>"><?php echo esc_attr($user->user_gplus) ?></a>
                    </li>
                    <?php } ?>
                </ul>
            </div>
            <div class="col-md-7 col-xs-12 user-post-count">
                <div class="row">
                    <div class="col-md-4 col-xs-4 question-cat">
                        <?php qa_user_badge( $user->ID ); ?>
                        <br>
                        <span class="points-count">
                        <?php echo qa_get_user_point($user->ID) ? qa_get_user_point($user->ID) : 0 ?>
                        </span>
                        <span class="star">
                            <i class="fa fa-star"></i><br>
                            <?php _e("points", ET_DOMAIN) ?>
                        </span>
                    </div>
                    <div class="col-md-4 col-xs-4">
                        <p class="questions-count">
                            <?php _e('Questions',ET_DOMAIN) ?><br>
                            <span><?php echo et_count_user_posts($user->ID, 'question'); ?></span>
                        </p>
                    </div>
                    <div class="col-md-4 col-xs-4">
                        <p class="answers-count">
                            <?php _e('Answers',ET_DOMAIN) ?><br>
                            <span><?php echo et_count_user_posts($user->ID, 'answer'); ?></span>
                        </p>
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-md-12 description">
                        <?php echo nl2br(esc_attr($user->description)); ?>
                    </div>
                </div>
            </div>
        </div><!-- END USER-STATISTIC -->
        <div class="row question-filter">
            <div class="col-md-12 sort-questions">
                <ul>
                    <li>
                        <a class="<?php if(!isset($_GET['type'])) echo 'active'; ?>" href="<?php echo get_author_posts_url($user->ID); ?>"><?php _e('Questions',ET_DOMAIN) ?></a>
                    </li>
                    <li>
                        <a class="<?php if(isset($_GET['type']) && $_GET['type'] == "answer") echo 'active'; ?>" href="<?php echo add_query_arg(array('type'=>'answer')); ?>"><?php _e('Answers',ET_DOMAIN) ?></a>
                    </li>
                    <?php if($current_user->ID == $user->ID){ ?>
                    <li>
                        <a class="<?php if(isset($_GET['type']) && $_GET['type'] == "following") echo 'active'; ?>" href="<?php echo add_query_arg(array('type'=>'following')); ?>"><?php _e('Following',ET_DOMAIN) ?></a>
                    </li>
                    <?php } ?>
                </ul>
            </div>
        </div><!-- END QUESTIONS-FILTER -->
        <div class="main-questions-list">
            <ul id="main_questions_list">
                <?php
                    $paged      = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

                    $type       = isset($_GET['type']) ? $_GET['type'] : 'question';

                    $args       = array(
                        'post_type' => $type,
                        'paged'     => $paged,
                        'author'    => $user->ID
                    );
                    //show pending question if current is author
                    if($current_user->ID == $user->ID){
                        $args['post_status'] = array('publish', 'pending');
                    }
                    //tab following questions
                    if(isset($_GET['type']) && $_GET['type'] == "following"){
                        $follow_questions  = array_filter( (array) get_user_meta( $user->ID, 'qa_following_questions', true ) );
                        $args['post_type'] = $type = "question";
                        $args['post__in']  = !empty($follow_questions) ? $follow_questions : array(0);
                        unset($args['author']);
                    }

                    $query      = QA_Questions::get_questions($args);

                    if($query->have_posts()){
                        while($query->have_posts()){
                            $query->the_post();
                            get_template_part( 'template/'.$type, 'loop' );
                        }
                    } else {
                        echo '<li class="no-questions">';
                        echo '<h2>'.__('There is no questions yet.', ET_DOMAIN).'</h2>';
                        echo '</li>';
                    }
                    wp_reset_query();

                ?>
            </ul>
        </div><!-- END MAIN-QUESTIONS-LIST -->
        <div class="row paginations home">
            <div class="col-md-12">
                <?php
                    qa_template_paginations($query,$paged);
                ?>
            </div>
        </div><!-- END MAIN-PAGINATIONS -->
    </div>
    <?php get_sidebar( 'right' ); ?>
<?php get_footer() ?>