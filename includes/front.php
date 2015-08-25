<?php

class QA_Front extends QA_Engine{

	/**
	 * Init
	 */
	public function __construct(){
		parent::__construct();

		new QA_FrontPost();
		// set timeout for send mail to user's following question
		$time_send_mail = apply_filters( 'qa_time_send_mail' , 120 );

		if(ae_get_option( 'qa_send_following_mail' )){
			wp_schedule_single_event( time() + $time_send_mail, 'qa_send_following_mail' );
		} else {
			wp_clear_scheduled_hook( 'qa_send_following_mail' );
		}

		$this->add_filter( 'query_vars'					, 'query_vars' );
		$this->add_filter( 'mce_external_plugins'		, 'tinymce_add_plugins');
		$this->add_filter( 'request'					, 'filter_request_feed');


		$this->add_action( 'init'					, 'frontend_init');
		$this->add_action( 'wp_footer'				, 'scripts_in_footer', 100);
		$this->add_action( 'pre_get_posts'			, 'custom_query' );
		$this->add_filter( 'comment_post_redirect'	, 'qa_comment_redirect' );
	}
	function qa_comment_redirect( $location )
	{
		global $post;
	    return get_permalink( $post->ID );
	}
	public function filter_request_feed($request) {
	    if (isset($request['feed']) && !isset($request['post_type'])):
	        $request['post_type'] = array("question");
	    endif;

	    return $request;
	}

	public function custom_query($query){
		global $wp_query;

		if (!$query->is_main_query())
			return $query;

		if( is_search() ){
			$query->set('post_type', 'post');
		}

		if(is_author()){
			if( !isset($_GET['type'])  || $_GET['type'] == "following")
				$query->set("post_type","question");
			else
				$query->set("post_type",$_GET['type']);
		}

		if( isset($_GET["sort"])){
			if( $_GET["sort"] == "vote" ){

				$this->add_filter("posts_join"		, "_post_vote_join");
				$this->add_filter("posts_orderby"	, "_post_vote_orderby");

			} elseif ($_GET["sort"] == "unanswer") {

				$this->add_filter("posts_join"		, "_post_unanswer_join");
				$this->add_filter("posts_orderby"	, "_post_unanswer_orderby");

			} elseif ($_GET["sort"] == "oldest") {

				$query->set('order', 'ASC');

			}
		}
		if( isset($_GET['numbers']) ){

			$query->set('posts_per_page', $_GET['numbers']);

		}
		return $query;

	}
	public static function _post_vote_join($join){
		global $wpdb;
		$join .= " LEFT JOIN {$wpdb->postmeta} as order_vote ON order_vote.post_id = {$wpdb->posts}.ID AND ( order_vote.meta_key = 'et_vote_count' ) ";
		$join .= " LEFT JOIN {$wpdb->postmeta} as order_best ON order_best.post_id = {$wpdb->posts}.ID AND ( order_best.meta_key = 'et_is_best_answer' ) ";
		return $join;
	}

	public static function _post_vote_orderby($orderby){
		global $wpdb;
		$orderby = " order_best.meta_value DESC, CAST(order_vote.meta_value AS SIGNED) DESC, {$wpdb->posts}.post_date ASC";
		return $orderby;
	}

	public static function _post_unanswer_join($join){
		global $wpdb;
		$join .= " LEFT JOIN {$wpdb->postmeta} as answer_count ON answer_count.post_id = {$wpdb->posts}.ID AND answer_count.meta_key = 'et_answers_count'";
		return $join;
	}

	public static function _post_unanswer_orderby($orderby){
		global $wpdb;
		$orderby = " CAST(answer_count.meta_value AS SIGNED) ASC, {$wpdb->posts}.post_date DESC";
		return $orderby;
	}
	public function frontend_init($wp_rewrite){
		global $wp_rewrite, $current_user;
		// modify the "search questions" link
		$search_slug = apply_filters( 'search_question_slug'			, 'search-questions' );
		add_rewrite_rule( $search_slug . '/([^/]+)/?$'					, 'index.php?pagename=search&keyword=$matches[1]', 'top' );
		add_rewrite_rule( $search_slug . '/([^/]+)/page/([0-9]{1,})/?$'	, 'index.php?pagename=search&keyword=$matches[1]&paged=$matches[2]', 'top' );

	    $author_slug = apply_filters( 'qa_member_slug', 'member' ); // change slug name
	    $wp_rewrite->author_base = $author_slug;

		$rules = get_option( 'rewrite_rules' );
		if ( !isset($rules[$search_slug . '/([^/]+)/?$']) ){
			$wp_rewrite->flush_rules();
		}
		// check ban user
		$user_factory = QA_Member::get_instance();
		if ( $current_user->ID && $user_factory->is_ban( $current_user->ID ) ){
			wp_logout();
			wp_clear_auth_cookie();
		}
	}
	public function query_vars($vars){
		$vars[] = 'keyword';
		return $vars;
	}

