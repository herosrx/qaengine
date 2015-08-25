<?php

class QA_Engine extends AE_Base{

	// declare post_types, scripts, styles ... which are uses in theme
	function __construct(){
		//parent::__construct();
		global $current_user;

		// disable admin bar if user can not manage options
		if (!current_user_can('manage_options')){
			show_admin_bar(false);
		};

		// register tag
		$this->add_action('init', 'init_theme');
		//block dashboard
		$this->add_action('admin_init', 'block_dashboard');

		//filter email message template
		$this->add_filter('et_reset_password_link'			, 'reset_password_link', 10, 3);
		$this->add_filter('et_retrieve_password_message'	, 'retrieve_password_message', 10, 3);
		$this->add_action('et_after_register'				, 'user_register_mail', 20 , 2);
		$this->add_action('qa_accept_answer'				, 'qa_after_accept_answer', 10, 2);
		$this->add_action('et_after_register'				, 'default_user_meta', 10 , 2);
		$this->add_action('et_password_reset'				, 'password_reset_mail', 10, 2);
		$this->add_action('widgets_init'					, 'et_widgets_init');
		$this->add_action('after_switch_theme'				, 'set_default_theme', 500);
		$this->add_filter('user_search_columns'				, 'user_search_columns_bd' , 10, 3);
		$this->add_filter('wp_title'						, 'qa_wp_title', 10, 2 );
		$this->add_filter('excerpt_length'					, 'qa_excerpt_length' );
		$this->add_filter('excerpt_more'					, 'qa_excerpt_more' );

		if( ae_get_option('qa_live_notifications') ){

		$this->add_filter( 'heartbeat_settings'			, 'change_hearbeat_rate');
		$this->add_filter( 'heartbeat_send'				, 'send_data_to_heartbeat', 10, 2 );
		$this->add_action( 'et_insert_question'			, 'store_new_question_to_DB');

		}

		$this->add_action( 'et_insert_question'			, 'alert_pending_question_to_admin' );

		$this->add_action( 'et_insert_question'			, 'save_following_questions');
		$this->add_action( 'et_insert_answer'			, 'save_following_questions' );
		//$this->add_action( 'ae_admin_user_action' 		, 'add_user_actions_backend');
		$this->add_action( 'qa_send_following_mail' 	, 'mail_to_following_users' );
		$this->add_action( 'add_meta_boxes'				, 'add_post_meta_box' );
		$this->add_action( 'et_after_reported'			, 'et_reported_email', 10, 2 );
		$this->add_filter( 'wp_link_query_args'			, 'qa_tinymce_filter_link_query' );

		//add return field for user
		$this->add_filter( 'ae_convert_user'			, 'ae_convert_user' );

		if(ae_get_option('qa_send_following_mail' ) && !ae_get_option("pending_answers"))
			$this->add_action( 'et_insert_answer'  ,'qa_questions_new_answer' );

		//short codes
		new QA_Shortcodes();

		// enqueue script and styles
		if ( is_admin() ){
			$this->add_action('admin_enqueue_scripts', 'on_add_scripts');
			$this->add_action('admin_print_styles', 'on_add_styles');
		} else {
			$this->add_action('wp_enqueue_scripts', 'on_add_scripts');
			$this->add_action('wp_print_styles', 'on_add_styles');
		}
		/* === filter bad words === */
		$this->add_filter( 'the_content', 'ae_filter_badword' );
		$this->add_filter( 'the_title', 'ae_filter_badword' );
		$this->add_filter( 'comment_text', 'ae_filter_badword' );
		$this->add_filter( 'qa_wp_title', 'ae_filter_badword' );
		/**
		 * bind ajax to get tag json for autocomplete tag in modal add/edit question
		*/
		$this->add_ajax('qa_get_tags' , 'qa_get_tags');

		/**
		 * load text domain
		*/
		add_action('after_setup_theme', array ( 'AE_Language' ,'load_text_domain' ) );
	}
	function ae_filter_badword($content){
        // filter badwords
		$filter_word     = ae_get_option('filter_keywords');
		$filter_keywords = explode(',', $filter_word);

        if(!empty($filter_keywords)){
        	foreach ($filter_keywords as $word) {
        		$content = str_replace(trim($word), '*** ', $content);
        	}
        }
		return $content;
	}
	/*
	* Send email to answer author when the answer is the best
	*
	*/
	function qa_after_accept_answer($answerID, $action){
		//get post data
		$answer       = get_post( $answerID );
		$question     = get_post( $answer->post_parent );
		$author       = get_user_by( 'id', $answer->post_author );

		$author_email = $author->user_email;

		$message = ae_get_option('accept_answer_mail_template');
		$message = stripslashes($message);
		$message = str_ireplace('[action]', $action == "accept-answer" ? __("marked", ET_DOMAIN) : __("unmarked", ET_DOMAIN), $message);
		$message = str_ireplace('[display_name]', $author->display_name, $message);
		$message = str_ireplace('[question_link]', get_permalink( $question->ID ), $message);
		$message = str_ireplace('[blogname]', get_option('blogname'), $message);

		$subject =	sprintf(__("[%s] Your answer has been marked as the best.",ET_DOMAIN),get_option('blogname'));

		$headers = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
		$headers .= "From: ".get_option('blogname')." < ".get_option('admin_email') ."> \r\n";

		if($author_email)
			wp_mail($author_email, $subject , $message, $headers) ;
	}
	function ae_convert_user($user){
		$user_instance           = QA_Member::get_instance();
		$user->register_status   = get_user_meta($user->ID, 'register_status', true) == "unconfirm" ? "unconfirm" : '';
		$user->banned            = $user_instance->is_ban($user->ID) ? true : false;
		$user->qa_point          = get_user_meta($user->ID, 'qa_point', true) ? get_user_meta($user->ID, 'qa_point', true) : 0;
		$user->et_question_count = et_count_user_posts($user->ID, 'question');
		$user->et_answer_count   = et_count_user_posts($user->ID, 'answer');
		return $user;
	}
	function qa_tinymce_filter_link_query($query){
		$query['post_type']   =	'question';
		$query['post_status'] =	array('publish','closed');
		return $query;
	}
	/**
	 *  Send email to admin after new pending question created
	 */
	function alert_pending_question_to_admin($id){
		if(ae_get_option( 'pending_questions' ) && get_post_status( $id ) == "pending"){
			$admin_email = apply_filters( 'email_alert_pending_question', get_option('admin_email') );

			$message =	ae_get_option('pending_question_mail_template');
			$message = 	stripslashes($message);
			$message =	str_ireplace('[question_title]', get_the_title( $id ), $message);
			$message =	str_ireplace('[pending_question_link]', et_get_page_link("pending"), $message);
			$message =	str_ireplace('[blogname]', get_option('blogname'), $message);

			$subject =	sprintf(__("[%s] New pending question has been created.",ET_DOMAIN),get_option('blogname'));

			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
			$headers .= "From: ".get_option('blogname')." < ".get_option('admin_email') ."> \r\n";

			wp_mail($admin_email, $subject , $message, $headers) ;
		}
	}
	/**
	 * Update option question has a new answer
	 */
	static public function qa_questions_new_answer($id){
		$answer    = get_post( $id );
		$id        = $answer->post_parent;
		$questions = (array)get_option( 'qa_questions_new_answer' );
		if(is_array($questions))
			array_push( $questions , $id);
		update_option( 'qa_questions_new_answer' , array_filter(array_unique( $questions )) );
	}
	/**
	 * Email to following user when thread has new reply
	 */
 	public function mail_to_following_users(){
		$questions = get_option( 'qa_questions_new_answer' );
		global $current_user;

		if(!empty($questions)){
			foreach ($questions as $id) {

				$question_title = get_the_title($id);
				$last_author    = get_post_meta( $id, 'et_last_author', true );
				$users_follow   = explode(',', get_post_meta($id, 'et_users_follow', true) );

				foreach ($users_follow as $userid) {

					$user    = get_user_by('id', $userid);

					$headers = 'MIME-Version: 1.0' . "\r\n";
					$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
					$headers .= "From: ".get_option('blogname')." < ".get_option('admin_email') ."> \r\n";
					$subject = sprintf(__("[New Answer] Your question %s has a new reply.",ET_DOMAIN), $question_title);

					$message = ae_get_option('new_answer_mail_template');
					$message = stripslashes($message);
					/* ============ filter placeholder ============ */
					$message =	str_ireplace('[display_name]', $user->display_name, $message);
					$message =	str_ireplace('[question_title]', $question_title, $message);
					$message =	str_ireplace('[question_link]', get_permalink($id), $message);
					$message =	str_ireplace('[blogname]', get_option('blogname'), $message);
					/* ============ filter placeholder ============ */

					// user email exist & user id != last author
					if($user->user_email && $userid != $last_author){
						wp_mail($user->user_email, $subject , $message, $headers);
					}
				}
			}
			update_option( 'qa_questions_new_answer' , array() );
		}
	}
	/**
	 * Save thread id to following to usermeta
	 */
	public function save_following_questions($id){
		global $user_ID;

		if(get_post_type($id) != "answer" && get_post_type($id) != "question") {
			return;
		}

		if(get_post_type($id) == "answer"){
			$answer = get_post( $id );
			$id     = $answer->post_parent;
			//update last author to question
			update_post_meta( $id, 'et_last_author', $answer->post_author );
		}

		$users_follow = explode(',', get_post_meta($id,'et_users_follow',true) );

		if(!in_array($user_ID, $users_follow)){
			$users_follow[] = $user_ID;
		}

		$users_follow = array_unique(array_filter($users_follow));
		$users_follow = implode(',', $users_follow);
		QA_Questions::update_field($id, 'et_users_follow', $users_follow);
	}
	/* ==================== LIVE NOTIFICATION ==================== */
	public function send_data_to_heartbeat($response, $data){

		global $wpdb, $current_user;

		$sql = $wpdb->prepare(
			"SELECT * FROM $wpdb->options WHERE option_name LIKE %s",
			'_transient_qa_notify_%'
		);

		$notifications = $wpdb->get_results( $sql );

		if(!empty($notifications)){
			foreach ( $notifications as $db_notification ) {

				$id = str_replace( '_transient_', '', $db_notification->option_name );

				if(ae_get_option( 'pending_questions' )){
					if ( false !== ( $notification = get_transient( $id ) )  && $notification['user'] != md5( $current_user->user_login ) && current_user_can( 'administrator' ) )
						$response['message'][ $id ] = $notification;
				} else {
					if ( false !== ( $notification = get_transient( $id ) )  && $notification['user'] != md5( $current_user->user_login ) )
						$response['message'][ $id ] = $notification;
				}

			}
		}

		return $response;
	}
	public function store_new_question_to_DB($post_id){

		global $current_user;

		if( get_post_type( $post_id ) != 'question')
			return $post_id;

		//if( get_option( 'pending_questions' ) && !current_user_can( 'administrator' ))
			//return $post_id;

		set_transient( 'qa_notify_' . mt_rand( 100000, 999999 ), array(
			'title'		=>		__( 'New Question', ET_DOMAIN ),
			'content'	=>	 	__( 'There\'s a new post, why don\'t you give a look at', ET_DOMAIN ) .
								' <a href="' . get_permalink( $post_id ) . '">' . get_the_title( $post_id ) . '</a>',
			'type'		=>		'update',
			'user'		=>	md5( $current_user->user_login )
		), 20 );

		return $post_id;
	}
	public function change_hearbeat_rate($settings){

		$settings['interval'] = 20;

		return $settings;
	}
	/* ==================== LIVE NOTIFICATION ==================== */
	public function add_user_actions_backend($user)	{
		$user = QA_Member::convert($user);
			if($user->register_status == "unconfirm"){
		?>
		<a class="action et-act-confirm" data-act="confirm" href="javascript:void(0)" title="<?php _e( 'Confirm this user', ET_DOMAIN ) ?>">
			<span class="icon" data-icon="3"></span>
		</a>
		<?php
			}
	}
	public function qa_excerpt_length(){
		return 20;
	}
	public function qa_excerpt_more( $more ) {
		return ' ...';
	}
	public function qa_wp_title( $title, $sep ) {
		global $paged, $page;

		if ( is_feed() )
			return $title;

		// Add the site name.
		$title .= get_bloginfo( 'name' );

		// Add the site description for the home/front page.
		$site_description = get_bloginfo( 'description', 'display' );
		if ( $site_description && ( is_home() || is_front_page() ) )
			$title = "$title $sep $site_description";

		// Add a page number if necessary.
		if ( $paged >= 2 || $page >= 2 )
			$title = "$title $sep " . sprintf( __( 'Page %s', ET_DOMAIN ), max( $paged, $page ) );

		return apply_filters( 'qa_wp_title', $title);
	}
	public function user_search_columns_bd($search_columns, $search, $vars){

	    if(!in_array('display_name', $search_columns)){
	        $search_columns[] = 'display_name';
	    }
	    return $search_columns;
	}

