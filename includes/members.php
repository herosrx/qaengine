<?php

/**
 * extends WP_User_Query to override prepare function
*/
class QA_User_Query extends WP_User_Query {
	/**
	 * Prepare the query variables.
	 *
	 * @since 3.1.0
	 *
	 * @param string|array $args Optional. The query variables.
	 */
	function prepare_query( $query = array() ) {
		global $wpdb;

		if ( empty( $this->query_vars ) || ! empty( $query ) ) {
			$this->query_limit = null;
			$this->query_vars = wp_parse_args( $query, array(
				'blog_id'        => $GLOBALS['blog_id'],
				'role'           => '',
				'meta_key'       => '',
				'meta_value'     => '',
				'meta_compare'   => '',
				'include'        => array(),
				'exclude'        => array(),
				'search'         => '',
				'search_columns' => array(),
				'orderby'        => 'login',
				'order'          => 'ASC',
				'offset'         => '',
				'number'         => '',
				'count_total'    => true,
				'fields'         => 'all',
				'who'            => ''
			) );
		}

		$qv =& $this->query_vars;

		if ( is_array( $qv['fields'] ) ) {
			$qv['fields'] = array_unique( $qv['fields'] );

			$this->query_fields = array();
			foreach ( $qv['fields'] as $field ) {
				$field = 'ID' === $field ? 'ID' : sanitize_key( $field );
				$this->query_fields[] = "$wpdb->users.$field";
			}
			$this->query_fields = implode( ',', $this->query_fields );
		} elseif ( 'all' == $qv['fields'] ) {
			$this->query_fields = "$wpdb->users.*";
		} else {
			$this->query_fields = "$wpdb->users.ID";
		}

		if ( isset( $qv['count_total'] ) && $qv['count_total'] )
			$this->query_fields = 'SQL_CALC_FOUND_ROWS ' . $this->query_fields;

		$this->query_from = "FROM $wpdb->users";
		$this->query_where = "WHERE 1=1";

		// sorting
		if ( isset( $qv['orderby'] ) ) {
			if ( in_array( $qv['orderby'], array('nicename', 'email', 'url', 'registered') ) ) {
				$orderby = 'user_' . $qv['orderby'];
			} elseif ( in_array( $qv['orderby'], array('user_nicename', 'user_email', 'user_url', 'user_registered') ) ) {
				$orderby = $qv['orderby'];
			} elseif ( 'name' == $qv['orderby'] || 'display_name' == $qv['orderby'] ) {
				$orderby = 'display_name';
			} elseif ( 'post_count' == $qv['orderby'] ) {
				// todo: avoid the JOIN
				$where = get_posts_by_author_sql('ad');
				$this->query_from .= " LEFT OUTER JOIN (
					SELECT post_author, COUNT(*) as post_count
					FROM $wpdb->posts
					$where
					GROUP BY post_author
				) p ON ({$wpdb->users}.ID = p.post_author)
				";
				$orderby = 'post_count';
			} elseif ( 'ID' == $qv['orderby'] || 'id' == $qv['orderby'] ) {
				$orderby = 'ID';
			} elseif ( 'meta_value' == $qv['orderby'] ) {
				$orderby = "$wpdb->usermeta.meta_value";
			} elseif ( 'meta_value_num' == $qv['orderby'] ) {
				$orderby = "$wpdb->usermeta.meta_value + 0";
			} else {
				$orderby = 'user_login';
			}
		}

		if ( empty( $orderby ) )
			$orderby = 'user_login';

		$qv['order'] = isset( $qv['order'] ) ? strtoupper( $qv['order'] ) : '';
		if ( 'ASC' == $qv['order'] )
			$order = 'ASC';
		else
			$order = 'DESC';
		$this->query_orderby = "ORDER BY $orderby $order";

		// limit
		if ( isset( $qv['number'] ) && $qv['number'] ) {
			if ( $qv['offset'] )
				$this->query_limit = $wpdb->prepare("LIMIT %d, %d", $qv['offset'], $qv['number']);
			else
				$this->query_limit = $wpdb->prepare("LIMIT %d", $qv['number']);
		}

		$search = '';
		if ( isset( $qv['search'] ) )
			$search = trim( $qv['search'] );

		if ( $search ) {
			$leading_wild = ( ltrim($search, '*') != $search );
			$trailing_wild = ( rtrim($search, '*') != $search );
			if ( $leading_wild && $trailing_wild )
				$wild = 'both';
			elseif ( $leading_wild )
				$wild = 'leading';
			elseif ( $trailing_wild )
				$wild = 'trailing';
			else
				$wild = false;
			if ( $wild )
				$search = trim($search, '*');

			$search_columns = array();
			if ( $qv['search_columns'] )
				$search_columns = array_intersect( $qv['search_columns'], array( 'ID', 'user_login', 'user_email', 'user_url', 'user_nicename' ) );
			if ( ! $search_columns ) {
				if ( false !== strpos( $search, '@') )
					$search_columns = array('user_email');
				elseif ( is_numeric($search) )
					$search_columns = array('user_login', 'ID');
				elseif ( preg_match('|^https?://|', $search) && ! ( is_multisite() && wp_is_large_network( 'users' ) ) )
					$search_columns = array('user_url');
				else
					$search_columns = array('user_login', 'user_nicename');
			}

			/**
			 * Filter the columns to search in a WP_User_Query search.
			 *
			 * The default columns depend on the search term, and include 'user_email',
			 * 'user_login', 'ID', 'user_url', and 'user_nicename'.
			 *
			 * @since 3.6.0
			 *
			 * @param array         $search_columns Array of column names to be searched.
			 * @param string        $search         Text being searched.
			 * @param WP_User_Query $this           The current WP_User_Query instance.
			 */
			$search_columns = apply_filters( 'user_search_columns', $search_columns, $search, $this );

			$this->query_where .= $this->get_search_sql( $search, $search_columns, $wild );
		}

		$blog_id = 0;
		if ( isset( $qv['blog_id'] ) )
			$blog_id = absint( $qv['blog_id'] );