	public function scripts_in_footer(){
		global $current_user;
		?>
		<script type="text/javascript">
            _.templateSettings = {
                evaluate: /\<\#(.+?)\#\>/g,
                interpolate: /\{\{=(.+?)\}\}/g,
                escape: /\{\{-(.+?)\}\}/g
            };
        </script>
		<script type="text/javascript" id="frontend_scripts">
			(function ($) {
				$(document).ready(function(){

					<?php if(!et_load_mobile()){ ?>

					if(typeof QAEngine.Views.Front != 'undefined') {
						QAEngine.App = new QAEngine.Views.Front();
					}

					if(typeof QAEngine.Views.Intro != 'undefined') {
						QAEngine.Intro = new QAEngine.Views.Intro();
					}

					if(typeof QAEngine.Views.UserProfile != 'undefined') {
						QAEngine.UserProfile = new QAEngine.Views.UserProfile();
					}

					if(typeof QAEngine.Views.Single_Question != 'undefined') {
						QAEngine.Single_Question = new QAEngine.Views.Single_Question();
					}

					<?php if( is_page_template( 'page-pending.php' ) ) { ?>
					if(typeof QAEngine.Views.PendingQuestions != 'undefined') {
						QAEngine.PendingQuestions = new QAEngine.Views.PendingQuestions();
					}
					<?php } ?>

					/*======= Open Reset Password Form ======= */
					<?php if( isset($_GET['user_login']) && isset($_GET['key']) && !is_user_logged_in() ){ ?>
						var resetPassModal = new QAEngine.Views.ResetPassModal({ el: $("#reset_password") });
						resetPassModal.openModal();
					<?php } ?>

					/*======= Open Reset Password Form ======= */
					<?php if( isset($_GET['confirm']) && $_GET['confirm'] == 0 ){ ?>
						AE.pubsub.trigger('ae:notification', {
							msg: "<?php _e("You need to verify your account to view the content.",ET_DOMAIN)  ?>",
							notice_type: 'error',
						});
					<?php } ?>

					/*======= Open Confirmation Message Modal ======= */
					<?php
						global $qa_confirm;
						if( $qa_confirm ){
					?>
						AE.pubsub.trigger('ae:notification', {
							msg: "<?php _e("Your account has been confirmed successfully!",ET_DOMAIN)  ?>",
							notice_type: 'success',
						});
					<?php } ?>

					<?php } ?>
				});
			})(jQuery);
		</script>
		<script type="text/javascript" id="current_user">
		 	currentUser = <?php
		 	if ($current_user->ID)
		 		echo json_encode(QA_Member::convert($current_user));
		 	else
		 		echo json_encode(array('id' => 0, 'ID' => 0));
		 	?>
		</script>
		<?php
		echo '<!-- GOOGLE ANALYTICS CODE -->';
        $google = ae_get_option('google_analytics');
        $google = implode("",explode("\\",$google));
        echo stripslashes(trim($google));
		echo '<!-- END GOOGLE ANALYTICS CODE -->';
	}