	public function set_default_theme(){

		$pages = array("profile","tags","users","search","badges","intro","categories");
		global $pagenow;

		if( is_admin() && isset($_GET['activated'] ) && $pagenow == 'themes.php' ){
			if( !get_option( 'qa_first_time_active' ) ){
				//add default page:
				foreach ($pages as $key => $page) {
					$id = wp_insert_post(array(
						'post_status' => "publish",
						'post_type'   => 'page',
						'post_title'  => ucfirst($page)
					));
					update_post_meta( $id, '_wp_page_template', 'page-'.$page.'.php' );
				}

				//set static front page
				$front_id  = get_option('page_on_front');
				if ( empty($front_id) ){
					$front = wp_insert_post(array(
						'post_status' => "publish",
						'post_type'   => 'page',
						'post_title'  => 'Questions Listing'
					));
					update_option( 'page_on_front' , $front );
					update_post_meta( $front, '_wp_page_template', 'page-questions.php' );
				}

				$posts_id  = get_option('page_for_posts');
				if (empty( $posts_id )){
					$post = wp_insert_post(array(
						'post_status' => "publish",
						'post_type'   => 'page',
						'post_title'  => 'Blog'
					));
					update_option( 'page_for_posts' , $post );
				}

				update_option( 'show_on_front' , "page" );
				update_option( 'qa_first_time_active', 1 );
			}
		}
	}