		if ( isset( $qv['who'] ) && 'authors' == $qv['who'] && $blog_id ) {
			$qv['meta_key'] = $wpdb->get_blog_prefix( $blog_id ) . 'user_level';
			$qv['meta_value'] = 0;
			$qv['meta_compare'] = '!=';
			$qv['blog_id'] = $blog_id = 0; // Prevent extra meta query
		}

		$role = '';
		if ( isset( $qv['role'] ) )
			$role = trim( $qv['role'] );

		if ( $blog_id && ( $role || is_multisite() ) ) {
			$cap_meta_query = array();
			$cap_meta_query['key'] = $wpdb->get_blog_prefix( $blog_id ) . 'capabilities';

			if ( $role ) {
				$cap_meta_query['value'] = '"' . $role . '"';
				$cap_meta_query['compare'] = 'like';
			}

			if ( empty( $qv['meta_query'] ) || ! in_array( $cap_meta_query, $qv['meta_query'], true ) ) {
				$qv['meta_query'][] = $cap_meta_query;
			}
		}

		$meta_query = new WP_Meta_Query();
		$meta_query->parse_query_vars( $qv );

		if ( !empty( $meta_query->queries ) ) {
			$clauses = $meta_query->get_sql( 'user', $wpdb->users, 'ID', $this );
			$this->query_from .= $clauses['join'];
			$this->query_where .= $clauses['where'];

			if ( 'OR' == $meta_query->relation )
				$this->query_fields = 'DISTINCT ' . $this->query_fields;
		}

		if ( ! empty( $qv['include'] ) ) {
			$ids = implode( ',', wp_parse_id_list( $qv['include'] ) );
			$this->query_where .= " AND $wpdb->users.ID IN ($ids)";
		} elseif ( ! empty( $qv['exclude'] ) ) {
			$ids = implode( ',', wp_parse_id_list( $qv['exclude'] ) );
			$this->query_where .= " AND $wpdb->users.ID NOT IN ($ids)";
		}

		/**
		 * Fires after the WP_User_Query has been parsed, and before
		 * the query is executed.
		 *
		 * The passed WP_User_Query object contains SQL parts formed
		 * from parsing the given query.
		 *
		 * @since 3.1.0
		 *
		 * @param WP_User_Query $this The current WP_User_Query instance,
		 *                            passed by reference.
		 */
		do_action_ref_array( 'pre_user_query', array( &$this ) );
	}
}

/**
 * Basic User class
 */
class ET_User extends AE_Base{

	/**
	 * Insert a member
	 */
	static $instance = null;

	public function __construct(){

	}

	static public function init(){
		$instance = self::get_instance();
	}

	public function _insert($data){

		$args = $this->_filter_meta($data);

		$result = wp_insert_user( $args['data'] );

		if ($result != false && !is_wp_error( $result )){
			if ( isset($args['meta']) ) {
				foreach ($args['meta'] as $key => $value) {
					update_user_meta( $result, $key, $value );
				}
			}

			// people can modify here
			do_action('et_insert_user', $result);
		}

		return $result;
	}

	public function _update($data){
		try {
			if (empty($data['ID']))
				throw new Exception(__('Member not found', ET_DOMAIN), 404);

			// filter meta and default data
			$args = $this->_filter_meta($data);

			// update database
			$result = wp_update_user( $args['data'] );
			if ($result != false || !is_wp_error( $result ) ){
				if ( isset($args['meta']) ){
					foreach ((array)$args['meta'] as $key => $value) {
						update_user_meta( $result, $key, $value );
					}
				}

				// people can modify here
				do_action('et_update_user', $result);
			}

			return $result;
		} catch (Exception $e) {
			return new WP_Error($e->getCode(), $e->getMessage());
		}
	}

	protected function _delete($id, $reassign = 'novalue'){
		if ( wp_delete_user( $id, $reassign ) ){
			do_action( 'et_delete_user' );
		}
	}

	// add more meta data into default userdata
	protected function _convert($data){

		if(empty($data))
			return false;

		$result = clone $data->data;

		if (!empty($result)){
			foreach ($this->meta_data as $key) {
				$result->$key = get_user_meta( $data->ID, $key, true );
			}
		}

		return $result;
	}

	protected function _filter_meta($data){
		$return = array();
		foreach ($data as $key => $value) {
			if (in_array($key, $this->meta_data))
				$return['meta'][$key] = $value;
			else
				$return['data'][$key] = $value;
		}
		return $return;
	}

}

/**
 * Handle member data in forum engine
 */
class QA_Member extends ET_User{

	static $instance;

	public function __construct(){
		$this->meta_data = array(
			'et_avatar',
			'qa_following_questions',
			'user_facebook',
			'user_twitter',
			'user_gplus',
			'user_location',
			'register_status',
			'key_confirm',
			'et_question_count',
			'et_answer_count',
			'description',
			'qa_point',
			'show_email',
			'ban_expired',
			'is_ban'
		);
		global $wpdb;
		if ( $wpdb->blogid > 0 ){
			$this->meta_ban_expired = 'et_' . $wpdb->blogid . '_ban_expired';
			$this->meta_ban_note 	= 'et_' . $wpdb->blogid . '_ban_note';
			$this->meta_data = $this->meta_data + array(
				'et_' . $wpdb->blogid . '_ban_expired',
				'et_' . $wpdb->blogid . '_ban_note'
			);
		} else {
			$this->meta_ban_expired = 'et_ban_expired';
			$this->meta_ban_note 	= 'et_ban_note';
			$this->meta_data = $this->meta_data + array(
				'et_ban_expired',
				'et_ban_note'
			);
		}
	}

	static public function init(){
		$instance = self::get_instance();
	}

	/**
	 * get instance
	 */
	static public function get_instance(){
		if (self::$instance == null){
			self::$instance = new QA_Member();
		}

		return self::$instance;
	}
	/**
	 *
	 */
	protected function _get_ban_expired(){
		return  get_user_meta( $user_id, $this->meta_ban_expired, true );
	}