	public function on_add_scripts(){
		parent::on_add_scripts();

		// default scripts: jquery, backbone, underscore
		$this->add_existed_script('jquery');
		$this->add_existed_script('underscore');
		$this->add_existed_script('backbone');

		$this->add_script('site-core', 			ae_get_url(). '/assets/js/appengine.js',array('jquery', 'backbone', 'underscore','plupload'));
		$this->add_script('site-functions', 	TEMPLATEURL . '/js/functions.js',array('jquery', 'backbone', 'underscore'));

		$this->add_script('bootstrap', 			TEMPLATEURL . '/js/libs/bootstrap.min.js');
		$this->add_script('modernizr', 			TEMPLATEURL . '/js/libs/modernizr.js', array('jquery'));
		//$this->add_script('adjector', 			TEMPLATEURL . '/js/libs/adjector.js','jquery');
		$this->add_script('rotator', 			TEMPLATEURL . '/js/libs/jquery.simple-text-rotator.min.js','jquery');
		$this->add_script('jquery-validator', 	TEMPLATEURL . '/js/libs/jquery.validate.min.js','jquery');

		$this->add_existed_script('jquery-ui-autocomplete');

		if(et_load_mobile()){
			return;
		} else {

			if( ae_get_option('qa_live_notifications') ){
				$this->add_existed_script('heartbeat');
			}

			$this->add_script('waypoints', 			TEMPLATEURL . '/js/libs/waypoints.min.js', array('jquery'));
			$this->add_script('waypoints-sticky', 	TEMPLATEURL . '/js/libs/waypoints-sticky.js', array('jquery', 'waypoints'));
			$this->add_script('chosen', 			TEMPLATEURL . '/js/libs/chosen.jquery.min.js', array('jquery'));
			$this->add_script('classie', 			TEMPLATEURL . '/js/libs/classie.js', array('jquery'));
			$this->add_script('site-script', 		TEMPLATEURL . '/js/scripts.js', 'jquery');
			$this->add_script('site-front', 		TEMPLATEURL . '/js/front.js', array('jquery', 'underscore', 'backbone', 'site-functions'));

			//localize scripts
			wp_localize_script( 'site-front', 'qa_front', qa_static_texts() );

			if( is_singular( 'question' ) || is_singular( 'answer' ) ){
				$this->add_script('qa-shcore', TEMPLATEURL . '/js/libs/syntaxhighlighter/shCore.js', array('jquery'));
				$this->add_script('qa-brush-js', TEMPLATEURL . '/js/libs/syntaxhighlighter/shBrushJScript.js', array('jquery', 'qa-shcore'));
				$this->add_script('qa-brush-php', TEMPLATEURL . '/js/libs/syntaxhighlighter/shBrushPhp.js', array('jquery', 'qa-shcore'));
				$this->add_script('qa-brush-css', TEMPLATEURL . '/js/libs/syntaxhighlighter/shBrushCss.js', array('jquery', 'qa-shcore'));
				$this->add_script('single-question', 	TEMPLATEURL . '/js/single-question.js', array('jquery', 'underscore', 'backbone', 'site-functions','site-front'));
			}

			if(is_page_template( 'page-intro.php' )){
				$this->add_script('intro', 		TEMPLATEURL . '/js/intro.js', array('jquery', 'underscore', 'backbone', 'site-functions', 'site-front'));
			}

			if(is_author()){
				$this->add_existed_script('plupload_all');
				$this->add_script('profile', 		TEMPLATEURL . '/js/profile.js', array('jquery', 'underscore', 'backbone', 'site-functions', 'site-front'));
			}
			if( is_page_template( 'page-pending.php' ) ){
				$this->add_script('pending', 		TEMPLATEURL . '/js/pending.js', array('jquery', 'underscore', 'backbone', 'site-functions', 'site-front'));
			}
		}
	}

	public function on_add_styles(){
		parent::on_add_styles();

		$this->add_style( 'bootstrap'		, TEMPLATEURL.'/css/libs/bootstrap.min.css' );
		$this->add_style( 'font-awesome'	, TEMPLATEURL.'/css/libs/font-awesome.min.css' );

		if(et_load_mobile()){
			return;
		} else {
			$this->add_style( 'main-style'		, TEMPLATEURL.'/css/main.css',array('bootstrap') );
			$this->add_style( 'editor-style'	, TEMPLATEURL.'/css/editor.css' );
			$this->add_style( 'push-menu'		, TEMPLATEURL.'/css/libs/push-menu.css' );
			$this->add_style( 'chosen'			, TEMPLATEURL.'/css/libs/chosen.css' );
			$this->add_style( 'custom-style'	, TEMPLATEURL.'/css/custom.css' );

			if(is_singular( 'question' ))
				$this->add_style('qa-shstyle', TEMPLATEURL . '/css/shCoreDefault.css');

			$this->add_style( 'style' 			, get_stylesheet_uri() );
		}

		do_action('qa_after_print_styles');
	}

	/**
	 * Add new plugin for TinyMCE
	 */
	public function tinymce_add_plugins($plugin_array){
		$qaimage    = TEMPLATEURL . '/js/plugins/feimage/editor_plugin_src.js';
		$autoresize = TEMPLATEURL . '/js/plugins/autoresize/editor_plugin.js';
		$autolink   = TEMPLATEURL . '/js/plugins/autolink/plugin.min.js';
		$qacode     = TEMPLATEURL . '/js/plugins/fecode/editor_plugin.js';

		$plugin_array['qaimage']    = $qaimage;
		$plugin_array['qacode']     = $qacode;
		$plugin_array['autoresize'] = $autoresize;
		$plugin_array['autolink']   = $autolink;

	    return $plugin_array;
	}
}

/**
 * Handle post data
 */
class QA_FrontPost extends AE_Base{