	public function et_widgets_init(){
		register_widget('QA_Hot_Questions_Widget');
		register_widget('QA_Statistic_Widget');
		register_widget('QA_Tags_Widget');
		register_widget('QA_Recent_Activity');
		register_widget('QA_Top_Users_Widget');
		register_widget('QA_Related_Questions_Widget');
	}
	public function retrieve_password_message($message , $active_key , $user_data) {
		$user_login 	=   $user_data->user_login;
		$forgot_message =	ae_get_option('forgotpass_mail_template');
		$forgot_message = 	stripslashes($forgot_message);
		$activate_url	= 	apply_filters('et_reset_password_link',  network_site_url("wp-login.php?action=rp&key=$active_key&login=" . rawurlencode($user_login), 'login'), $active_key, $user_login );

		$forgot_message	=	et_filter_authentication_placeholder ( $forgot_message, $user_data->ID );
		$forgot_message	=	str_ireplace('[activate_url]', $activate_url, $forgot_message);

		return $forgot_message;
	}
	public function password_reset_mail ( $user, $new_pass ) {
		$new_pass_msg	=	ae_get_option('resetpass_mail_template');
		$new_pass_msg   = 	stripslashes($new_pass_msg);
		$new_pass_msg	=	et_filter_authentication_placeholder($new_pass_msg, $user->ID);

		$subject 		=	apply_filters('et_reset_pass_mail_subject',__('Password updated successfully!', ET_DOMAIN));

		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
		$headers .= "From: ".get_option('blogname')." < ".get_option('admin_email') ."> \r\n";

		wp_mail($user->user_email, $subject , $new_pass_msg, $headers);
	}
	public function user_register_mail( $user_id, $role = false) {

		$user			=   new WP_User($user_id);
		$user_email		=	$user->user_email;

		if(ae_get_option( 'user_confirm' )){
			$message		=	ae_get_option('confirm_mail_template');
		} else {
			$message		=	ae_get_option('register_mail_template');
		}
		$message   = 	stripslashes($message);
		$message		=	et_filter_authentication_placeholder ( $message, $user_id );
		$subject		=	sprintf(__("Congratulations! You have successfully registered to %s.",ET_DOMAIN),get_option('blogname'));

		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
		$headers .= "From: ".get_option('blogname')." < ".get_option('admin_email') ."> \r\n";

		wp_mail($user_email, $subject , $message, $headers) ;

	}
	public function default_user_meta( $user_id, $role = false) {
		$user = get_user_by( 'id',$user_id );

		update_user_meta( $user_id, 'qa_point', apply_filters('qa_default_points_after_register', 1) );
		update_user_meta( $user_id, 'et_question_count', 0 );
		update_user_meta( $user_id, 'et_answer_count', 0 );
		update_user_meta( $user_id, 'key_confirm', md5($user->user_email) );

		if(ae_get_option( 'user_confirm' ))
			update_user_meta( $user_id, 'register_status', 'unconfirm' );
	}
	public function block_dashboard() {
		if ( ! current_user_can( 'manage_options' ) && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
			wp_redirect( home_url() );
			exit;
		}
	}
	public function init_theme(){
		global $wp_rewrite;
		// post type
		QA_Questions::init();
		QA_Answers::init();
		QA_Member::init();

		if(ae_get_option('twitter_login', false))
			new ET_TwitterAuth();
		if(ae_get_option('facebook_login', false)){
			new ET_FaceAuth();
		}
		if(ae_get_option('gplus_login', false)){
			new ET_GoogleAuth();
		}

		/**
		 * new class QA_PackAction to control all action do with user badge
		*/
		$qa_pack = new QA_PackAction();

		// register footer menu
		register_nav_menus ( array(
			'et_header' => __('Menu display on Header',ET_DOMAIN),
			'et_left'	=>	__('Menu display on Left Sidebar',ET_DOMAIN)
		));

		//sidebars
		register_sidebar( array(
			'name' 			=> __('Left Sidebar', ET_DOMAIN),
			'id' 			=> 'qa-left-sidebar',
			'description' 	=> __("Display widgets in left sidebar", ET_DOMAIN)
		) );
		register_sidebar( array(
			'name' 			=> __('Right Sidebar', ET_DOMAIN),
			'id' 			=> 'qa-right-sidebar',
			'description' 	=> __("Display widgets in right sidebar", ET_DOMAIN)
		) );

		//header sidebars
		register_sidebar( array(
			'name' 			=> __('Header Sidebar', ET_DOMAIN),
			'id' 			=> 'qa-header-sidebar',
			'description' 	=> __("Display widgets in header sidebar", ET_DOMAIN)
		) );

		//blog sidebars
		register_sidebar( array(
			'name' 			=> __('Blog\'s Left Sidebar', ET_DOMAIN),
			'id' 			=> 'qa-blog-left-sidebar',
			'description' 	=> __("Display widgets in blog's left sidebar", ET_DOMAIN)
		) );
		register_sidebar( array(
			'name' 			=> __('Blog\'s Right Sidebar', ET_DOMAIN),
			'id' 			=> 'qa-blog-right-sidebar',
			'description' 	=> __("Display widgets in blog's right sidebar", ET_DOMAIN)
		) );

		//single question sidebars
		register_sidebar( array(
			'name' 			=> __('Single Question Sidebar', ET_DOMAIN),
			'id' 			=> 'qa-question-right-sidebar',
			'description' 	=> __("Display widgets in single question sidebar", ET_DOMAIN)
		) );

		register_sidebar( array(
			'name' 			=> __('Top Questions Listing Ads Banner Sidebar', ET_DOMAIN),
			'id' 			=> 'qa-top-questions-banner-sidebar',
			'description' 	=> __("Display ad banners widgets in top questions listing sidebar", ET_DOMAIN)
		) );

		register_sidebar( array(
			'name' 			=> __('Bottom Ads Banner Sidebar', ET_DOMAIN),
			'id' 			=> 'qa-btm-questions-banner-sidebar',
			'description' 	=> __("Display ad banners widgets in bottom of website", ET_DOMAIN)
		) );

		register_sidebar( array(
			'name' 			=> __('Content Question Ad Banner Sidebar', ET_DOMAIN),
			'id' 			=> 'qa-content-question-banner-sidebar',
			'description' 	=> __("Display ad banners widgets in bottom questions listing sidebar", ET_DOMAIN)
		) );

		register_sidebar( array(
			'name' 			=> __('Below Answers Listing Ad Banner Sidebar', ET_DOMAIN),
			'id' 			=> 'qa-btm-single-question-banner-sidebar',
			'description' 	=> __("Display ad banners widgets in bottom questions listing sidebar", ET_DOMAIN)
		) );

		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'post-thumbnails', array('post') );

