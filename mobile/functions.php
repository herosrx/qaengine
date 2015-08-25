<?php
define( 'MOBILE_PATH', dirname(__FILE__) );
/**
*
* TEMPLATE FILTER QUESTIONS FOR MOBILE
* @author ThaiNT
* @since 1.0
*
**/
function qa_mobile_submit_questions_form(){
	$privi  =   qa_get_privileges();
	?>
	<section class="post-question-form-wrapper">
		<form id="submit_question" action="">
		<input type="hidden" id="qa_nonce" name="qa_nonce" value="<?php echo wp_create_nonce( 'insert_question' ); ?>">
		<input id="add_tag_text" type="hidden" value="<?php printf(__("You must have %d points to add tag. Current, you have to select existed tags.", ET_DOMAIN), $privi->create_tag  ); ?>" />
		<div class="container">
	        <div class="row">
	        	<div class="col-md-12">
	                	<div class="form-post">
	                		<input type="text" name="post_title" id="question_title" placeholder="<?php _e("Your Question", ET_DOMAIN) ?>">
	                    </div>
	                    <div class="form-post">
	                        <div class="select-categories-wrapper">
	                            <div class="select-categories">
	                                <select class="select-grey-bg" id="" name="question_category">
										<option value=""><?php _e("Select Category",ET_DOMAIN) ?></option>
										<?php
											$terms = get_terms( 'question_category', array('hide_empty' => 0) );
											foreach ($terms as $term) {
										?>
										<option value="<?php echo $term->slug ?>"><?php echo $term->name ?></option>
										<?php
											}
										?>
	                                </select>
	                            </div>
	                        </div>
	                    </div>
	                    <div class="form-post">
	                    	<textarea name="post_content" id="" cols="30" rows="10" placeholder="<?php _e("Your Description", ET_DOMAIN) ?>"></textarea>
	                    </div>
	                    <div class="form-post">
	                    	<input  data-provide="typeahead" type="text" name="" id="question_tags" placeholder="<?php _e('Tag(max 5 tags)',ET_DOMAIN) ?>" />
	                    </div>
	                    <ul class="post-question-tags" id="tag_list"></ul>
	            </div>
	        </div>
	    </div>
	    <div class="group-btn-post">
	    	<div class="container">
	            <div class="row">
	                <div class="col-xs-5"><span class="text"><?php _e("Ask a Question", ET_DOMAIN) ?></span></div>
	                <div class="col-xs-7 text-right">
	                	<button type="submit" class="submit-post-question"><?php _e("Submit", ET_DOMAIN) ?></button>
	                    <a href="javascript:void(0)" class="cancel-post-question"><?php _e("Cancel", ET_DOMAIN) ?></a>
	                </div>
	            </div>
	        </div>
	    </div>
		</form>
	</section>
	<?php
}
/**
*
* TEMPLATE FILTER QUESTIONS FOR MOBILE
* @param array
* @author ThaiNT
* @since 1.0
*
**/
function qa_mobile_filter_search_questions(){
	$current_url = "http".(isset($_SERVER['HTTPS']) ? 's' : '')."://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	?>
	<div class="container">
	    <div class="row">
	    	<div class="col-md-12">
	        	<ul class="menu-middle-bar">
	                <li class="<?php echo !isset($_GET['sort']) ? 'active' : ''; ?>">
	                    <a href="<?php echo remove_query_arg( array('sort') ,$current_url); ?>">
	                    	<?php _e("Latest",ET_DOMAIN) ?>
	                    </a>
	                </li>
	                <li class="<?php echo isset($_GET['sort']) && $_GET['sort'] == 'vote' ? 'active' : ''; ?>" >
	                    <a href="<?php echo add_query_arg(array('sort' => 'vote')); ?>">
	                    	<?php _e("Votes",ET_DOMAIN) ?>
	                    </a>
	                </li>
	                <li class="<?php echo isset($_GET['sort']) && $_GET['sort'] == 'unanswer' ? 'active' : ''; ?>">
	                    <a href="<?php echo add_query_arg(array('sort' => 'unanswer')); ?>">
	                    	<?php _e("Unanswered",ET_DOMAIN) ?>
	                    </a>
	                </li>
	            </ul>
	        </div>
		</div>
	</div>
	<div class="form-search-wrapper" <?php if(is_page_template( 'page-search.php' )){ ?>style="display:block;"<?php } ?>>
	    <?php
	        $keyword = get_query_var( 'keyword' ) ? get_query_var( 'keyword' ) : '';
	        $keyword = str_replace('+', ' ', $keyword);
	    ?>
		<form id="form-search" method="POST" action="<?php echo home_url(); ?>">
	    	<a href="javascript:void(0)" class="clear-text-search"><i class="fa fa-times-circle"></i></a>
	        <a href="javascript:void(0)" class="close-form-search"><?php _e("Cancel",ET_DOMAIN) ?></a>
	        <input type="text" class="form-input-search" autocomplete="off" name="keyword" value="<?php echo esc_attr($keyword) ?>" placeholder="<?php _e("Enter Keywords",ET_DOMAIN) ?>" />
	    </form>
	</div>
	<?php
}
/**
*
* TEMPLATE FORM COMMENTS FOR MOBILE
* @param array $comments
* @author ThaiNT
* @since 1.0
*
**/
function qa_mobile_comment_form( $post, $type = 'question' ){
	global $current_user;
?>
<form class="form-post-answers create-comment collapse">
    <input type="hidden" name="qa_nonce"        value="<?php echo wp_create_nonce( 'insert_comment' );?>" />
    <input type="hidden" name="comment_post_ID" value="<?php echo $post->ID ?>" />
    <input type="hidden" name="comment_type"    value="<?php echo $type ?>" />
    <input type="hidden" name="user_id"         value="<?php echo $current_user->ID ?>" />
	<textarea name="post_content" id="post_content" rows="4" placeholder="<?php _e("Type your comment", ET_DOMAIN)?> "></textarea>
	<input type="submit" class="btn-submit" name="submit" id="" value="<?php _e("Add comment", ET_DOMAIN)?>">
	<a href="javascript:void(0)" class="close-form-post-answers"><?php _e("Cancel", ET_DOMAIN)?></a>
</form>
<?php
}
/**
*
* TEMPLATE LOOP FOR MOBILE COMMENTS
* @param array $comments
* @author ThaiNT
* @since 1.0
*
**/
function qa_mobile_comments_loop($child){
	global $qa_comment;
	$qa_comment = QA_Comments::convert($child);
	get_template_part( 'mobile/template/item' , 'comment' );
}