	public function __construct(){
		$this->add_action('template_redirect', 'handle_posts');
	}

	public function handle_posts(){
		global $current_user;
		/**
		*
		* - PREVENT USERS ACCESS TO PENDING PAGE EXCEPT ADMIN
		* -
		* - @package QAEngine
		* - @version 1.0
		*
		**/
		if( is_page_template( 'page-pending.php' ) && !current_user_can( 'manage_options' ) ){
			wp_redirect( home_url() );
			exit();
		}
		/**
		*
		* - PREVENT USERS ACCESS TO CONTENT PAGE IF OPTION IS ACTIVE
		* -
		* - @package QAEngine
		* - @version 1.0
		*
		**/
		if( ae_get_option("login_view_content") ){
			//var_dump(!is_page_template( 'page-intro.php' ) && !is_user_logged_in());
			if( !is_page()  && !is_singular( 'post' ) && !is_user_logged_in() ){
				wp_redirect( et_get_page_link('intro') );
				exit();
			}
		}

		/**
		*
		* - REDIRECT USERS TO QUESTIONS LIST PAGE IF ALREADY LOGGED IN
		* -
		* - @package QAEngine
		* - @version 1.0
		*
		**/

		if(is_page_template( 'page-intro.php' )){
			if(is_user_logged_in()){
				wp_redirect( get_post_type_archive_link( 'question' ) );
				exit();
			}
		}

		/**
		*
		* - REDIRECT TO SEARCH PAGE
		* -
		* - @package QAEngine
		* - @version 1.0
		*
		**/
		if ( isset($_REQUEST['keyword']) ){
			$keyword = str_replace('.php', ' php', $_REQUEST['keyword']);
			$link = qa_search_link( esc_attr( $keyword ) );
			wp_redirect( $link );
			exit;
		}
		/**
		*
		* - COUNT QUESTION VIEW
		* -
		* - @package QAEngine
		* - @version 1.0
		*
		**/
	    if( is_singular( 'question' )) {
	        global $post,$user_ID;

	        if( ae_get_option("login_view_content") && get_user_meta( $user_ID, 'register_status', true ) == "unconfirm" ){
	        	wp_redirect( add_query_arg( array('confirm' => 0), home_url() ) );
	        	exit();
	        }

	        if($post->post_status == 'publish') {

	            $views  =   (int) QA_Questions::get_field($post->ID, 'et_view_count');
	            $key    =   "et_post_".$post->ID."_viewed";

	            if(!isset($_COOKIE[$key]) ||  $_COOKIE[$key] != 'on') {
	                QA_Questions::update_field($post->ID, 'et_view_count', $views + 1 ) ;
	                setcookie($key, 'on', time()+3600, "/");
	            }
	        }
	    }
		/**
		*
		* - INSERT A QUESTION
		* - @param string $post_title
		* - @param string $post_content
		* - @param string $question_category
		* - @package QAEngine
		* - @version 1.0
		*
		**/
		if ( isset($_POST['qa_nonce']) && wp_verify_nonce( $_POST['qa_nonce'], 'insert_question' ) ){
			global $current_user;

			$cats = array(
				'qa_tag' 			=> $_POST['tags'],
				'question_category' => $_POST['question_category']
			);

			$result = QA_Questions::insert_question($_POST['post_title'],$_POST['post_content'],$cats);

			do_action( 'qa_insert_question', $result );

			if(!is_wp_error( $result )){
				wp_redirect( get_permalink( $result ) );
				exit;
			}
		}
		/**
		*
		* - INSERT A COMMENT TO QUESTION
		* - @param int $post_id
		* - @param array $author_data
		* - @param array $comment_data
		* - @package QAEngine
		* - @version 1.0
		*
		**/
		if ( isset($_POST['qa_nonce']) && wp_verify_nonce( $_POST['qa_nonce'], 'insert_comment') ){
			global $current_user;
			$result = QA_Comments::insert( array(
					'comment_post_ID' => $_POST['comment_post_ID'],
					'comment_content' => $_POST['post_content'],
				));

			do_action( 'qa_insert_comment', $result );

			if(!is_wp_error( $result )){
				wp_redirect( et_get_last_page( $_POST['comment_post_ID'] ) );
				exit;
			}
		}
		/**
		 * Confirm User
		 */
		if(isset($_GET['act']) && $_GET['act'] == "confirm" && $_GET['key'] ){
			$user = get_users(array( 'meta_key' => 'key_confirm', 'meta_value' => $_GET['key'] ));
			global $qa_confirm;
			$qa_confirm = update_user_meta( $user[0]->ID, 'register_status', '' );

			$user_email		=	$user[0]->user_email;

			$message		=	ae_get_option('confirmed_mail_template');

			$message	=	et_filter_authentication_placeholder ( $message, $user[0]->ID );
			$subject	=	__("Congratulations! Your account has been confirmed successfully.",ET_DOMAIN);

			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
			$headers .= "From: ".get_option('blogname')." < ".get_option('admin_email') ."> \r\n";

			if($qa_confirm && $user_email)
				wp_mail($user_email, $subject , $message, $headers) ;
		}
	}
}