	    $author_slug = apply_filters( 'qa_member_slug', 'member' ); // change slug name
	    $wp_rewrite->author_base = $author_slug;

	    /**
		 * create post type report
		*/
		$args = array(
			'labels' => array(
				'name'               => __('Reports', ET_DOMAIN ),
				'singular_name'      => __('Report', ET_DOMAIN ),
				'add_new'            => __('Add New', ET_DOMAIN ),
				'add_new_item'       => __('Add New Report', ET_DOMAIN ),
				'edit_item'          => __('Edit Report', ET_DOMAIN ),
				'new_item'           => __('New Report', ET_DOMAIN ),
				'all_items'          => __('All Reports', ET_DOMAIN ),
				'view_item'          => __('View Report', ET_DOMAIN ),
				'search_items'       => __('Search Reports', ET_DOMAIN ),
				'not_found'          => __('No Reports found', ET_DOMAIN ),
				'not_found_in_trash' => __('No Reports found in Trash', ET_DOMAIN ),
				'parent_item_colon'  => '',
				'menu_name'          => __('Reports', ET_DOMAIN )
			),
			'public'              => true,
			'publicly_queryable'  => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'query_var'           => true,
			'rewrite'             => array( 'slug' => 'report'),
			'capability_type'     => 'post',
			'has_archive'         => 'reports',
			'hierarchical'        => false,
			'menu_position'       => null,
			'supports'            => array( 'title', 'editor', 'author'),
			'taxonomies'          => array('report-taxonomy')
		);
		register_post_type( 'report', $args );