	/**
	 * Ban a user
	 * @param int $user_id
	 * @param string $time
	 */
	protected function _ban( $user_id, $time, $note = "" ){
		global $wpdb, $current_user;

		$user 			= get_user_by( 'id', $user_id );
		if ( user_can( $user, 'manage_options' ) || $current_user->ID == $user->ID ){
			return false;
		}

		update_user_meta( $user_id, $this->meta_ban_expired, date( 'Y-m-d h:i:s' , strtotime($time) ) );
		update_user_meta( $user_id, $this->meta_ban_note, $note );

		// send email
		$mail     = ae_get_option('ban_mail_template');
		$blogname = get_bloginfo( 'name');
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers  .= 'Content-type: text/html; charset=utf-8' . "\r\n";
		$headers  .= "From: " . $blogname . " \r\n";
		$subject  = sprintf( __('You have been banned from %s', ET_DOMAIN), $blogname);

		$params = array(
			'blogname' 		=> $blogname,
			'display_name' 	=> $user->display_name,
			'reason' 		=> $note,
			'ban_expired' 	=> date( get_option( 'date_format' ), strtotime($time) )
		);
		foreach ($params as $key => $value) {
			$mail = str_replace("[$key]", $value, $mail);
		}

		$sent = wp_mail( $user->user_email, $subject, $mail, $headers );

		return array(
			'expired' 	=> date( get_option( 'date_format' ), strtotime($time) ),
			'note' 		=> $note
		);
	}

	protected function _is_ban( $user_id ){
		$ban = get_user_meta( $user_id, $this->meta_ban_expired, true );
		return !empty($ban);
	}

	protected function _get_ban_info( $user_id ){
		$ban = array(
			'expired' 	=> get_user_meta( $user_id, $this->meta_ban_expired, true ),
			'note' 		=> get_user_meta( $user_id, $this->meta_ban_note, true ),
		);

		if ( empty($ban['expired']) )
			return false;
		else
			return $ban;
	}

	protected function _unban( $user_id ){
		delete_user_meta( $user_id, $this->meta_ban_expired );
		delete_user_meta( $user_id, $this->meta_ban_note );
	}

	/**
	 * Static method
	 */
	static public function ban($user_id, $time, $note = ''){
		$instance = self::get_instance();

		return $instance->_ban( $user_id, $time, $note );
	}

	static public function is_ban($user_id){
		$instance = self::get_instance( );
		return $instance->_is_ban( $user_id );
	}

	static public function get_ban_info($user_id){
		$instance = self::get_instance( );
		return $instance->_get_ban_info( $user_id );
	}

	static public function unban($user_id){
		$instance = self::get_instance( );
		return $instance->_unban( $user_id );
	}

	static public function insert($data){
		$instance = self::get_instance();
		return $instance->_insert($data);
	}

	static public function update($data){
		$instance = self::get_instance();
		return $instance->_update($data);
	}

	static public function delete($id){
		$instance = self::get_instance();
		return $instance->_delete($id);
	}

	static public function get($id){
		$user = get_userdata( $id );
		return self::convert($user);
	}

	static public function convert($user){

		if(empty($user))
			return false;

		$instance = self::get_instance();
		$result   = $instance->_convert($user);

		$result->id                = $result->ID;
		$result->et_avatar         = self::get_avatar($result->ID,64,array('class'=> 'avatar','alt' => $user->display_name));
		$result->et_question_count = et_count_user_posts($result->ID, 'question');
		$result->et_answer_count   = et_count_user_posts($result->ID, 'answer');

		$excludes = array('user_pass');
		foreach ($excludes as $value) {
			unset($result->$value);
		}

		if ( empty($result->et_question_count) ) $result->et_question_count = 0;
		if ( empty($result->et_answer_count) ) $result->et_answer_count = 0;
		// ban expired
		$result->ban_expired = date( get_option('date_format'), strtotime(get_user_meta( $result->ID, $instance->meta_ban_expired, true )) );
		$result->is_ban      = self::is_ban($result->ID);
		/**
		 * add cap to user data
		*/
		$result->cap =	qa_get_user_caps();

		// additional
		return $result;
	}

	static public function get_avatar_urls($id, $size = 64){
		$avatar = get_user_meta( $id, 'et_avatar', true );

		if ( empty($avatar) || empty($avatar['thumbnail']) || !isset($avatar['thumbnail'][0]) ){
			$link 	= get_avatar( $id, $size );
			preg_match( '/src=(\'|")(.+?)(\'|")/i', $link, $array );
			$sizes = get_intermediate_image_sizes();
			$avatar = array();
			foreach ($sizes as $size) {
				$avatar[$size] = array($array[2]);
			}
		} else {
			$avatar = $avatar['thumbnail'][0];
		}
		return $avatar;
	}

	static public function get_avatar($id, $size = 64 ,$params = array('class'=> 'avatar' , 'title' => '', 'alt' => '')){
		extract($params);
		$avatar = get_user_meta( $id, 'et_avatar', true );

		if ( !empty($avatar) && !empty($avatar['thumbnail']) && isset($avatar['thumbnail'][0])){
			$avatar = '<img src="'.$avatar['thumbnail'][0].'" class="'.$class.'" alt="'.$alt.'" />';
		} else {
			$link 	= get_avatar( $id, $size );
			preg_match( '/src=(\'|")(.+?)(\'|")/i', $link, $array );
			$sizes = get_intermediate_image_sizes();
			$avatar = array();
			foreach ($sizes as $size) {
				$avatar[$size] = $array[2];
			}
			$avatar = '<img src="'.$avatar['thumbnail'].'" class="'.$class.'" alt="'.$alt.'" />';
		}
		return $avatar;
	}

	static public function get_current_member(){
		$user = wp_get_current_user();
		if ( !$user->ID ) return $user;
		else {
			return QA_Member::convert($user);
		}
	}
}

function et_get_avatar($id, $size = 64,$params = array('class'=> 'avatar','alt' => '')){
	return QA_Member::get_avatar($id, $size, $params);
}