class QA_Ajax extends AE_Base{

	public function __construct(){
		$this->add_ajax('et_post_sync'			  , 'sync_post');
		$this->add_ajax('et_upload_images'		  , 'upload_images', true, true);
		$this->add_ajax('et_get_nonce'			  , 'get_nonce', true, false);
		$this->add_ajax('et_search'				  , 'search_questions');
	}

	public function get_nonce(){
		global $user_ID;
		if($user_ID){
			$resp = array(
				'success' 	=> true,
				'msg' => 'success',
				'data' => array(
					'ins' => wp_create_nonce( 'insert_question' ),
					'up'  => wp_create_nonce( 'et_upload_images' ),
					)
				);
		} else {
			$resp = array(
				'success' 	=> false,
				'msg' 		=> 'failed'
			);
		}
		wp_send_json( $resp );
	}

	public function sync_post(){
		$method = $_POST['method'];

		switch ($method) {

			case 'report':
				$resp = $this->report();
				break;

			case 'create':
				$resp = $this->create();
				break;

			case 'update':
				$resp = $this->update_post();
				break;

			case 'remove':
				$resp = $this->delete();
				break;

			default:
				# code...
				break;
		}

		wp_send_json( $resp );

	}

	public function create(){
		try{

			$args = $_POST['content'];
			global $current_user;

			if( !is_user_logged_in() )
				throw new Exception(__("You must log in to post question.", ET_DOMAIN));

			if( isset($args['post_title']) && $args['post_title'] != strip_tags($args['post_title']) )
				throw new Exception(__("Post title should not contain any HTML Tag.", ET_DOMAIN));

			if( isset($args['qa_nonce']) && wp_verify_nonce( $args['qa_nonce'], 'insert_comment' )) {

				if(!qa_user_can('add_comment'))
					throw new Exception(__("You don't have enough point to add a comment.", ET_DOMAIN));

				$args['comment_content']      = $args['post_content'];
				$args['comment_author']       = $current_user->user_login;
				$args['comment_author_email'] = $current_user->user_email;

				$result 	= QA_Comments::insert($args);
				$comment  	= QA_Comments::convert(get_comment($result));

				if(is_wp_error( $result )){
					$resp = array(
						'success' 	=> false,
						'msg' 		=> __('An error occur when created comment.',ET_DOMAIN)
					);
				} else {
					$resp = array(
						'success' 	=> true,
						'msg' 		=> __('Comment has been created successfully.',ET_DOMAIN),
						'data'		=> $comment
					);
				}

			} elseif (isset($args['qa_nonce']) && wp_verify_nonce( $args['qa_nonce'], 'insert_answer' )){

				$result 	= QA_Answers::insert_answer($args['post_parent'], $args['post_content']);
							  QA_Answers::update_field($result, "et_vote_count", 0);
				$answer  	= QA_Answers::convert(get_post($result));

				if(is_wp_error( $result )){
					$resp = array(
						'success' 	=> false,
						'msg' 		=> __('An error occur when created answer.',ET_DOMAIN)
					);
				} else {
					$msg = ae_get_option('pending_answers') && !(current_user_can( 'manage_options' ) || qa_user_can( 'approve_answer' )) ? __('Your answer has been created successfully and need to be approved by Admin before displayed!', ET_DOMAIN) : __('Answer has been created successfully.', ET_DOMAIN);
					$resp = array(
						'success' 	=> true,
						'redirect'	=> get_permalink($answer->post_parent),
						'msg' 		=> $msg,
						'data'		=> $answer
					);
				}

			} elseif (isset($args['qa_nonce']) && wp_verify_nonce( $args['qa_nonce'], 'insert_question' )){

				$cats = array(
					'qa_tag' 			=> isset($args['tags']) ? $args['tags'] : array(),
					'question_category' => $args['question_category']
				);

				$status = ae_get_option("pending_questions") && !current_user_can( 'manage_options' ) ? "pending" : "publish";

				$result = QA_Questions::insert_question($args['post_title'], $args['post_content'], $cats, $status);
						  QA_Questions::update_field($result, "et_vote_count", 0);
						  QA_Questions::update_field($result, "et_answers_count", 0);
				$post 	= QA_Questions::convert(get_post($result));

				$msg 	= ae_get_option("pending_questions") && !current_user_can( 'manage_options' ) ? __('Your question has been created successfully. It\'ll appear right after being approved by admin.',ET_DOMAIN) : __('Question has been created successfully.',ET_DOMAIN);
				$redirect = ae_get_option("pending_questions") && !current_user_can( 'manage_options' ) ? home_url() : get_permalink( $result );

				if(is_wp_error( $result )){
					$resp = array(
						'success' 	=> false,
						'msg' 		=> __('An error occur when created question.',ET_DOMAIN)
					);
				} else {
					$resp = array(
						'success' 	=> true,
						'redirect'	=> $redirect,
						'msg' 		=> $msg,
						'data'		=> $post
					);
				}

			}else {
				throw new Exception("Error Processing Request", 1);

			}

		} catch (Exception $e) {
			$resp = array(
				'success' 	=> false,
				'msg' 		=> $e->getMessage()
			);
		}
		return $resp;
	}