		$tax_labels = array(
			'name'                       => _x( 'Reports taxonomy', ET_DOMAIN ),
			'singular_name'              => _x( 'Report taxonomys', ET_DOMAIN ),
			'search_items'               => __( 'Search Reports', ET_DOMAIN ),
			'popular_items'              => __( 'Popular Reports', ET_DOMAIN ),
			'all_items'                  => __( 'All Reports', ET_DOMAIN ),
			'parent_item'                => null,
			'parent_item_colon'          => null,
			'edit_item'                  => __( 'Edit Report', ET_DOMAIN ),
			'update_item'                => __( 'Update Report', ET_DOMAIN ),
			'add_new_item'               => __( 'Add New Report', ET_DOMAIN  ),
			'new_item_name'              => __( 'New Report Name', ET_DOMAIN ),
			'separate_items_with_commas' => __( 'Separate Reports with commas', ET_DOMAIN ),
			'add_or_remove_items'        => __( 'Add or remove Reports', ET_DOMAIN ),
			'choose_from_most_used'      => __( 'Choose from the most used Reports', ET_DOMAIN ),
			'not_found'                  => __( 'No Reports found.', ET_DOMAIN ),
			'menu_name'                  => __( 'Reports taxonomy', ET_DOMAIN ),
		);
		$tax_args = array(
			'hierarchical'          => true,
			'labels'                => $tax_labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var'             => true,
			'rewrite'               => array( 'slug' => 'Report-taxonomy' ),
		);
		register_taxonomy( 'report-taxonomy', 'report', $tax_args );
	}
	/**
	 * All about meta boxes in backend
	 */
	function add_post_meta_box(){
		add_meta_box( 'thread_info',
			__('Report Information', ET_DOMAIN),
			array($this, 'meta_box_view'),
			'report',
			'normal',
			'high' );
	}
	function meta_box_view($post){
		?>
		<p>Click this link below to view thread:</p>
		<p>
			<a href="<?php echo get_post_meta($post->ID, '_link_report', true) ?>">
				<?php echo get_post_meta($post->ID, '_link_report', true) ?>
			</a>
		</p>
		<?php
	}
	public function on_add_scripts(){
		global $current_user, $pagenow;

		if($pagenow == "customize.php") return;

		$isEditable = current_user_can( 'manage_questions' );
		$variables = array(
			'ajaxURL'           => apply_filters( 'ae_ajax_url', admin_url('admin-ajax.php') ),
			'imgURL'            => TEMPLATEURL.'/img/',
			'posts_per_page'    => get_option('posts_per_page'),
			'homeURL'           => home_url(),
			'user_confirm'      => ae_get_option('user_confirm') ? 1 : 0 ,
			'pending_questions' => ae_get_option('pending_questions') ? 1 : 0,
			'pending_answers'   => ae_get_option("pending_answers") ? 1 : 0,
			'introURL'          => et_get_page_link('intro'),
			'gplus_client_id'   => ae_get_option("gplus_client_id"),
			'plupload_config'   => array(
				'max_file_size'       => '3mb',
				'url'                 => admin_url('admin-ajax.php'),
				'flash_swf_url'       => includes_url('js/plupload/plupload.flash.swf'),
				'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
		));

		?>
		<script type="text/javascript">
			ae_globals = <?php echo json_encode($variables) ?>
		</script>
		<?php
	}
	public function on_add_styles(){}

	/**
	 * Write some method specified for forumengine only ...
	 */

	public function reset_password_link($link, $key, $user_login){
		return add_query_arg(array('user_login' => $user_login, 'key' => $key), home_url());
	}

	public function qa_get_tags() {
		$terms	=	get_terms('qa_tag', array('hide_empty' => 0, 'fields' => 'names' )) ;
		wp_send_json($terms);
	}
	/**
	 * Send email after report success
	 */
	public function et_reported_email($thread_id, $report_message){
		global $current_user;
		if($thread_id && $report_message){
			$thread = get_post( $thread_id );
			$user_send 		= get_users( 'role=administrator' );
			foreach ( $user_send as $user ) {
				$user_email			=	$user->user_email;

				$message =	ae_get_option('report_mail_template');

				/* ============ filter placeholder ============ */
				$message  	=	str_ireplace('[display_name]', $user->display_name, $message);
				$message  	=	str_ireplace('[thread_title]', $thread->post_title, $message);
				$message  	=	str_ireplace('[thread_content]', $thread->post_content, $message);
				$message  	=	str_ireplace('[thread_link]', get_permalink($thread_id), $message);
				$message  	=	str_ireplace('[report_message]',$report_message, $message);
				$message  	=	str_ireplace('[blogname]', get_option('blogname'), $message);
				$message	=	et_filter_authentication_placeholder ( $message, $user->ID);

				$subject	=	'[#'.$thread_id.']'.__("There's a new report ",ET_DOMAIN);

				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
				$headers .= "From: ".get_option('blogname')." < ".$current_user->user_email."> \r\n";

				if($user_email){
					$test = wp_mail($user_email, $subject , $message, $headers) ;
				}
			}
		}
	}
}