class ET_UserAjax extends AE_Base{

	public function __construct(){
		$this->add_ajax('et_user_sync', 'user_sync');
	}

	public function user_sync(){

		switch ($_POST['method']){
			case 'read':

				if( isset($_POST['content']['action']) && $_POST['content']['action'] == 'forgot' )
					$resp = $this->forgot();
				elseif( isset($_POST['content']['action']) && $_POST['content']['action'] == 'reset' )
					$resp = $this->reset_password();
				else
					$resp = $this->login();

				break;

			case 'create':
				$resp = $this->register();
				break;

			case 'update':
				$resp = $this->update();
				break;

			case 'logout':
				$resp = $this->logout();
				break;

			case 'remove':
				$resp = $this->remove();
				break;

			case 'inbox':
				$resp = $this->inbox();
				break;

			case 'ban':
				$resp = $this->ban_user();
				break;

			case 'unban':
				$resp = $this->unban_user();
				break;

			case 'forgot':
				$resp = $this->forgot();
				break;

			case 'change_logo':
				$resp = $this->change_logo();
				break;

			case 'get_members':
				$resp = $this->get_members();
				break;

			default:
				break;
		}

		wp_send_json( $resp );
	}
	/**
	  * remove() delete user function.
	  *
	  * @access public
	  * @since 1.0
	  * @param $_POST['content']
	  * @return json
	  */
	public function remove(){
		try{
			$id = $_POST['id'];
			$result = wp_delete_user( $id );
			wp_logout();
			if( !is_wp_error($result) ){
				$resp = array(
					'success' 		=> true,
					'msg' 			=> __('Your account has been deleted successfully.',ET_DOMAIN ),
					'redirect'  	=> home_url()
				);
			};

		} catch (Exception $e) {
			$resp = array(
				'success' 	=> false,
				'msg' 		=> $e->getMessage()
			);
		}
		return $resp;
	}
	/**
	  * update() user data function.
	  *
	  * @access public
	  * @since 1.0
	  * @param $_POST['content']
	  * @return json
	  */
	public function update(){
		try{
			global $current_user;
			$user_email	= $current_user->data->user_email;
			$args = $_POST['content'];

			if( isset($_POST['do_action'])){

				if( $_POST['do_action'] == "saveProfile" ){

					QA_Member::update(array(
							'ID'            => $_POST['ID'],
							'display_name'  => sanitize_text_field($args['display_name']),
							'user_location' => sanitize_text_field($args['user_location']),
							'user_facebook' => sanitize_text_field($args['user_facebook']),
							'user_twitter'  => sanitize_text_field($args['user_twitter']),
							'user_gplus'    => sanitize_text_field($args['user_gplus']),
							'description'   => sanitize_text_field($args['description']),
							'user_email'    => sanitize_text_field($args['user_email']),
							'show_email'    => isset($args['show_email']) ? $args['show_email'] : "off" ,
						));
					$user = QA_Member::convert(get_userdata( $_POST['ID'] ));

					$resp = array(
						'success' 		=> true,
						'msg' 			=> __('Your profile has been updated!',ET_DOMAIN ),
						'data'			=> $user,
						'redirect'  	=> get_author_posts_url($_POST['ID'])

					);

				} elseif ( $_POST['do_action'] == "changePassword" ) {

					if( !isset( $args['old_password'] ) || !isset( $args['new_password'] ) ){
						throw new Exception(__('Please enter all required information to reset your password.', ET_DOMAIN ), 400 );
					}
					if( $args['new_password'] !== $args['re_password'] ){
						throw new Exception(__('Confirmed password does not matched', ET_DOMAIN ), 400 );
					}

					// check old password is correct or not
					$pass_check = wp_check_password( $args['old_password'], $current_user->data->user_pass, $current_user->data->ID );

					if ( !$pass_check ) {
						throw new Exception(__('Old password is not correct.', ET_DOMAIN), 401);
					}

					if ( empty($args['new_password']) )
						throw new Exception(__('Your new password cannot be empty.', ET_DOMAIN), 400);

					// set new password for current user
					wp_set_password( $args['new_password'], $current_user->data->ID );

					// relogin the user automatically
					$user = et_login_by_email( $user_email, $args['new_password'] );

					if( !is_wp_error($user) ){
						$resp = array(
							'success' 		=> true,
							'msg' 			=> __('Your password was changed! Please login again!',ET_DOMAIN ),
							'redirect'  	=> get_author_posts_url($current_user->data->ID)
						);
					};
				}
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
	 * Perform login ajax request
	 * @param username (or email)
	 * @param password
	 */
	public function login(){
		$args          = $_POST['content'];
		$user_facetory = QA_Member::get_instance();
		// find user by username
		$userdata      = get_user_by( 'login', $args['username'] );

		// if no username found, find by email
		if ( $userdata == false ){
			$userdata 	= get_user_by( 'email', $args['username'] );
		}

		// user is still not found, return error
		if ( $userdata == false ){
			return array(
				'success' => false,
				'code'    => 401,
				'msg'     => __('Your login information was incorrect. Please try again.', ET_DOMAIN),
			);
		}
		// if user is banned, return error
		if (  $user_facetory->is_ban( $userdata->ID ) ){
			$ban_info = $user_facetory->get_ban_info($userdata->ID);
			return array(
				'success' 	=> false,
				'code' 		=> 401,
				'banned' 	=> true,
				'msg' 		=> sprintf(__('Your account has been banned. Reason "%s". Expired on "%s".', ET_DOMAIN), $ban_info['note'], $ban_info['expired'] )
			);
		}

		$remember = $args['remember'] == 1 ? true : false;
		// if nothing wrong, continue
		$user     = et_login($args['username'], $args['password'], $remember);

		if( is_wp_error($user) ) {
			// apply login by email here
			$user 	= et_login_by_email($args['username'], $args['password'], $remember);
		}

		// get new data of user
		if(!is_wp_error($user)) $userdata  	= QA_Member::convert($user);

		// generate new nonces
		$nonce 		= array(
			'reply_thread' => wp_create_nonce( 'insert_reply' ),
			'upload_img'   => wp_create_nonce( 'et_upload_images' ),
		);

		if ( !is_wp_error($user) ){
			$resp = array(
				'success'  => true,
				'code'     => 200,
				'msg'      => __('You have logged in successfully', ET_DOMAIN),
				'redirect' => get_post_type_archive_link( 'question' ),
				'data'     => array(
					'user' 		=> $userdata,
					'nonce' 	=> $nonce
				)
			);
		}
		else {
			$resp = array(
				'success' => false,
				'code'    => 401,
				'msg'     => __('Your login information was incorrect. Please try again.', ET_DOMAIN),
			);
		}
		return $resp;
	}

	public function logout(){
		wp_logout();
		$resp = array(
			'success' 	=> true,
			'msg' 		=> __('You have logged out', ET_DOMAIN),
		);
		return $resp;
	}
	public function change_logo(){
		$res	= array(
			'success'	=> false,
			'msg'		=> __('There is an error occurred', ET_DOMAIN ),
			'code'		=> 400,
		);

		// check fileID
		if(!isset($_POST['fileID']) || empty($_POST['fileID']) ){
			$res['msg']	= __('Missing image ID', ET_DOMAIN );
		}
		else {
			$fileID	= $_POST["fileID"];

			// check author
			if(!isset($_POST['author']) || empty($_POST['author']) || !is_numeric($_POST['author']) ){
				$res['msg']	= __('Missing user data', ET_DOMAIN );
			}
			else{
				$author	= $_POST['author'];

				// check ajax nonce
				if ( !check_ajax_referer( 'user_avatar_et_uploader', '_ajax_nonce', false ) ){
					$res['msg']	= __('Security error!', ET_DOMAIN );
				}
				elseif(isset($_FILES[$fileID])){

					// handle file upload
					$attach_id	= et_process_file_upload( $_FILES[$fileID], $author, 0, array(
							'jpg|jpeg|jpe'	=> 'image/jpeg',
							'gif'			=> 'image/gif',
							'png'			=> 'image/png',
							'bmp'			=> 'image/bmp',
							'tif|tiff'		=> 'image/tiff'
						) );

					if ( !is_wp_error($attach_id) ){

						// Update the author meta with this logo
						try {
							$user_avatar	= et_get_attachment_data($attach_id);
							/**
							 * get old logo and delete it
							 */
							$old_logo  = get_user_meta( $author, 'et_avatar', true );
							if(isset($old_logo['attach_id'])) {
								$old_logo_id = $old_logo['attach_id'];
								wp_delete_attachment( $old_logo_id, true);
							}
							/**
							 * update new user logo
							*/
							QA_Member::update(array(
									'ID' => $author,
									'et_avatar' => $user_avatar
								));

							$res	= array(
								'success'	=> true,
								'msg'		=> __('User logo has been uploaded successfully!', ET_DOMAIN ),
								'data'		=> $user_avatar
							);
						}
						catch (Exception $e) {
							$res['msg']	= __( 'Problem occurred while updating user field', ET_DOMAIN );
						}
					}
					else{
						$res['msg']	= $attach_id->get_error_message();
					}
				}
				else {
					$res['msg']	= __('Uploaded file not found', ET_DOMAIN);
				}
			}
		}
		return $res;
	}
	public function register(){
		$param = $_REQUEST['content'];
		$args = array(
			'user_email' 	=> $param['email'],
			'user_pass'  	=> $param['password'],
			'user_login' 	=> $param['username'],
			'display_name'  => isset($param['display_name']) ? $param['display_name'] : $param['username']
		);

		// validate here, later
		try {

			$role	=	'author';
			do_action ('je_before_user_register', $args);

			// apply register & log the user in
			$auto_sign  = ae_get_option( 'user_confirm' ) ? false : true;
			$user_id 	= et_register( $args , $role, $auto_sign );

			if ( is_wp_error($user_id) ){
				throw new Exception($user_id->get_error_message() , 401);
			}

			$data 		= get_userdata( $user_id );
			$userdata 	= QA_Member::convert($data);
			// generate new nonces
			$msg = ae_get_option( 'user_confirm' ) ? __('You have registered an account successfully but are not able to join the discussions yet. Please confirm your email address first.', ET_DOMAIN) : __('You are registered and logged in successfully.', ET_DOMAIN) ;
			$response = array(
				'success' 		=> true,
				'code' 			=> 200,
				'msg' 			=> $msg,
				'data' 			=> $userdata,
				'redirect'		=> apply_filters( 'qa_filter_redirect_link_after_register', home_url() )
			);

		} catch (Exception $e) {
			$response = array(
				'success' => false,
				'code' => $e->getCode(),
				'msg' => $e->getMessage()
			);
		}

		wp_send_json( $response );
	}

	public function forgot(){

		$errors = new WP_Error();

		$args = $_POST['content'];

		if ( empty( $args['user_login'] ) ) {
			$errors->add('empty_username', __('<strong>ERROR</strong>: Enter username or email address.', ET_DOMAIN));
		} else if ( strpos( $args['user_login'], '@' ) ) {
			$user_data = get_user_by( 'email', trim( $args['user_login'] ) );
			if ( empty( $user_data ) )
				$errors->add('invalid_email', __('<strong>ERROR</strong>: There is no user registered with that email address.', ET_DOMAIN));
		} else {
			$login = trim($args['user_login']);
			$user_data = get_user_by('login', $login);
		}

		// call the retrieve password request
		$result = et_retrieve_password($user_data, $errors);

		$user = QA_Member::convert($user_data);

		if ( is_wp_error($result) ){
			$response = array(
				'success' 	=> false,
				'msg' 		=> $result->get_error_message(),
				);
		}
		else {
			$response = array(
				'success' 	=> true,
				'msg' 		=> __('Please check your email inbox to reset password.', ET_DOMAIN),
				'data'		=> array('ID' => $user->ID, 'id' => $user->ID )
				);
		}
		return $response;
	}

	public function reset_password(){
		try {
			if ( empty($_REQUEST['content']['user_login']) )
				throw new Exception( __("This user is not found.", ET_DOMAIN) );
			if ( empty($_REQUEST['content']['user_key']) )
				throw new Exception( __("Invalid Key", ET_DOMAIN) );
			if ( empty($_REQUEST['content']['new_pass']) )
				throw new Exception( __("Please enter your new password", ET_DOMAIN) );

			// validate activation key
			$validate_result = et_check_password_reset_key($_REQUEST['content']['user_key'], $_REQUEST['content']['user_login']);
			if ( is_wp_error($validate_result) ){
				throw new Exception( $validate_result->get_error_message() );
			}

			// do reset password
			$user = get_user_by('login', $_REQUEST['content']['user_login']);
			$reset_result = et_reset_password($user, $_REQUEST['content']['new_pass']);

			if ( is_wp_error($reset_result) ){
				throw new Exception( $reset_result->get_error_message() );
			}
			else {
				$response = array(
					'success' 	=> true,
					'code' 		=> 200,
					'msg' 		=> __('Your password has been changed. Please log in again.', ET_DOMAIN),
					'data' 		=> $user,
					'redirect'	=> home_url()
				);
			}
		} catch (Exception $e) {
			$response = array(
				'success' 	=> false,
				'code' 		=> 400,
				'msg' 		=> $e->getMessage(),
				'redirect'	=> home_url()
			);
		}
		return $response;
	}

	public function confirm(){
		try {
			if (!current_user_can('manage_options'))
				throw new Exception( __("You don't have permission", ET_DOMAIN) );

			$data = $_POST['content'];

			if (!$data['ID'])
				throw new Exception( __("Required user ID.", ET_DOMAIN) );

			QA_Member::update(array(
				'ID' => $data['ID'],
				'register_status' => ''
			));

			$resp = array(
				'success' 	=>  true,
				'msg' 		=> __('User has been confirmed', ET_DOMAIN),
			);

		} catch (Exception $e) {
			$resp = array(
				'success' 	=> false,
				'msg'		=> $e->getMessage()
			);
		}
		return $resp;
	}

	/**
	 * Query member
	 *
	 */
	public function get_members(){
		try {
			$query_vars = wp_parse_args( $_POST['content']['query_vars'], array('search_columns' => array('user_nicename', 'user_login')));

			if ( !empty($query_vars['search']) )
				$query_vars['search'] = "*" . $query_vars['search'] . "*";

			$query = new WP_User_Query($query_vars);

			if ( !empty($query->results) ){
				$result = array();

				foreach ($query->results as $user) {
					$result[] = et_make_member_data($user);
				}

			} else {
				throw new Exception(__('No result found', ET_DOMAIN));
			}

			$resp = array(
				'success' 	=> true,
				'msg' 		=> '',
				'data' 		=> array(
					'users' => $result,
					'total' => (int)$query->total_users,
					'offset' => (int)$query_vars['offset'],
					'number' => (int)$query_vars['number']
				)
			);

		} catch (Exception $e) {
			$resp = array(
				'success' 	=> false,
				'msg' 		=> $e->getMessage()
			);
		}
		return $resp;
	}
	/**
	 * Inbox Message
	 *
	 */
	public function inbox(){
		$args 		= $_POST['content'];

		global $current_user;

		try {
			if ( !$current_user->ID ){
				throw new Exception(__('Login required', ET_DOMAIN));
			}

			$author 	= get_user_by( 'id', $args["user_id"] );
			$to_email 	= $author->user_email;
			$from_email = $current_user->user_email;

			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
			$headers .= "From: $current_user->display_name <$from_email>" . "\r\n";
			$headers .= 'Reply-To: '.$current_user->display_name.' <'.$from_email.'>' . "\r\n";

			$subject     = apply_filters('qa_inbox_subject',__('New Private Message From ', ET_DOMAIN).get_bloginfo('blogname' ));
			$message     = ae_get_option('inbox_mail_template');
			$new_message = stripslashes(str_replace("\n", "<br>", $args['message'])) ;
			$blogname    = get_bloginfo('blogname');
			$sender_link = get_author_posts_url($current_user->ID);
			$message     = str_replace('[display_name]', $author->display_name, $message);
			$message     = str_replace('[sender]', '<a href="'.$sender_link.'"'.$current_user->display_name.'</a>', $message);
			$message     = str_replace('[message]', $new_message, $message);
			$message     = str_replace('[blogname]', $blogname, $message);

			$send     = wp_mail($to_email, $subject , $message, $headers);

			if(!$send){
				throw new Exception(__('Email sending failed.', ET_DOMAIN));
			}

			$resp = array(
				'success' 	=> true,
				'msg' 		=> __('Message was sent successfully.',ET_DOMAIN),
				'send'		=> $current_user->display_name.'--'.$from_email.'--'.$to_email .'-'. $subject .'-'. $message.'-'.$headers
			);
		} catch (Exception $e) {
			$resp = array(
				'success' 	=> false,
				'msg' 		=> $e->getMessage()
			);
		}

		return $resp;
	}
	/**
	 * handle ajax ban user
	 * @return mixed
	 */
	public function ban_user(){
		try {
			if (!current_user_can('manage_options'))
				throw new Exception( __("You don't have permission", ET_DOMAIN) );

			wp_parse_str( $_POST['content'], $data );

			$user      = QA_Member::get_instance();
			$result    = $user->ban( $data['id'], $data['expired'], $data['reason'] );
			$user_data = et_make_member_data( get_user_by( 'id', $data['id'] ) );
			//delete private data
			unset($user_data['user_email']);
			unset($user_data['user_pass']);
			$resp = array(
				'success' 	=>  true,
				'msg' 		=> __('User has been banned', ET_DOMAIN),
				'data' 		=> array(
					'ban'  => $result,
					'user' => $user_data
				)
			);

		} catch (Exception $e) {
			$resp = array(
				'success' 	=> false,
				'msg'		=> $e->getMessage()
			);
		}
		return $resp;
	}

	/**
	 * handle ajax unban user
	 * @return mixed
	 */
	public function unban_user(){
		try {
			if (!current_user_can('manage_options'))
				throw new Exception( __("You don't have permission", ET_DOMAIN) );

			//wp_parse_str( $_POST['content'], $data );
			$data = $_POST['content'];

			$user      = QA_Member::get_instance();
			$result    = $user->unban( $data['ID'] );
			$user_data = et_make_member_data( get_user_by( 'id', $data['ID'] ) );
			//delete private data
			unset($user_data['user_email']);
			unset($user_data['user_pass']);
			$resp = array(
				'success' 	=>  true,
				'msg' 		=> __('User has been unbanned', ET_DOMAIN),
				'data' 		=> array(
					'user' => $user_data
				)
			);

		} catch (Exception $e) {
			$resp = array(
				'success' 	=> false,
				'msg'		=> $e->getMessage()
			);
		}
		return $resp;
	}
}

/**
 *
 */
function et_make_member_data($user){
	global $wpdb;
	$info = (array)$user->data + array(
		'id' 				=> $user->ID,
		'question_count' 	=> get_user_meta($user->ID, 'et_question_count',true) ? get_user_meta($user->ID, 'et_question_count',true) : 0,
		'answer_count' 		=> get_user_meta($user->ID, 'et_answer_count', true) ? get_user_meta($user->ID, 'et_answer_count',true) : 0,
		'user_location' 	=> get_user_meta($user->ID, 'user_location', true) ? get_user_meta($user->ID, 'user_location', true) : 'NA',
		'date_text' 		=> sprintf( __('Join on %s', ET_DOMAIN), date('jS M, Y', strtotime($user->user_registered)) ),
		'role' 				=> $user->roles[0],
		'avatar' 			=> et_get_avatar($user->ID),
		'register_status' 	=> get_user_meta($user->ID, 'register_status', true) == "unconfirm" ? "unconfirm" : '',
	);

	$member_object = QA_Member::get_instance();
	$ban           = get_user_meta( $user->ID, $member_object->meta_ban_expired , true );

	if ( !empty( $ban ) ){
		$info['banned'] 		= true;
		$info['ban_expired'] 	= date( get_option('date_format'), strtotime( $ban ) );
	} else {
		$info = $info + array(
			'banned' 		=> false,
			'ban_expired' 	=> ''
		);
	}
	return $info;
}


/**
 * Additional functions
 */
function et_count_user_posts($user_id,$post_type = "question"){
	global $wpdb;
	$sql = "SELECT COUNT(post.ID)
				FROM {$wpdb->posts} as post
				WHERE post.post_type = '".$post_type."'
					AND ( post.post_status = 'publish' OR post.post_status = 'pending' )
					AND post.post_author = ".$user_id;
	return $wpdb->get_var( $sql );
}
function et_add_user_group($role_id, $display_name, $permission){
	add_role( $role_id, $display_name, $permission );
}

function et_remove_user_group($role){
	// check if role has
	$users = get_users(array(
		'roles' => $role
	));

	// if at least there is a user in role, return error
	if (!empty($users)) return false;

	// if
	remove_role( $role );

	return true;
}

function get_user_role( $user_id ){

  $user_data = get_userdata( $user_id );

  if(!empty( $user_data->roles ))
      return $user_data->roles[0];

  return false;

}

/**
 * Log a user in in via user information.
 * @param $username username to log in
 * @param $password password
 * @param $remember remember log in for later access
 * @param $secure_cookie Whether to use secure cookie.
 * @return WP_User on success or WP_Error on failure
 *
 * @since 1.0
 */
function et_login( $username, $password, $remember = false, $secure_cookie = false ){
	global $current_user;

	// check users if he is member of this blog
	$user = get_user_by('login', $username);
	if ( !$user || !is_user_member_of_blog( $user->ID ) )
		return new WP_Error('login_failed', "Login failed");

	$creds['user_login']    = $username;
	$creds['user_password'] = $password;
	$creds['remember']      = true;

	//$result = &wp_signon( $creds, $secure_cookie );
	$result = wp_signon( $creds, $secure_cookie );

	if ( $result instanceof WP_User )
		$current_user = $result;

	return $result;
}

/**
 * Perform log user in via email
 * @param $email user's email
 * @param $password password for log-in
 * @param @remember allow auto log for next time
 * @param @secure_cookie ...
 *
 * @since 1.0
 */
function et_login_by_email( $email, $password, $remember = false, $secure_cookie = false ){
	$user = get_user_by('email', $email);
	if ( $user != false )
		return et_login($user->user_login, $password, $remember, $secure_cookie);
	else
		return new WP_Error(403, __('This email address was not found.', ET_DOMAIN));
}

/**
 * Register user by given user data
 * @param array $user information of new user:
 * 	- username : new user name
 * 	- password : new password
 * 	- email : email
 * @since 1.0
 *
 */
function et_register( $userdata, $role = 'subscriber', $auto_login = false ){
	extract($userdata);

	if (!preg_match("/^[a-zA-Z0-9_]+$/", $userdata['user_login'])){
		return new WP_Error('username_invalid', __('Username is invalid', ET_DOMAIN));
	}

	$userdata['role']	= $role;
	$result = wp_insert_user( $userdata );

	// if creating user false
	if ( $result instanceof WP_Error ){
		return $result;
	}

	do_action('et_after_register', $result , $role );

	// auto login
	if ( $auto_login ) {
		et_login($user_login , $user_pass, true);
	}

	// then return user id
	return $result;
}
/**
 * Handles resetting the user's password.
 *
 * @param object $user The user
 * @param string $new_pass New password for the user in plaintext
 */
function et_reset_password($user, $new_pass) {
	do_action('et_password_reset', $user, $new_pass);

	wp_set_password($new_pass, $user->ID);

	wp_password_change_notification($user);
}
/**
 * Retrieves a user row based on password reset key and login
 *
 * @uses $wpdb WordPress Database object
 *
 * @param string $key Hash to validate sending user's password
 * @param string $login The user login
 * @return object|WP_Error User's database row on success, error object for invalid keys
 */
function et_check_password_reset_key($key, $login) {
	global $wpdb;

	$key = preg_replace('/[^a-z0-9]/i', '', $key);

	if ( empty( $key ) || !is_string( $key ) )
		return new WP_Error('invalid_key', __('Invalid key', ET_DOMAIN));

	if ( empty($login) || !is_string($login) )
		return new WP_Error('invalid_key', __('Invalid key', ET_DOMAIN));

	$user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->users WHERE user_activation_key = %s AND user_login = %s", $key, $login));


	if ( empty( $user ) )
		return new WP_Error('invalid_key', __('Invalid key', ET_DOMAIN));

	return $user;
}
/**
 * Handles sending password retrieval email to user.
 *
 * @uses $wpdb WordPress Database object
 *
 * @return bool|WP_Error True: when finish. WP_Error on error
 */
function et_retrieve_password($user_data, $errors) {
	global $wpdb, $current_site;

	do_action('lostpassword_post');

	if ( $errors->get_error_code() )
		return $errors;

	if ( !$user_data ) {
		$errors->add('invalidcombo', __('<strong>ERROR</strong>: Invalid username or email address.', ET_DOMAIN));
		return $errors;
	}

	// redefining user_login ensures we return the right case in the email
	$user_login = $user_data->user_login;
	$user_email = $user_data->user_email;

	do_action('retreive_password', $user_login);  // Misspelled and deprecated
	do_action('retrieve_password', $user_login);

	$allow = apply_filters('allow_password_reset', true, $user_data->ID);

	if ( ! $allow )
		return new WP_Error('no_password_reset', __('Password reset is not allowed for this user', ET_DOMAIN));
	else if ( is_wp_error($allow) )
		return $allow;

	$key = $wpdb->get_var($wpdb->prepare("SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login));
	if ( empty($key) ) {
		// Generate something random for a key...
		$key = wp_generate_password(20, false);
		do_action('retrieve_password_key', $user_login, $key);
		// Now insert the new md5 key into the db
		$wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_login));
	}
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
	$headers .= "From: ".get_option('blogname')." < ".get_option('admin_email') ."> \r\n";

	$message = __('There is a request to reset the password for the following account:', ET_DOMAIN) . "\r\n\r\n";
	$message .= network_home_url( '/' ) . "\r\n\r\n";
	$message .= sprintf(__('Username: %s', ET_DOMAIN), $user_login) . "\r\n\r\n";
	$message .= __('If this was a mistake, just ignore this email and nothing will happen.',ET_DOMAIN) . "\r\n\r\n";
	$message .= __('To reset your password, visit the following link:', ET_DOMAIN) . "\r\n\r\n";
	//$message .= '<' . network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login') . ">\r\n";
	$site = apply_filters('et_reset_password_link',  network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login'), $key, $user_login );
	$message .= '<' . $site . ">\r\n";

	if ( is_multisite() )
		$blogname = $GLOBALS['current_site']->site_name;
	else
		// The blogname option is escaped with esc_html on the way into the database in sanitize_option
		// we want to reverse this for the plain text arena of emails.
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	$title = sprintf( __('[%s] Password Reset', ET_DOMAIN), $blogname );

	$title = apply_filters('et_retrieve_password_title', $title);
	$message = apply_filters('et_retrieve_password_message', $message, $key, $user_data);

	if ( $message && !wp_mail($user_email, $title, $message , $headers) )
		wp_die( __('The email could not be sent.', ET_DOMAIN) . "<br />\n" . __('Possible reason: your host may have disabled the mail() function...', ET_DOMAIN) );

	return true;
}

/**
 * Cron job for automatically unban user
 * @author EngineTheme
 */
function et_setup_schedule_ban(){
	if ( ! wp_next_scheduled( 'et_cron_unban_users' ) ){
		wp_schedule_event( time(), 'hourly', 'et_cron_unban_users');
	}
}
add_action('wp', 'et_setup_schedule_ban');

/**
 * Search expired banned users and unban them
 * @return void
 */
function et_unban_expired_users(){
	global $wpdb;

	$member = QA_Member::get_instance();

	$user_query = new WP_User_Query(array(
		'meta_key' 		=> $member->meta_ban_expired,
		'meta_value' 	=> date('Y-m-d h:i:s'),
		'meta_compare' 	=> '<'
	));

	$users = $user_query->get_results();

	foreach ($users as $user) {
		QA_Member::unban( $user->ID );
	}
}
add_action('et_cron_unban_users', 'et_unban_expired_users');

/**
 * Get expired period
 * @return mixed
 */
function et_get_ban_expired_period(){
	$expired = array(
		array(
			'label' 	=> __('1 Day', ET_DOMAIN),
			'value' 	=> '+1 day'
		),
		array(
			'label' 	=> __('3 Days', ET_DOMAIN),
			'value' 	=> '+3 days'
		),
		array(
			'label' 	=> __('7 Days', ET_DOMAIN),
			'value' 	=> '+7 days'
		),
		array(
			'label' 	=> __('15 Days', ET_DOMAIN),
			'value' 	=> '+15 days'
		),
		array(
			'label' 	=> __('1 Month', ET_DOMAIN),
			'value' 	=> '+1 month'
		),
		array(
			'label' 	=> __('3 Months', ET_DOMAIN),
			'value' 	=> '+3 months'
		),
		array(
			'label' 	=> __('6 Months', ET_DOMAIN),
			'value' 	=> '+6 months'
		),
		array(
			'label' 	=> __('1 Year', ET_DOMAIN),
			'value' 	=> '+1 year'
		),
		array(
			'label' 	=> __('Forever', ET_DOMAIN),
			'value' 	=> '+999 year'
		)
	);
	$expired = apply_filters( 'et_ban_expired_period', $expired );
	return $expired;
}

?>