/**
*
* JS TEMPLATE ANSWER
* @param array $comments
* @author ThaiNT
* @since 1.0
*
**/
function qa_mobile_answer_template(){
	// get template-js/item-answer.php
	get_template_part( 'mobile/template-js/item', 'answer' );
}
function qa_mobile_comment_template(){
	// get template-js/item-answer.php
	get_template_part( 'mobile/template-js/item', 'comment' );
}
/**
 * New Script in Mobile Footer
 */
if ( et_load_mobile()  ){
add_action( 'wp_footer', 'scripts_in_footer_mobile', 100);
}
function scripts_in_footer_mobile(){
?>
<script type="text/javascript" id="frontend_scripts">
	(function ($) {
		$(document).ready(function(){
			if(typeof QAEngine.Views.MobileFront != 'undefined') {
				QAEngine.MobileApp = new QAEngine.Views.MobileFront();
			}
			if(typeof QAEngine.Views.MobileSingleQuestion != 'undefined') {
				QAEngine.MobileSingleQuestion = new QAEngine.Views.MobileSingleQuestion();
			}
		});
	})(jQuery);
</script>
<?php
}
/**
 * Enqueue Styles & Scripts
 */
if ( et_load_mobile()  ){
add_action( 'wp_enqueue_scripts', 'qa_mobile_scripts_styles' );
}
function qa_mobile_scripts_styles(){
	/* ==== PRINT SCRIPTS ==== */
	wp_enqueue_script('mobile-front', 	TEMPLATEURL . '/mobile/js/front.js', array('jquery', 'underscore', 'backbone', 'site-functions'));
	wp_enqueue_script('mouseweel', 		TEMPLATEURL . '/mobile/js/jquery.mouseweel.js', array('jquery'));
	wp_enqueue_script('mobile-script', 	TEMPLATEURL . '/mobile/js/script-mobile.js', array('jquery'));
	wp_enqueue_script('mobile-script', 	TEMPLATEURL . '/js/libs/adjector.js', array('jquery'));

	//localize scripts
	wp_localize_script( 'mobile-front', 'qa_front', qa_static_texts() );

	if(is_singular( 'question' )){
		wp_enqueue_script('mobile-single-question', 	TEMPLATEURL . '/mobile/js/single-question.js', array('jquery', 'underscore', 'backbone', 'site-functions','mobile-front'));
	}
	/* ==== PRINT STYLES ==== */
	wp_enqueue_style( 'mobile-style', 	TEMPLATEURL . '/mobile/css/main.css', array('bootstrap') );
}
/**
 * Handle mobile here
 */
