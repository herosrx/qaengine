<?php
/**
 * Template Name: Authentication
 */
	global $wp_query, $wp_rewrite, $post, $et_data;
	if ( session_id() == '' ) session_start();
	get_header();
	get_sidebar( 'left' );
	$labels = $et_data['auth_labels'];
	$auth   = unserialize($_SESSION['et_auth']);
	// echo '<pre>';
	// print_r($_SESSION);
	// echo '</pre>';
?>
    <div class="col-md-8 main-blog-fix">
        <div class="row">
            <div class="col-md-12">
                <div class="blog-classic-top">
                    <h2><?php echo $labels['title'] ?></h2>
                    <form id="search-bar" action="<?php echo home_url() ?>">
                        <i class="fa fa-search"></i>
                        <input type="text" name="s" id="" placeholder="<?php _e("Search at blog",ET_DOMAIN) ?>">
                    </form>
                </div>
            </div>
            <div class="clearfix"></div>
            <div class="col-md-12">
				<div class="blog-wrapper">
				    <div class="row">

				        <div class="col-md-12 col-xs-12">
				            <div class="blog-content">
								<!--end header Bottom-->
								<div class="container-fluid main-center">
									<div class="row">
										<div class="col-md-12 marginTop30">
											<div class="twitter-auth social-auth social-auth-step1">
												<p class="social-small"><?php echo $labels['content'] ?></p>
												<form id="form_auth" method="post" action="">
													<div class="social-form">
														<input type="hidden" name="et_nonce" value="<?php echo wp_create_nonce( 'authentication' ) ?>">
														<input type="text" name="user_email" autocomplete="off" placeholder="<?php _e('Email', ET_DOMAIN) ?>">
														<input type="password" name="user_pass" autocomplete="off"  placeholder="<?php _e('Password', ET_DOMAIN) ?>">
														<input type="submit" class="btn-submit" value="Submit">
													</div>
												</form>
											</div>
											<div class="social-auth social-auth-step2">
												<p class="social-small"><?php echo $labels['content_confirm'] ?></p>
												<form id="form_username" method="post" action="">
													<div class="social-form">
														<input type="hidden" name="et_nonce" value="<?php echo wp_create_nonce( 'authentication' ) ?>">
														<input type="text" name="user_login" value="<?php echo isset($auth['user_login']) ? $auth['user_login'] : "" ?>" placeholder="<?php _e('Username', ET_DOMAIN) ?>">
														<input type="submit" class="btn-submit" value="Submit">
													</div>
												</form>
											</div>
										</div>
									</div>
								</div>
								</div>
				        </div>
				    </div>
				</div>
            </div>
        </div>
    </div>
    <?php get_sidebar( 'right' ); ?>
<?php get_footer() ?>