	public function update_post(){
		try {
			global $current_user;

			if(!isset($_POST['do_action'])) {
				throw new Exception(__("Invalid action", ET_DOMAIN));
			}

			if( isset($_POST['content']['post_title']) && $_POST['content']['post_title'] != strip_tags($_POST['content']['post_title']) ){
				throw new Exception(__("Post title should not contain any HTML Tag.", ET_DOMAIN));
			}

			$action	=	$_POST['do_action'];
			switch ( $action ) {
				case 'vote_down':
					if(!qa_user_can('vote_down')) throw new Exception(__("You don't have enough point to vote up.", ET_DOMAIN));
				case 'vote_up':
					if(!qa_user_can('vote_up')) throw new Exception(__("You don't have enough point to vote down.", ET_DOMAIN));

					QA_Questions::vote( $_POST['ID'], $action );
					$post = QA_Questions::convert(get_post( $_POST['ID'] ));
					$resp = array(
						'success' 	=> true,
						'data' 		=> $post
					);
					break;

				case 'accept-answer':
				case 'un-accept-answer':
					$question = get_post( $_POST['post_parent'] );
					$answerID  = $action == "accept-answer" ? $_POST['ID'] : 0;

					if( $current_user->ID != $question->post_author ){
						throw new Exception(__("Only question author can mark answered.", ET_DOMAIN));
						return false;
					}

					QA_Questions::mark_answer( $_POST['post_parent'], $answerID );

					do_action( 'qa_accept_answer', $answerID, $action );

					$resp = array(
						'success' 	=> true
					);
					break;

				case 'saveComment':
					$data = array();
					$data['comment_ID'] 	 = $_POST['comment_ID'];
					$data['comment_content'] = $_POST['comment_content'];

					$success = QA_Comments::update($data);
					$comment = QA_Comments::convert(get_comment( $_POST['comment_ID'] ));

					$resp = array(
						'success' 	=> true,
						'data' 		=> $comment
					);
					break;
				case 'savePost':

					$data                 = array();
					$data['ID']           = $_POST['ID'];
					$data['post_content'] = $_POST['post_content'];
					$data['post_author']  = $_POST['post_author'];

					$success = QA_Answers::update($data);
					$post    = QA_Answers::convert(get_post( $_POST['ID'] ));

					if( $success &&  !is_wp_error( $success ) ) {

						$resp = array(
							'success' 	=> true,
							'data' 		=> $post
						);
					}else {
						$resp = array(
							'success' 	=> false,
							'data' 		=> $post,
							'msg'		=> $success->get_error_message()
						);
					}

					break;

				case 'saveQuestion':

					$data                = $_POST['content'];
					$data['ID']          = $_POST['ID'];
					$data['qa_tag']      = isset($data['tags']) ? $data['tags'] : array() ;
					$data['post_author'] = $_POST['post_author'];
					unset($data['tags']);

					$success = QA_Questions::update($data);

					$post    = QA_Questions::convert(get_post( $_POST['ID'] ));

					if( $success &&  !is_wp_error( $success ) ) {
						$resp = array(
							'success'  => true,
							'data'     => $post,
							'msg'      => __('Question has been saved successfully.', ET_DOMAIN),
							'redirect' => get_permalink( $_POST['ID'] )
						);
					}else {
						$resp = array(
							'success' 	=> false,
							'data' 		=> $post,
							'msg'		=> $success->get_error_message()
						);
					}

					break;
				case 'approve':

					$id      = $_POST['ID'];
					$success = QA_Questions::change_status($id, "publish");
					$post    = QA_Questions::convert(get_post( $id ));
					$point   = qa_get_badge_point();
					//store question id to data for send mail
					QA_Engine::qa_questions_new_answer($id);

					if( $success &&  !is_wp_error( $success ) ) {
						if($post->post_type == "question"){
							//add points to user
							if( !empty( $point->create_question ) ) qa_update_user_point( $post->post_author, $point->create_question );
							// set transient for new question
							set_transient( 'qa_notify_' . mt_rand( 100000, 999999 ), array(
								'title'   =>		__( 'New Question', ET_DOMAIN ),
								'content' =>	 	__( "There's a new post, why don't you give a look at", ET_DOMAIN ) .
								' <a href ="' . get_permalink( $id ) . '">' . get_the_title( $id ) . '</a>',
								'type'    =>		'update',
								'user'    =>	md5( $current_user->user_login )
							), 20 );

							$resp = array(
								'success' 	=> true,
								'data' 		=> $post,
								'msg'		=> __("You've just successfully approved a question.", ET_DOMAIN),
								'redirect'	=> get_permalink( $id )
							);
						} else if($post->post_type == "answer"){
							//add point to user
							if( !empty( $point->post_answer ) ) qa_update_user_point( $post->post_author, $point->post_answer );
							$resp = array(
								'success' 	=> true,
								'data' 		=> $post,
								'msg'		=> __("You've just successfully approved an answer.", ET_DOMAIN),
								'redirect'	=> get_permalink( $id )
							);
						}

					} else {
						$resp = array(
							'success' 	=> false,
							'data' 		=> $post,
							'msg'		=> $success->get_error_message()
						);
					}

					break;
				case 'follow':
				case 'unfollow':
					if ( !$current_user->ID ){
						throw new Exception(__('Login required', ET_DOMAIN));
					}

					$result = QA_Questions::toggle_follow($_POST['ID'], $current_user->ID);

					if (!is_array($result))
						throw new Exception(__('Error occurred', ET_DOMAIN));

					if(in_array($current_user->ID, $result)){
						$msg = __( 'You have started following this question.', ET_DOMAIN );
					} else {
						$msg = __( 'You have stopped following this question.', ET_DOMAIN );
					}

					$resp = array(
						'success' 	=> true,
						'msg' => $msg,
						'data' 		=> array(
							'isFollow' 	=> in_array($current_user->ID, $result),
							'following' => $result
						)
					);
					break;
				case 'report':
					$id = $_POST['ID'];
					if(!isset($_POST) || !$id){
						throw new Exception(__("Invalid post", ET_DOMAIN));
					}
					else{
						$fl = $this->report($id, $_POST['data']['message']);
						if($fl){
							$resp = array(
								'success' 	=> true,
								'msg' 		=> __("You have reported successfully", ET_DOMAIN)
							);
						}
						else{
							$resp = array(
								'success' 	=> false,
								'msg' 		=> __("Error when reported!", ET_DOMAIN)
							);
						}
					}
					break;
				default:
					throw new Exception(__("Invalid action", ET_DOMAIN));
					break;
			}

		} catch (Exception $e) {
			$resp = array(
				'success' 	=> false,
				'msg' 		=> $e->getMessage()
			);
		}
		return $resp;
	}