class QA_Shortcodes{
	public function __construct(){
		$this->add_shortcode( 'img', 'img' );
		//$this->add_shortcode( 'quote', 'quote' );
		$this->add_shortcode( 'code', 'code' );
		add_filter('comment_text', 'do_shortcode');
		do_action('et_add_shortcodes');
	}

	function img($atts, $content = ""){
		return '<img class="img-responsive" src="' . $content . '">';
	}

	function code($atts, $content = ''){
		extract( shortcode_atts( array(
				'type'      => 'php',
				'start'     => 1,
				'highlight' => ''
			), $atts ) );

		$content = preg_replace('#<br\s*/?>#i', "\n", $content);
		$content = str_replace("<br>", "\n", $content);
		$content = str_replace("<p></p>", "", $content);
		$content = str_replace("<p>", "", $content);
		$content = str_replace("</p>", "", $content);

		return '<pre class="ruler: true;brush: '.$type.';toolbar: false;highlight: ['.$highlight.'];first-line: '.$start.';">'.do_shortcode( $content ).'</pre>';
	}

	function quote($atts, $content = ''){
		extract( shortcode_atts( array(
				'author' => '',
			), $atts ) );
		return '<blockquote>' . do_shortcode( $content ) . '</blockquote>';
	}

	private function add_shortcode($name, $callback){
		add_shortcode( $name, array($this, $callback) );
	}
}

/**
 * process uploaded image: save to upload_dir & create multiple sizes & generate metadata
 * @param  [type]  $file     [the $_FILES['data_name'] in request]
 * @param  [type]  $author   [ID of the author of this attachment]
 * @param  integer $parent=0 [ID of the parent post of this attachment]
 * @param  array [$mimes] [array of supported file extensions]
 * @return [int/WP_Error]	[attachment ID if successful, or WP_Error if upload failed]
 * @author anhcv
 */
function et_process_file_upload( $file, $author=0, $parent=0, $mimes=array() ){

	global $user_ID;
	$author = ( 0 == $author || !is_numeric($author) ) ? $user_ID : $author;
	//print_r($file);
	if( isset($file['name']) && $file['size'] > 0 && $file['size'] < 1024*1024){

		// setup the overrides
		$overrides['test_form']	= false;
		if( !empty($mimes) && is_array($mimes) ){
			$overrides['mimes']	= $mimes;
		}
		require_once(ABSPATH . "wp-admin" . '/includes/file.php');
		require_once(ABSPATH . "wp-admin" . '/includes/image.php');
		// this function also check the filetype & return errors if having any
		$uploaded_file	=	wp_handle_upload( $file, $overrides );

		//if there was an error quit early
		if ( isset( $uploaded_file['error'] )) {
			return new WP_Error( 'upload_error', $uploaded_file['error'] );
		}
		elseif(isset($uploaded_file['file'])) {

			// The wp_insert_attachment function needs the literal system path, which was passed back from wp_handle_upload
			$file_name_and_location = $uploaded_file['file'];

			// Generate a title for the image that'll be used in the media library
			$file_title_for_media_library = preg_replace('/\.[^.]+$/', '', basename($file['name']));

			$wp_upload_dir = wp_upload_dir();

			// Set up options array to add this file as an attachment
			$attachment = array(
				'guid'				=> $uploaded_file['url'],
				'post_mime_type'	=> $uploaded_file['type'],
				'post_title'		=> $file_title_for_media_library,
				'post_content'		=> '',
				'post_status'		=> 'inherit',
				'post_author'		=> $author
			);

			// Run the wp_insert_attachment function. This adds the file to the media library and generates the thumbnails. If you wanted to attch this image to a post, you could pass the post id as a third param and it'd magically happen.
			$attach_id = wp_insert_attachment( $attachment, $file_name_and_location, $parent );

			$attach_data = wp_generate_attachment_metadata( $attach_id, $file_name_and_location );
			wp_update_attachment_metadata($attach_id,  $attach_data);
			return $attach_id;

		} else { // wp_handle_upload returned some kind of error. the return does contain error details, so you can use it here if you want.
			return new WP_Error( 'upload_error', __( 'There was a problem with your upload.', ET_DOMAIN ) );
		}
	}
	else { // No file was passed
		return new WP_Error( 'upload_error', __( 'Image\'s size upload must be less than 1MB!', ET_DOMAIN ) );
	}
}

/**
 * Print the content with shortcode
 */
function et_the_content($more_link_text = null, $stripteaser = false){
	$content = get_the_content($more_link_text, $stripteaser);
	$content = apply_filters( 'et_the_content', $content );
	$content = str_replace(']]>', ']]&gt;', $content);
	echo $content;
}

add_filter('et_the_content', 'et_the_content_filter');
function et_the_content_filter($content){
	add_filter('the_content', 'do_shortcode', 11);
	$content = apply_filters( 'the_content', $content );
	remove_filter('the_content', 'do_shortcode');
	return $content;
}

function et_the_content_edit($content){
	if(is_contain_ytd_vm($content)){
		return apply_filters( 'the_content', $content );
	} else {
		return wpautop(nl2br($content));
	}
}
function is_contain_ytd_vm($content){
	if ( strpos($content, "youtube.com") !== false || strpos($content, "youtu.be") !== false || strpos($content, "vimeo.com") !== false ) {
	    return true;
	} else {
	    return false;
	}
}

/**
 * Get editor default settings
 * @param array $args overwrite settings
 */
