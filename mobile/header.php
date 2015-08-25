<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<title><?php echo get_option("blogname") ; ?>  | <?php _e("MOBILE VERSION", ET_DOMAIN) ?></title>
    <?php
        $options    =   AE_Options::get_instance();
        $favicon = $options->mobile_icon['thumbnail'][0];
    ?>
    <link rel="shortcut icon" href="<?php echo $favicon ?>"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link href='//fonts.googleapis.com/css?family=Lato:300,400,700,300italic,400italic,700italic' rel='stylesheet' type='text/css'>
    <?php
        //loads comment reply JS on single posts and pages
        if ( is_single()) wp_enqueue_script( 'comment-reply' );
    ?>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<!-- HEADDER -->
<header>
	<div class="wrapper-mobile">
    	<div class="menu-btn">
        	<i class="fa fa-bars"></i>
        </div>
        <div class="post-question-btn">
        	<i class="fa fa-plus"></i>
        </div>
    	<div class="logo">
        	<a href="<?php echo home_url(); ?>">
        		<?php
        			$site_logo	=	ae_get_option('site_logo');
        		?>
        		<img src="<?php echo $site_logo['large'][0] ? $site_logo['large'][0] : get_template_directory_uri().'/img/logo.png' ?>" />
        	</a>
        </div>
    </div>
</header>
<!-- HEADDER / END -->

<!-- POST QUESTION -->
<?php qa_mobile_submit_questions_form() ?>
<!-- POST QUESTION / END -->

<!-- MENU PUSH -->
<section class="menu-push">
    <div class="container">
        <div class="row">
            <?php
                if(is_user_logged_in()){
                    global $current_user;
            ?>
            <div class="col-md-3 col-xs-3">
                <div class="author-avatar">
                    <a href="<?php echo get_author_posts_url( $current_user->ID ); ?>">
                        <?php echo et_get_avatar($current_user->ID, 65); ?>
                    </a>
                </div>
            </div>
            <div class="col-md-9 col-xs-9">
            	<div class="author-info">
                    <span class="author-name"><?php echo $current_user->display_name; ?></span>
                    <!-- <span class="author-badge">Professor</span> -->
                    <?php qa_user_badge($current_user->ID) ?>
                    <a href="<?php echo get_author_posts_url( $current_user->ID ); ?>" class="setting-author"><i class="fa fa-cog"></i></a>
                </div>
            </div>
            <div class="clearfix"></div>

            <div class="line-menu-push first"></div>
            <ul class="list-categories sign-out-link">
                <li>
                    <a href="<?php echo wp_logout_url(home_url()); ?>"><?php _e("Sign Out", ET_DOMAIN) ?></a>
                </li>
            </ul>

            <?php } else { ?>

            <div class="line-menu-push first"></div>
            <ul class="list-categories sign-in-link">
                <li>
                    <a href="<?php echo et_get_page_link('intro'); ?>"><?php _e("Sign In", ET_DOMAIN) ?></a>
                </li>
            </ul>

            <?php } ?>
            <div class="line-menu-push"></div>
            <ul class="list-categories list-menus">
                <?php
                    if(has_nav_menu('et_left')){
                        wp_nav_menu( array(
                            'theme_location' => 'et_left',
                            'depth'          => '1',
                            'items_wrap'     => '%3$s',
                            'container'      => '',
                            'walker'         => new QA_Custom_Walker_Nav_Menu()
                        ) );
                    }
                ?>
            </ul>
            <div class="line-menu-push"></div>
            <ul class="list-categories">
                <?php
                    $terms = get_terms( 'question_category', array('hide_empty'=> 0) );
                    foreach ($terms as $term) {
                ?>
                <li>
                    <a href="<?php echo get_term_link( $term, 'question_category' ); ?>">
                        <?php echo $term->name; ?>
                    </a>
                </li>
                <?php
                    }
                ?>
            </ul>
        </div>
    </div>
</section>
<!-- MENU PUSH / END -->