add_filter('template_include', 'et_template_mobile');
function et_template_mobile($template){
	global $user_ID, $wp_query, $wp_rewrite;
	$new_template = $template;

	// no need to redirect when in admin
	if ( is_admin() ) return $template;

	/***
	  * Detect mobile and redirect to the correlative layout file
	  */

	if ( et_load_mobile()  ){

		$filename 		= basename($template);

		$child_path		= get_stylesheet_directory() . '/mobile' . '/' . $filename;
		$parent_path 	= get_template_directory() . '/mobile' . '/' . $filename;

		if ( file_exists($child_path) ){
			$new_template = $child_path;
		} else if ( file_exists( $parent_path )){
			$new_template = $parent_path;
		} else {
			wp_redirect( home_url() );
			// $new_template = get_template_directory() . '/mobile/unsupported.php';
		}
	}

	return $new_template;
}

/**
 *
 */
function et_load_mobile(){
	global $isMobile;
	$detector = new AE_MobileDetect();
	$isMobile = apply_filters( 'et_is_mobile', ( $detector->isIphone() || $detector->isAndroid() || $detector->isWindowsphone() ) ? true : false );
	if ( $isMobile ){
		return true;
	} else {
		return false;
	}
}
/**
 * Get mobile version header template
 * @author toannm
 * @param name of the custom header template
 * @version 1.0
 * @copyright enginethemes.com team
 * @license enginethemes.com team
 */
function et_get_mobile_header( $name = null ){
	do_action( 'get_header', $name );

	//$templates = array();
	$templates = MOBILE_PATH . '/' . 'header.php';
	if ( isset($name) )
		$templates = MOBILE_PATH . '/' . "header-{$name}.php";
	$templates = apply_filters( 'template_include', $templates );

	if ('' == locate_template($templates, true))
		//load_template( ABSPATH . WPINC . '/theme-compat/header.php');
		load_template( $templates);
}

/**
 * Get mobile version header template
 * @author toannm
 * @param name of the custom header template
 * @version 1.0
 * @copyright enginethemes.com team
 * @license enginethemes.com team
 */
function et_get_mobile_footer( $name = null ) {

	do_action( 'get_footer', $name );

	//$templates = array();
	$templates = MOBILE_PATH . '/' . 'footer.php';
	if ( isset($name) )
		$templates = MOBILE_PATH . '/' . "footer-{$name}.php";
	$templates = apply_filters( 'template_include', $templates );

	//$templates = apply_filters( 'template_include', $templates );
	// Backward compat code will be removed in a future release
	if ('' == locate_template($templates, true) )
		//load_template( ABSPATH . WPINC . '/theme-compat/footer.php');
		load_template($templates);
}