function editor_settings($args = array()){
	$buttons = apply_filters( 'qa_editor_buttons', 'bold,|,italic,|,underline,|,link,unlink,|,bullist,numlist,qaimage,qacode' );
	return array(
	'quicktags' 	=> false,
	'media_buttons' => false,
	'tabindex' 		=> 5,
	'textarea_name' => 'post_content',
	'tinymce' 		=> array(
		'content_css'           => get_template_directory_uri() . '/css/editor_content.css',
		'height'                => 150,
		'toolbar1'              => $buttons,
		'toolbar2'              => '',
		'toolbar3'              => '',
		'autoresize_min_height' => 150,
		'force_p_newlines'      => false,
		'statusbar'             => false,
		'force_br_newlines'     => false,
		'forced_root_block'     => '',
		'setup'                 => 'function(ed) {
			ed.on("keyup", function(e) {
				if ( typeof hasChange == "undefined" ) {
					hasChange = true;
				}

				var content = ed.getContent(),
				textarea    = jQuery("#insert_question"),
				container   = textarea.parent();
				label       = container.find("label.error");

				if(content){
					label.hide();
					textarea.val(content).removeClass("error").addClass("valid");
				} else {
					label.show();
					textarea.removeClass("valid").addClass("error");
				}
			});
			ed.on("focus", function(e) {
				if(currentUser.ID == 0)
					QAEngine.App.openAuthModal(e);
			});
			ed.onPaste.add(function(ed, e) {
				if ( typeof hasChange == "undefined" ) {
					hasChange = true;
				}
			});
	   }'
	));
}

function et_filter_authentication_placeholder ($content, $user_id) {
		$user 		=	new WP_User ($user_id);

		$content =	str_ireplace('[user_login]'		, $user->user_login, $content);
		$content =	str_ireplace('[user_name]'		, $user->user_login, $content);
		$content =	str_ireplace('[user_nicename]'	, ucfirst( $user->user_nicename ), $content);
		$content =	str_ireplace('[user_email]'		, $user->user_email, $content);
		$content =	str_ireplace('[blogname]'		, get_bloginfo( 'name' ), $content);
		$content =	str_ireplace('[display_name]'	, ucfirst( $user->display_name ), $content);
		$content =	str_ireplace('[company]'		, ucfirst( $user->display_name ) , $content);
		$content =	str_ireplace('[dashboard]'		, et_get_page_link('dashboard'), $content);
		$content =	str_ireplace('[site_url]'		, home_url(), $content);
		$content =	str_ireplace('[confirm_link]'	, add_query_arg(array('act' => 'confirm', 'key'=>md5($user->user_email)),home_url()), $content);

		return $content;
}

/**
 * Edit WP_NAV_MENUs HTML list of nav menu items.
 *
 * @since 1.0
 * @uses Walker
 */
class QA_Custom_Walker_Nav_Menu extends Walker_Nav_Menu {
	/**
	 * Start the element output.
	 *
	 * @see Walker::start_el()
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item   Menu item data object.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   An array of arguments. @see wp_nav_menu()
	 * @param int    $id     Current item ID.
	 */
	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		$custom_class = isset($item->classes[0]) ? $item->classes[0] : '';
		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		$class_names = '';

		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$classes[] = 'menu-item-' . $item->ID;

		/**
		 * Filter the CSS class(es) applied to a menu item's <li>.
		 *
		 * @since 3.0.0
		 *
		 * @see wp_nav_menu()
		 *
		 * @param array  $classes The CSS classes that are applied to the menu item's <li>.
		 * @param object $item    The current menu item.
		 * @param array  $args    An array of wp_nav_menu() arguments.
		 */
		$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
		$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

