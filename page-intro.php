<?php
/**
 * Template Name: Intro Page Template
 * version 1.0
 * @author: enginethemes
 **/
$disabled_register = is_multisite() ? get_site_option('registration') : get_option('users_can_register');
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<!--[if lt IE 7]> <html class="ie ie6 oldie" lang="en"> <![endif]-->
	<!--[if IE 7]>    <html class="ie ie7 oldie" lang="en"> <![endif]-->
	<!--[if IE 8]>    <html class="ie ie8 oldie" lang="en"> <![endif]-->
	<!--[if gt IE 8]> <html class="ie ie9 newest" lang="en"> <![endif]-->
	<head>
		<meta http-equiv="X-UA-Compatible" content="IE=Edge">
		<meta charset="utf-8">
	    <title>
            <?php
                if(is_home() || is_front_page()) {
                    echo get_option("blogdescription").' | '.get_option("blogname") ;
                } else {
                    wp_title( '|', true, 'right' );
                }
            ?>
	    </title>
		<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
		<meta name="description" content="<?php echo get_option("blogdescription"); ?>">
		<?php
			$favicon = (TEMPLATEURL . '/img/fe-favicon.png');
		?>
		<link rel="shortcut icon" href="<?php echo $favicon ?>"/>
		<link href='http://fonts.googleapis.com/css?family=Lato:400,700,900' rel='stylesheet' type='text/css'>
		<script type="text/javascript" src="<?php echo TEMPLATEURL ?>/js/libs/selectivizr-min.js"></script>
		<!--[if lt IE 9]>
			<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
	    <?php wp_head(); ?>
	</head>
	<body <?php echo body_class('intro-wrapper') ?>>
		<div class="container intro-page-wrapper">
			<div class="row">
				<header id="header" class="intro-header">
					<div class="col-md-12" id="logo">
						<a href="<?php echo home_url(); ?>">
                            <?php
                                $site_logo  =   ae_get_option('site_logo');
                            ?>
							<img src="<?php echo $site_logo['large'][0] ?>">
						</a>
					</div><!-- logo -->
				</header><!-- END HEADER -->

                <div class="clearfix"></div>
                <!-- CONTENT INTRO -->
                <div class="intro-content-wrapper">
                	<div class="col-md-7">
                    	<div class="intro-text">
                        	<h2 class="slide-text">
                                <?php
                                    if(ae_get_option('intro_slide_text')){
                                        $string = ae_get_option('intro_slide_text');
                                        $string = implode("",explode("\\",$string));
                                        echo stripslashes(trim($string));
                                    }
                                ?>
                            	<!-- Not just another
                            	<span class="adject">EngineThemes|Geek|Cool|Lazy|Nerd</span>
								WordPress theme, -->
                            </h2>
                            <h3 class="text-bottom">
                                <?php
                                    if(ae_get_option('intro_bottom_text')){
                                        echo stripcslashes( ae_get_option('intro_bottom_text') );
                                    }
                                ?>
                                <!-- QAEngine aims to take the knowledge sharing <br>
								experience to a <span>whole new level.</span> -->
                            </h3>
                        </div>
                    </div>
                    <!-- FORM -->
                    <div class="col-md-5">
                    	<div class="form-signup-wrapper">
                            <a class="hiddenanchor" id="toregister"></a>
                            <a class="hiddenanchor" id="tologin"></a>
                            <div class="group-btn-intro">
                                <a href="#tologin" class="to_register active"> <?php _e("Sign in",ET_DOMAIN) ?> </a><?php if($disabled_register == 1 || $disabled_register == "user" || $disabled_register == "all" || $disabled_register == "blog" ){ ?> <span><?php _e("or",ET_DOMAIN) ?></span> <a href="#toregister" class="to_register"><?php _e("Sign up",ET_DOMAIN) ?></a><?php } ?>
                            </div>
                            <div id="wrapper">
                                <div id="login" class="animate">
                                    <form class="sign-in-intro" id="sign_in" method="POST">
                                    	<div class="row">
                                        	<div class="col-md-12">
                                            	<div class="row">
                                        			<div class="col-md-6">
                                                        <p class="intro-name">
                                                            <span class="your-email">
                                                                <input type="text" autocomplete="off" id="username" name="username" value="" class="" placeholder="Email">
                                                                <i class="fa fa-envelope-o"></i>
                                                            </span>
                                                        </p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p class="intro-password">
                                                            <span class="your-password">
                                                                <input type="password" autocomplete="off" id="password" name="password" class="" placeholder="<?php _e("Password",ET_DOMAIN) ?>">
                                                                <i class="fa fa-key"></i>
                                                            </span>
                                                        </p>
                                                    </div>
                                                 </div>
                                            </div>
                                            <div class="col-md-12">
                                            `	<div class="row">
                                        			<div class="col-md-6 col-xs-6">
                                                    	<p class="intro-remember">
                                                            <input type="hidden" id="remember" name="remember" value="0" />
                                                            <a class="your-remember" href="javascript:void(0)">
                                                                <i class="fa fa-check-circle-o"></i> <?php _e("Remember me",ET_DOMAIN) ?>
                                                            </a>
                                                        </p>
                                                     </div>
                                                     <div class="col-md-6 col-xs-6">
                                                        <p class="intro-remember">
                                                            <a class="your-fogot-pass" href="javascript:void(0)">
                                                               <?php _e("Forgot password ?",ET_DOMAIN) ?>
                                                            </a>
                                                        </p>
                                                     </div>
                                                 </div>
                                            </div>
                                            <div class="col-md-12">
                                            	<p class="btn-submit-intro">
                                                    <span class="your-submit">
                                                        <input type="submit" name="" value="<?php _e("Sign in",ET_DOMAIN) ?>" class="btn-submit" />
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <?php if($disabled_register == 1 || $disabled_register == "user" || $disabled_register == "all" || $disabled_register == "blog" ){ ?>
                                <div id="register" class="animate ">
                                    <form class="sign-up-intro" id="sign_up" method="POST">
                                        <div class="row">
                                        	<div class="col-md-12">
                                            	<div class="row">
                                        			<div class="col-md-6">
                                                        <p class="intro-name">
                                                            <span class="your-email">
                                                                <input type="text" autocomplete="off" name="email" id="email" value="" class="" placeholder="<?php _e("Email",ET_DOMAIN) ?>">
                                                                <i class="fa fa-envelope-o"></i>
                                                            </span>
                                                        </p>
                                                     </div>
                                                     <div class="col-md-6">
                                                        <p class="intro-name">
                                                            <span class="your-name">
                                                                <input type="text" autocomplete="off" name="username" id="username" value="" class="" placeholder="<?php _e("User Name",ET_DOMAIN) ?>">
                                                                <i class="fa fa-user"></i>
                                                            </span>
                                                        </p>
                                                    </div>
                                                 </div>
                                            </div>
                                            <div class="col-md-12">
                                            	<div class="row">
                                        			<div class="col-md-6">
                                                        <p class="intro-password">
                                                            <span class="your-password">
                                                                <input type="password" autocomplete="off" name="password" id="password1" value="" class="" placeholder="<?php _e("Password",ET_DOMAIN) ?>">
                                                                <i class="fa fa-key"></i>
                                                            </span>
                                                        </p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p class="intro-password">
                                                            <span class="your-password">
                                                                <input type="password" autocomplete="off" id="re_password" name="re_password" value="" class="" placeholder="<?php _e("Repeat Password",ET_DOMAIN) ?>">
                                                                <i class="fa fa-key"></i>
                                                            </span>
                                                        </p>
                                                	</div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                            	<p class="terms-intro">
                                                     <?php _e('By clicking "Sign Up" you indicate that you have read and agree to the',ET_DOMAIN) ?> <a target="_blank"  href="<?php echo et_get_page_link('term') ?>"><?php _e("Terms of Service.",ET_DOMAIN) ?></a>
                                                </p>
                                            </div>
                                            <div class="col-md-12">
                                            	<p class="btn-submit-intro">
                                                    <span class="your-submit">
                                                        <input type="submit" name="" value="<?php _e("Sign up",ET_DOMAIN) ?>" class="btn-submit" />
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <?php } ?>
                            </div>
                    	</div>
                        <?php if($disabled_register == 1 || $disabled_register == "user" || $disabled_register == "all" || $disabled_register == "blog" ){ ?>
                        <div class="sign-in-social">
                            <ul class="social-icon clearfix">
                                <!-- google plus login -->
                                <?php if(ae_get_option('gplus_login', false)){?>
                                            <li class="gp"><a id="signinButton" href="#" class="sc-icon color-google" ><i class="fa fa-google-plus-square"></i></a></li>
                                <?php } ?>
                                <!-- twitter plus login -->
                                <?php if(ae_get_option('twitter_login', false)){?>
                                    <li class="tw"><a href="<?php echo add_query_arg('action', 'twitterauth', home_url()) ?>" class="sc-icon color-twitter" ><i class="fa fa-twitter-square"></i></a></li>
                                <?php } ?>
                                <!-- facebook plus login -->
                                <?php if(ae_get_option('facebook_login', false)){?>
                                    <li class="fb"><a href="#" id="facebook_auth_btn" class="sc-icon color-facebook" ><i class="fa fa-facebook-square"></i></a></li>
                                <?php } ?>

                             </ul>
                        </div>
                        <?php } ?>
                    </div>
                    <!-- END FORM -->
                 </div>
                 <!-- END CONTENT INTRO -->
                 <div class="clearfix"></div>
                 <div class="footer-intro">
                 	<div class="col-md-12">
                    	<ul class="list-menu-footer">
                            <?php
                                if(has_nav_menu('et_header')){
                                    wp_nav_menu(array(
                                            'theme_location' => 'et_header',
                                            'items_wrap' => '%3$s',
                                            'container' => ''
                                        ));
                                }
                            ?>
                        </ul>
                    </div>
                 </div>
            </div>
        </div>
    <!-- Style Intro Background -->
    <?php
        $bg_images = ae_get_option('intro_background');
        $bg_images = wp_get_attachment_image_src($bg_images['attach_id'],'full');
        //print_r($bg_images);
        if($bg_images){
    ?>
    <style type="text/css">
        .intro-wrapper {
            background: url(<?php echo $bg_images[0]; ?>) no-repeat;
            background-size: cover;
            background-attachment: fixed;
        }
    </style>
    <?php } ?>
    <!-- Style Intro Background -->
    <!-- MODAL RESET PASSWORD -->
    <?php
        qa_reset_password_modal()
    ?>
    <!-- MODAL RESET PASSWORD -->
    <?php
        qa_login_register_modal();

        $google = ae_get_option('google_analytics');
        $google = implode("",explode("\\",$google));
        echo stripslashes(trim($google));

        wp_footer();
    ?>
	</body><!-- END BODY -->
</html>