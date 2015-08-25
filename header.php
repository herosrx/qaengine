<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<!--[if lt IE 7]> <html class="ie ie6 oldie" lang="en"> <![endif]-->
	<!--[if IE 7]>    <html class="ie ie7 oldie" lang="en"> <![endif]-->
	<!--[if IE 8]>    <html class="ie ie8 oldie" lang="en"> <![endif]-->
	<!--[if gt IE 8]> <html class="ie ie9 newest" lang="en"> <![endif]-->
	<head>
		<meta http-equiv="X-UA-Compatible" content="IE=Edge">
		<meta charset="utf-8">
            <?php
            	$options	=	AE_Options::get_instance();
            ?>
        <title><?php wp_title( '|', true, 'right' ); ?></title>

		<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />

		<?php
			$favicon = $options->mobile_icon['thumbnail'][0];
		?>
		<link rel="shortcut icon" href="<?php echo $favicon ?>"/>
		<link href='//fonts.googleapis.com/css?family=Lato:400,700&subset=latin,cyrillic,cyrillic-ext,vietnamese,latin-ext' rel='stylesheet' type='text/css'>
		<script type="text/javascript" src="<?php echo TEMPLATEURL ?>/js/libs/selectivizr-min.js"></script>
		<?php
	    //loads comment reply JS on single posts and pages
	    if ( is_single()) wp_enqueue_script( 'comment-reply' );
	    ?>
		<!--[if lt IE 9]>
			<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
	    <?php wp_head(); ?>
	</head>
	<body <?php echo body_class('cbp-spmenu-push') ?>>
    	<nav class="cbp-spmenu cbp-spmenu-vertical cbp-spmenu-left" id="cbp-spmenu-s1">
			<?php get_sidebar( 'left-tablet' ); ?>
		</nav>
		<nav class="cbp-spmenu cbp-spmenu-vertical cbp-spmenu-right" id="cbp-spmenu-s2">
			<?php get_sidebar( 'right-tablet' ); ?>
		</nav>
		<?php
			global $current_user;
			$site_logo	=	ae_get_option('site_logo');
		?>
		<div class="container-fluid">
			<div class="row">
				<header id="header">
					<div class="col-md-2 col-xs-2" id="logo">
						<a href="<?php echo home_url(); ?>">
							<?php if(!empty($site_logo)){ ?>
							<img src="<?php echo $site_logo['large'][0] ?>">
							<?php } else { ?>
							<img src="<?php echo TEMPLATEURL ?>/img/logo.png">
							<?php } ?>
						</a>
					</div><!-- logo -->
					<div class="col-md-8 col-xs-8">
						<?php if(has_nav_menu('et_header')){ ?>
                        <div class="header-menu">
                            <ul>
                                <?php
                                    wp_nav_menu(array(
										'theme_location' => 'et_header',
										'items_wrap'     => '%3$s',
										'container'      => ''
                                    ));
                                ?>
                            </ul>
                        </div><!-- menu -->
                        <?php } ?>
                        <div class="header-search-wrapper">
                        	<section class="buttonset">
                                <button id="showLeftPush"><i class="fa fa-question"></i></button>
                                <button id="showRightPush"><i class="fa fa-bar-chart-o"></i></button>
                            </section>
                        	<?php
                        		$keyword = get_query_var( 'keyword' ) ? get_query_var( 'keyword' ) : '';
                        		$keyword = str_replace('+', ' ', $keyword);
                        	?>
                            <form id="header_search" method="GET" action="<?php echo home_url(); ?>" class="disable-mobile">
                                <input type="text" name="keyword" value="<?php echo esc_attr(urldecode($keyword)) ?>" placeholder="<?php _e("Enter Keywords",ET_DOMAIN) ?>" autocomplete="off" />
                                <i class="fa fa-search"></i>
                                <div id="search_preview" class="search-preview empty"></div>
                            </form>
                        </div><!-- search -->
					</div>
					<div class="col-md-2 col-xs-2 btn-group <?php echo is_user_logged_in() ? 'header-avatar ' : ''; ?>">
						<?php
							if(is_user_logged_in()) {
								global $current_user;
						?>
                    	<span class="expand dropdown-toggle" type="span" data-toggle="dropdown">
							<a href="javascript:void(0)" class="dropdown-account " >
                                <span class="avatar"><?php echo et_get_avatar( $current_user->ID, 30 ); ?></span>
                                <span class="display_name"><?php echo $current_user->display_name ?></span>
                                <span class="icon-down"><i class="fa fa-chevron-circle-down"></i></span>
                        	</a>
						</span>
						<ul class="dropdown-menu dropdown-profile">
							<li>
								<a href="<?php echo get_author_posts_url($current_user->ID); ?>">
									<i class="fa fa-user"></i> <?php _e("User Profile",ET_DOMAIN) ?>
								</a>
							</li>
							<li>
								<a href="javascript:void(0)" class="open-edit-profile edit_profile">
									<i class="fa fa-cog"></i> <?php _e("User Settings",ET_DOMAIN) ?>
								</a>
							</li>

							<li>
								<a href="<?php echo wp_logout_url(home_url()); ?>">
									<i class="fa fa-power-off"></i> <?php _e("Log out",ET_DOMAIN) ?>
								</a>
							</li>
						</ul>

						<?php } else {?>

						<a class="login-url" href="javascript:void(0)" data-toggle="modal">
							<?php _e("Login or Register",ET_DOMAIN)?>
						</a>

						<?php } ?>
					</div><!-- avatar -->
				</header><!-- END HEADER -->
				<div class="col-md-12 col-xs-12" id="header_sidebar">
					<?php if(is_active_sidebar( 'qa-header-sidebar' )) dynamic_sidebar( 'qa-header-sidebar' ); ?>
				</div>