		/**
		 * Filter the ID applied to a menu item's <li>.
		 *
		 * @since 3.0.1
		 *
		 * @see wp_nav_menu()
		 *
		 * @param string $menu_id The ID that is applied to the menu item's <li>.
		 * @param object $item    The current menu item.
		 * @param array  $args    An array of wp_nav_menu() arguments.
		 */
		$id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args );
		$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

		$output .= $indent . '<li' . $id . $class_names .'>';

		$atts = array();
		$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
		$atts['target'] = ! empty( $item->target )     ? $item->target     : '';
		$atts['rel']    = ! empty( $item->xfn )        ? $item->xfn        : '';
		$atts['href']   = ! empty( $item->url )        ? $item->url        : '';

		/**
		 * Filter the HTML attributes applied to a menu item's <a>.
		 *
		 * @since 3.6.0
		 *
		 * @see wp_nav_menu()
		 *
		 * @param array $atts {
		 *     The HTML attributes applied to the menu item's <a>, empty strings are ignored.
		 *
		 *     @type string $title  Title attribute.
		 *     @type string $target Target attribute.
		 *     @type string $rel    The rel attribute.
		 *     @type string $href   The href attribute.
		 * }
		 * @param object $item The current menu item.
		 * @param array  $args An array of wp_nav_menu() arguments.
		 */
		$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args );

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( ! empty( $value ) ) {
				$value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}

		$item_output = $args->before;
		$custom_icon = $custom_class ? '<i class="fa '.$custom_class.'"></i>' : '';
		$item_output .= '<a'. $attributes .'>'.$custom_icon;
		/** This filter is documented in wp-includes/post-template.php */
		$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
		$item_output .= '</a>';
		$item_output .= $args->after;

		/**
		 * Filter a menu item's starting output.
		 *
		 * The menu item's starting output only includes $args->before, the opening <a>,
		 * the menu item's title, the closing </a>, and $args->after. Currently, there is
		 * no filter for modifying the opening and closing <li> for a menu item.
		 *
		 * @since 3.0.0
		 *
		 * @see wp_nav_menu()
		 *
		 * @param string $item_output The menu item's starting HTML output.
		 * @param object $item        Menu item data object.
		 * @param int    $depth       Depth of menu item. Used for padding.
		 * @param array  $args        An array of wp_nav_menu() arguments.
		 */
		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}
}
function et_count_posts($status = 'publish', $type = 'question'){
	$count = wp_count_posts($type);
	return $count->$status;
}
/**
*
* Return the array of static texts
*
**/
function qa_static_texts(){
	return 	array(
		'form_auth'	=> array(
			'error_msg'      => __("Please fill out all fields required.", ET_DOMAIN),
			'error_user'     => __("Please enter your user name.", ET_DOMAIN),
			'error_email'    => __("Please enter a valid email address.", ET_DOMAIN),
			'error_username' => __("Please enter a valid username.", ET_DOMAIN),
			'error_repass'   => __("Please enter the same password as above.", ET_DOMAIN),
			'error_url'      => __("Please enter a valid URL.", ET_DOMAIN),
			'error_cb'       => __("You must accept the term & privacy.", ET_DOMAIN),
		),
		'texts' => array(
			'require_login'   => __("You must be logged in to perform this action.", ET_DOMAIN),
			'enought_points'  => __("You don't have enought points to perform this action.", ET_DOMAIN),
			'create_topic'    => __("Create Topic", ET_DOMAIN),
			'upload_images'   => __("Upload Images", ET_DOMAIN),
			'insert_codes'    => __("Insert Code", ET_DOMAIN),
			'no_file_choose'  => __("No file chosen.", ET_DOMAIN),
			'require_tags'    => __("Please insert at least one tag.", ET_DOMAIN),
			'add_comment'     => __("Add comment", ET_DOMAIN),
			'cancel'          => __("Cancel", ET_DOMAIN),
			'sign_up'         => __("Sign Up", ET_DOMAIN),
			'sign_in'         => __("Sign In", ET_DOMAIN),
			'accept_txt'      => __("Accept", ET_DOMAIN),
			'best_ans_txt'    => __("Best answer", ET_DOMAIN),
			'forgotpass'      => __("Forgot Password", ET_DOMAIN),
			'close_tab'       => __("You have made some changes which you might want to save.", ET_DOMAIN),
			'confirm_account' => __("You must activate your account first to create questions / answers!.", ET_DOMAIN),
			'cancel_auth'     => __("User cancelled login or did not fully authorize.", ET_DOMAIN),
			'banned_account'  => __('You account has been banned, you can\'t make this action!', ET_DOMAIN),
		)
	);
}
/**
*
* Insert post link to listing report post type
*
**/
add_filter('manage_report_posts_columns' , 'report_cpt_columns');
add_action( 'manage_report_posts_custom_column' , 'custom_report_column', 10,2 );
function report_cpt_columns($columns) {

	$new_columns = array(
		'post_link' => __('Post link', ET_DOMAIN),
	);
    return array_merge($columns, $new_columns);
}
function custom_report_column( $column, $post_id ) {
    switch ( $column ) {

        case 'post_link' :
            $post_link = get_post_meta($post_id, '_link_report', true);
            if ($post_link)
                echo '<a target ="_blank" href ="'.$post_link.'" >'. $post_link.'</a>';
            else
                _e( 'Unable to get post link', ET_DOMAIN);
            break;
        default:

        	break;
    }
}
// insert rel nofollow to a link
add_filter( 'the_content', 'add_nofollow_blank_link');
function add_nofollow_blank_link( $content ) {

	$regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>";
	if(preg_match_all("/$regexp/siU", $content, $matches, PREG_SET_ORDER)) {
		if( !empty($matches) ) {

			$srcUrl = get_option('siteurl');
			for ($i=0; $i < count($matches); $i++)
			{

				$tag = $matches[$i][0];
				$tag2 = $matches[$i][0];
				$url = $matches[$i][0];

				$noFollow = '';

				$pattern = '/target\s*=\s*"\s*_blank\s*"/';
				preg_match($pattern, $tag2, $match, PREG_OFFSET_CAPTURE);
				if( count($match) < 1 )
					$noFollow .= ' target="_blank" ';

				$pattern = '/rel\s*=\s*"\s*[n|d]ofollow\s*"/';
				preg_match($pattern, $tag2, $match, PREG_OFFSET_CAPTURE);
				if( count($match) < 1 )
					$noFollow .= ' rel="nofollow" ';

				$pos = strpos($url,$srcUrl);
				if ($pos === false) {
					$tag = rtrim ($tag,'>');
					$tag .= $noFollow.'>';
					$content = str_replace($tag2,$tag,$content);
				}
			}
		}
	}

	$content = str_replace(']]>', ']]&gt;', $content);
	return $content;
}
/**

* Shorten long numbers to K / M / B
* @author Thái NT
* @since 1.3
* @package QAEngine

*/
function custom_number_format($n, $precision = 1) {
    // first strip any formatting;
    $n = (0+str_replace(",","",$n));

    // is this a number?
    if(!is_numeric($n)) return false;

    // now filter it;
    if($n >= 1000000000000) return round(($n/1000000000000),1).'T';
    else if($n >= 1000000000) return round(($n/1000000000),1).'B';
    else if($n >= 1000000) return round(($n/1000000),1).'M';
    else if($n >= 1000) return round(($n/1000),1).'K';

    return number_format($n);
}