	/**
	 * Upload Images via TinyMCE
	 */
	public function upload_images(){
		try{
			if ( !check_ajax_referer( 'et_upload_images', '_ajax_nonce', false ) ){
				throw new Exception( __('Security error!', ET_DOMAIN ) );
			}

			// check fileID
			if(!isset($_POST['fileID']) || empty($_POST['fileID']) ){
				throw new Exception( __('Missing image ID', ET_DOMAIN ) );
			}
			else {
				$fileID	= $_POST["fileID"];
			}

			if(!isset($_FILES[$fileID])){
				throw new Exception( __('Uploaded file not found',ET_DOMAIN) );
			}

			if($_FILES[$fileID]['size'] > 1024*1024){
				throw new Exception( __('Image file size is too big.Size must be less than < 1MB.',ET_DOMAIN) );
			}

			// handle file upload
			$attach_id = et_process_file_upload( $_FILES[$fileID], 0 , 0, array());

			if ( is_wp_error($attach_id) ){
				throw new Exception( $attach_id->get_error_message() );
			}

			$image_link = wp_get_attachment_image_src( $attach_id , 'full');

			// no errors happened, return success response
			$res	= array(
				'success'	=> true,
				'msg'		=> __('The file was uploaded successfully', ET_DOMAIN),
				'data'		=> $image_link[0]
			);
		}
		catch(Exception $e){
			$res	= array(
				'success'	=> false,
				'msg'		=> $e->getMessage()
			);
		}
		wp_send_json( $res );
	}

	/**
	 * Report a question or answer
	 */
	public function report($id, $report_data){
		global $current_user;
		// required logged in
		if ( !$current_user->ID || !$id  || !$report_data) return false;

		$post_type = get_post_type($id);
		$post      = get_post($id);
		$report_tx = '';
		if(!empty($post_type)){
			$report_tx = 'report-'.$post_type;
		}
		switch ($post_type) {
			case 'question':
				$title     = $post->post_title;
				$link      = $post->guid;
				$report_tx = $report_tx;
				break;
			case 'answer':
				$thread    = get_post($post->post_parent);
				$title     = $thread->post_title;
				$link      = $thread->guid;
				$report_tx = $report_tx;
				break;
			default:
				$report_tx = 'untaxonomy';
				break;
		}

		$content = '<p>Post Content:</p>
					<p>'.$post->post_content.'</p>
					<p>|----------------------------------------------|</p>
					<p>Message:</p>
					<p>'.$report_data.'</p>';

		$my_post = array(
			  'post_title'    => 'REPORT#',
			  'post_content'  => $content,
			  'post_status'   => 'publish',
			  'post_author'   => $current_user->ID,
			  'post_type'     => 'report',
			);

		// Insert the post into the database
		$users_report = (array)get_post_meta( $id, 'et_users_report',true);
		if(!in_array($current_user->ID,$users_report)){
			$result = wp_insert_post( $my_post );
			$m_post = array(
					'ID'         => $result,
					'post_title' => '[REPORT#'.$result.']'.$title
				 );
			wp_update_post($m_post);
			wp_set_object_terms( $result, $report_tx, 'report-taxonomy' );
			if($result){
				array_push($users_report, $current_user->ID);
				update_post_meta( $id, 'et_users_report', $users_report );
				update_post_meta( $result, '_link_report', $link );
			}
			do_action('et_after_reported', $id, $report_data);
		} else {
			$result = false;
		}

		if($result)
			return true;
		else
			return false;
	}
	public function delete(){
		try {

			if ( empty($_POST['ID']) && empty($_POST['comment_ID']) ) throw new Exception( __('Error occurred', ET_DOMAIN) );

			if( isset($_POST['do_action']) && $_POST['do_action'] == "deleteComment"){

				$msg  = __( "Comment deleted successfully!", ET_DOMAIN );
				$post = get_comment($_POST['ID']);
				wp_delete_comment($_POST['comment_ID']);

			} else {

				if( isset($_POST['post_type']) && $_POST['post_type'] == "answer") {

					$msg  = __( "Answer deleted successfully!", ET_DOMAIN );
					$post = get_post($_POST['ID']);
					QA_Answers::delete($_POST['ID']);

				} else {

					$msg  = __( "Question deleted successfully!", ET_DOMAIN );
					$post = get_post($_POST['ID']);
					QA_Questions::delete($_POST['ID']);
				}
			}

			$resp = array(
				'success' 	=> true,
				'msg' 		=> $msg,
				'redirect'		=> get_post_type_archive_link( 'question' ),
				'data' 		=> $post
			);

		} catch (Exception $e) {
			$resp = array(
				'success' => false,
				'msg' => $e->getMessage()
			);
		}
		return $resp;
	}

	/**
	 * AJAX search questions by keyword (next version)
	 *
	 */
	public function search_questions(){
		try {
			$query = QA_Questions::search($_POST['content']);
			$data  = array();
			foreach ($query->posts as $post) {
				$question            = QA_Questions::convert($post);
				$question->et_avatar = QA_Member::get_avatar_urls($post->post_author, 30);
				$question->permalink = get_permalink( $post->ID );

				$data[] = $question;
			}

			$resp = array(
				'success' 	=> true,
				'msg' 		=> '',
				'data' 		=> array(
					'questions' 	=> $data,
					'total' 		=> $query->found_posts,
					'count' 		=> $query->post_count,
					'pages' 		=> $query->max_num_pages,
					'search_link' 	=> qa_search_link( $_POST['content']['s'] ),
					'search_term' 	=> $_POST['content']['s'],
					'test' 			=> $query
				)
			);
		} catch (Exception $e) {
			$resp = array(
				'success' => false,
				'msg' 	=> $e->getMessage()
			);
		}
		wp_send_json($resp);
	}

} // end class ET_ForumAjax

?>