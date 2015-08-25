<?php

/**
 * Contains some quick method for post type
 */
class ET_PostType extends AE_Base {

	static $instance;
	public $name;
	public $args;
	public $taxonomy_args;
	public $meta_data;

	function __construct($name, $args, $taxonomy_args, $meta_data){
		$this->name 			= $name;
		$this->args 			= $args;
		$this->taxonomy_args 	= $taxonomy_args;
		$this->meta_data 		= $meta_data;
	}

	/**
	 * Init post type by registering post type and taxonomy
	 */
	static public function _init($name, $args, $taxonomy_args){
		// register post type
		register_post_type(
			$name,
			$args
		);
		// register taxonomies
		if (!empty($taxonomy_args)){
			foreach ($taxonomy_args as $tax_name => $args) {
				register_taxonomy( $tax_name, array($name), $args );
			}
		}
	}

	protected function trip_meta($data){
        // trip meta datas
        $args = $data;
		$meta = array();
		foreach ($args as $key => $value) {
			if ( in_array($key, $this->meta_data) ){
				$meta[$key] = $value;
				unset($args[$key]);
			}
		}

		return array(
			'args' 	=> $args,
			'meta' 	=> $meta
		);
	}

	/**
	 * Insert post type data into database
	 */
	protected function _insert($args){
		global $current_user;

		$args = wp_parse_args( $args, array(
            'post_type'     => $this->name,
            'post_status'   => 'pending',
        ) );

        if(isset($args['author']) && !empty($args['author'])) $args['post_author'] = $args['author'];

		if ( empty($args['post_author']) ) return new WP_Error('missing_author', __('Missing Author', ET_DOMAIN));

        // filter args
        $args = apply_filters( 'et_pre_insert_' . $this->name, $args );

        $data = $this->trip_meta($args);

        $result = wp_insert_post( $data['args'], true );
        /**
         * update custom field and tax
        */
        $this->update_custom_field ($result, $data, $args );

        // do action here
        do_action('et_insert_' . $this->name, $result);
        return $result;
	}

	/**
	 * Update post type data in database
	 */
	protected function _update($args){
		global $current_user;

		$args = wp_parse_args( $args );

		// filter args
        $args = apply_filters( 'et_pre_update_' . $this->name, $args );

		// if missing ID, return errors
        if (empty($args['ID'])) return new WP_Error('et_missing_ID', __('Thread not found!', ET_DOMAIN));

        // separate default data and meta data
        $data = $this->trip_meta($args);

    	// insert into database
        $result = wp_update_post( $data['args'], true );
        /**
         * update custom field and tax
        */
		$this->update_custom_field ($result, $data, $args );
        // make an action so develop can modify it
        do_action('et_update_' . $this->name, $result);
        return $result;
	}

	/**
	 * update post meta and taxonomy
	 * @param object $result post
	 * @param array $data post data
	 * @param array $args
	 * @author Dakachi
	 * @since version 1.0
	*/
	public function update_custom_field( $result, $data ,  $args ) {

		if ( !($result instanceof WP_Error) ){
			foreach ($this->taxonomy_args as $tax_name => $tax_args) {
				//if ( isset($args['tax_input'][$tax_name]) && term_exists($args['tax_input'][$tax_name], $tax_name) ){
				if ( isset($args['tax_input'][$tax_name]) ){
					$terms = wp_set_object_terms( $result, $args['tax_input'][$tax_name], $tax_name );
				}
			}
		}

        if ($result != false || !is_wp_error( $result )){
        	foreach ($data['meta'] as $key => $value) {
        		update_post_meta( $result, $key, $value );
        	}
        }
	}

	protected function _delete($ID, $force_delete = false){
		if ( $force_delete ){
			$result = wp_delete_post( $ID, true );
		} else {
			$result = wp_trash_post( $ID );
		}
		if ( $result )
			do_action('et_delete_' . $this->name, $ID);

		return $result;
	}

	protected function _update_field($id, $field_name, $value){
		update_post_meta( $id, $field_name, $value );
	}

	protected function _get_field($id, $field_name){
		return get_post_meta( $id, $field_name, true );
	}

	/**
	 * Get post type data by ID
	 */
	public function _get($id, $raw = false){
		$post = get_post($id);
		if ( $raw )
			return $raw;
		else
			return $this->_convert($post);
	}

	public function _convert($post, $taxonomy = true, $meta = true){
		$result = (array)$post;
		// echo '<pre>';
		// print_r($result);
		// echo '</pre>';
		// generate taxonomy
		if ( $taxonomy ){
			foreach ($this->taxonomy_args as $name => $args) {
				$result[$name]	 = wp_get_object_terms( $result['ID'], $name );
			}
		}

		// generate meta data
		if ( $meta ){
			foreach ($this->meta_data as $key) {
				$result[$key] 	= get_post_meta( $result['ID'], $key, true );
			}
		}
		$result['id']	=	$result['ID'];
		return (object)$result;
	}
	public static function vote($id, $type){
		global $current_user;

		$type = ($type == 'vote_up') ? 'vote_up' : 'vote_down';
		$vote_up_authors 	= (array) get_post_meta( $id, 'et_vote_up_authors', true);
		$vote_down_authors 	= (array) get_post_meta( $id, 'et_vote_down_authors', true);
		$vote_up 			= 0;
		$vote_down 			= 0;

		$comment_up = get_comments( array(
                'post_id'       => $id,
                'parent'        => 0,
                'status'        => 'approve',
                'post_status'   => 'publish',
                'order'         => 'ASC',
                'type'  		=> 'vote_up'
			) );
		$comment_down = get_comments( array(
                'post_id'       => $id,
                'parent'        => 0,
                'status'        => 'approve',
                'post_status'   => 'publish',
                'order'         => 'ASC',
                'type'  		=> 'vote_down'
			) );

		if ( in_array( $current_user->ID , $vote_up_authors ) ){

			$pos = array_search( $current_user->ID , $vote_up_authors );
			unset($vote_up_authors[$pos]);

			if ( $type == 'vote_down'){
				$vote_down_authors[] = $current_user->ID;
				array_unique($vote_down_authors);
			}
			if(!empty($comment_up)){
				$user_cmt = get_comments( array(
				                'post_id'       => $id,
				                'parent'        => 0,
				                'status'        => 'approve',
				                'post_status'   => 'publish',
				                'order'         => 'ASC',
				                'type'  		=> 'vote_up',
				                'user_id'		=> $current_user->ID
							) );
				wp_delete_comment( $user_cmt[0]->comment_ID, true );
			}
			else
				wp_insert_comment(array(
						'comment_post_ID' => $id,
						'comment_content' => $type,
						'comment_type' 	  => 'vote_up',
						'user_id'		  => $current_user->ID
					));

		} else if ( in_array( $current_user->ID , $vote_down_authors ) ){

			$pos = array_search( $current_user->ID , $vote_down_authors );
			unset($vote_down_authors[$pos]);

			if ( $type == 'vote_up'){
				$vote_up_authors[] = $current_user->ID;
				array_unique($vote_up_authors);
			}
			if(!empty($comment_down)){
				$user_cmt = get_comments( array(
				                'post_id'       => $id,
				                'parent'        => 0,
				                'status'        => 'approve',
				                'post_status'   => 'publish',
				                'order'         => 'ASC',
				                'type'  		=> 'vote_down',
				                'user_id'		=> $current_user->ID
							) );
				wp_delete_comment( $user_cmt[0]->comment_ID, true );
			}
			else
				wp_insert_comment(array(
						'comment_post_ID' => $id,
						'comment_content' => $type,
						'comment_type' 	  => 'vote_down',
						'user_id'		  => $current_user->ID
					));

		} else {
			/*================ INSERT COMMENT VOTE ================ */
			wp_insert_comment(array(
					'comment_post_ID' => $id,
					'comment_content' => $type,
					'comment_type' 	  => $type,
					'user_id'		  => $current_user->ID
				));
			/*================ INSERT COMMENT VOTE ================ */
			if ( $type == 'vote_up' ){
				$vote_up_authors[] 	= $current_user->ID;
			} else {
				$vote_down_authors[] 	= $current_user->ID;
			}
		}

		// remove empty item
		foreach ($vote_up_authors as $key => $value) {
			if ( $value === '' ){
				unset($vote_up_authors[$key]);
			}
		}

		// remove empty item
		foreach ($vote_down_authors as $key => $value) {
			if ( $value === '' ){
				unset($vote_down_authors[$key]);
			}
		}

		$comment_up = get_comments( array(
                'post_id'       => $id,
                'parent'        => 0,
                'status'        => 'approve',
                'post_status'   => 'publish',
                'order'         => 'ASC',
                'type'  		=> 'vote_up'
			) );
		$comment_down = get_comments( array(
                'post_id'       => $id,
                'parent'        => 0,
                'status'        => 'approve',
                'post_status'   => 'publish',
                'order'         => 'ASC',
                'type'  		=> 'vote_down'
			) );

		$vote_up 		= count($comment_up);
		$vote_down 		= count($comment_down);

		// save authors
		update_post_meta( $id, 'et_vote_up_authors', $vote_up_authors );
		update_post_meta( $id, 'et_vote_down_authors', $vote_down_authors );

		// save vote count
		update_post_meta( $id, 'et_vote_count' , $vote_up - $vote_down );
		//var_dump($vote_up .'-'. $vote_down);
	}
}

/**
 * Class QA_Questions
 */
class QA_Questions extends ET_PostType {
	CONST POST_TYPE = 'question';

	static $instance = null;

	public function __construct(){
		$this->name = self::POST_TYPE;
		$this->args = array(
			'labels' => array(
				'name'               => __('Questions', ET_DOMAIN ),
				'singular_name'      => __('Question', ET_DOMAIN ),
				'add_new'            => __('Add New', ET_DOMAIN ),
				'add_new_item'       => __('Add New Question', ET_DOMAIN ),
				'edit_item'          => __('Edit Question', ET_DOMAIN ),
				'new_item'           => __('New Question', ET_DOMAIN ),
				'all_items'          => __('All Questions', ET_DOMAIN ),
				'view_item'          => __('View Question', ET_DOMAIN ),
				'search_items'       => __('Search Questions', ET_DOMAIN ),
				'not_found'          => __('No questions found', ET_DOMAIN ),
				'not_found_in_trash' => __('No questions found in Trash', ET_DOMAIN ),
				'parent_item_colon'  => '',
				'menu_name'          => __('Questions', ET_DOMAIN )
			),
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => ae_get_option('question_slug', 'question')),
			'capability_type'    => 'post',
			'has_archive'        => 'questions',
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array(
				'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'custom-fields',  'revisions'
			)
		);
		$this->taxonomies =  array(
			'question_category' => array(
				'hierarchical'      => true,
				'labels'            => array(
					'name'              => __( 'Question Categories', ET_DOMAIN ),
					'singular_name'     => __( 'Category', ET_DOMAIN ),
					'search_items'      => __( 'Search Categories', ET_DOMAIN ),
					'all_items'         => __( 'All Categories', ET_DOMAIN ),
					'parent_item'       => __( 'Parent Category', ET_DOMAIN ),
					'parent_item_colon' => __( 'Parent Category:', ET_DOMAIN ),
					'edit_item'         => __( 'Edit Category' , ET_DOMAIN),
					'update_item'       => __( 'Update Category', ET_DOMAIN ),
					'add_new_item'      => __( 'Add New Category' , ET_DOMAIN),
					'new_item_name'     => __( 'New Category Name', ET_DOMAIN ),
					'menu_name'         => __( 'Category' , ET_DOMAIN),
				),
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite' 			=> array( 'slug' => ae_get_option('category_slug', 'question-category')),
			),
			'qa_tag'  => array(
				'hierarchical'          => false,
				'labels'                => array(
					'name'                       => _x( 'Tags', 'taxonomy general name' ),
					'singular_name'              => _x( 'Tag', 'taxonomy singular name' ),
					'search_items'               => __( 'Search Tags',ET_DOMAIN ),
					'popular_items'              => __( 'Popular Tags',ET_DOMAIN ),
					'all_items'                  => __( 'All Tags',ET_DOMAIN ),
					'parent_item'                => null,
					'parent_item_colon'          => null,
					'edit_item'                  => __( 'Edit Tag',ET_DOMAIN ),
					'update_item'                => __( 'Update Tag',ET_DOMAIN ),
					'add_new_item'               => __( 'Add New Tag',ET_DOMAIN ),
					'new_item_name'              => __( 'New Tag Name',ET_DOMAIN ),
					'separate_items_with_commas' => __( 'Separate tags with commas',ET_DOMAIN ),
					'add_or_remove_items'        => __( 'Add or remove tags',ET_DOMAIN ),
					'choose_from_most_used'      => __( 'Choose from the most used tags',ET_DOMAIN ),
					'not_found'                  => __( 'No tags found.',ET_DOMAIN ),
					'menu_name'                  => __( 'Tags',ET_DOMAIN ),
				),
				'show_ui'               => true,
				'show_admin_column'     => true,
				'update_count_callback' => '_update_post_term_count',
				'query_var'             => true,
				'rewrite'               => array( 'slug' => ae_get_option('tag_slug', 'qa-tag') ),
			)
		);
		$this->meta_data = apply_filters( 'question_meta_fields', array(
			'et_vote_count',
			'et_view_count',
			'et_answers_count',
			'et_users_follow',
			'et_answer_authors',
			'et_last_author',
			'et_vote_up_authors',
			'et_vote_down_authors',
			'et_best_answer'
		));
		parent::__construct( self::POST_TYPE , $this->args, $this->taxonomies, $this->meta_data );
	}

	/**
	 *
	 */
	static public function init(){
		$instance = self::get_instance();

		// register post type
		ET_PostType::_init( self::POST_TYPE , $instance->args, $instance->taxonomies);
	}

	static public function get_instance(){
		if ( self::$instance == null){
			self::$instance = new QA_Questions();
		}
		return self::$instance;
	}

	public static function insert($data){
		// required login
		global $user_ID;
		if ( !is_user_logged_in() )
			return new WP_Error('user_logged_required', __('Required Log In', ET_DOMAIN));
		$data['post_author'] = $user_ID;

		$instance = self::get_instance();
		$return = $instance->_insert($data);

		return $return;
	}
	static public function toggle_follow($question_id, $user_id){

		$users_follow_arr = explode(',',get_post_meta($question_id,'et_users_follow',true));
		$follow_questions = (array) get_user_meta( $user_id, 'qa_following_questions', true );

		//check question_id is in meta user follow
		if(!in_array($question_id, $follow_questions)){
			array_push($follow_questions, $question_id);
		} else {
			foreach ($follow_questions as $key => $value) {
				if ( $question_id == $value ){
					unset($follow_questions[$key]);
					break;
				}
			}
		}

		$follow_questions = array_unique(array_filter($follow_questions));
		update_user_meta( $user_id, 'qa_following_questions', $follow_questions );

		//check user_id is in meta follow question
		if(!in_array($user_id, $users_follow_arr)){
			array_push($users_follow_arr, $user_id);
		} else {
			foreach ($users_follow_arr as $key => $value) {
				if ( $user_id == $value ){
					unset($users_follow_arr[$key]);
					break;
				}
			}
		}

		$users_follow_arr = array_unique(array_filter($users_follow_arr));
		$users_follow     = implode(',', $users_follow_arr);

		QA_Questions::update_field($question_id, 'et_users_follow', $users_follow);

		return $users_follow_arr;
	}
	/**
	 * update question
	*/
	public static function update($data){

		global $current_user;

		if( isset($data['post_author']) && $current_user->ID != $data['post_author'] && !qa_user_can('edit_question') ) { // check user cap with edit question
			/**
			 * get site privileges
			*/
			$privileges	=	qa_get_privileges();
			return new WP_Error('cannot_edit', sprintf( __("You must have %d points to edit question.", ET_DOMAIN), $privileges->edit_question) );

		}

		// update question category
		if ( isset($data['question_category']) && isset($data['qa_tag']) ){

			$data['tax_input'] = array(
				'question_category' => $data['question_category'],
				'qa_tag'			=> $data['qa_tag']
			);
		}

		$instance = self::get_instance();
		$return = $instance->_update($data);

		return $return;
	}

	/**
	 * Delete a question + answer of this question
	 */
	public static function delete($id, $force_delete = false){
		$instance 	= self::get_instance();
		$post 		= get_post($id);
		$question 	= QA_Questions::convert($post);
		$answers 	= get_posts( array(
				'post_type'   => 'answer',
				'post_parent' => $question->ID
			) );

		if(is_array($answers) && count($answers) > 0){
			foreach ($answers as $answer) {
				$deleted = wp_trash_post( $answer->ID, $force_delete );
				if($deleted){
					//update answer count
					$count = et_count_user_posts($answer->post_author, 'answer');
					update_user_meta( $answer->post_author, 'et_answer_count', $count );
				}
			}
		}

		$success = $instance->_delete($id, $force_delete);

		if($success){
			//update question count
			$count = et_count_user_posts($question->post_author, 'question');
			update_user_meta( $question->post_author, 'et_question_count', $count );
		}

		return $success;
	}

	public static function get($id){
		return	self::get_instance()->_get($id);
	}

	public static function convert($post){
		global $current_user;
		$result = self::get_instance()->_convert($post);

		$result->qa_tag 				= wp_get_object_terms( $post->ID, 'qa_tag' );

		$result->et_vote_up_authors 	= is_array($result->et_vote_up_authors) ? $result->et_vote_up_authors : array();
		$result->et_vote_down_authors 	= is_array($result->et_vote_down_authors) ? $result->et_vote_down_authors : array();
		$result->voted_down 			= in_array($current_user->ID, (array)$result->et_vote_down_authors);
		$result->voted_up 				= in_array($current_user->ID, (array)$result->et_vote_up_authors);
		$result->et_vote_count 			= get_post_meta( $post->ID, 'et_vote_count', true ) ? get_post_meta( $post->ID, 'et_vote_count', true ) : 0;
		$result->user_badge 		= qa_user_badge( $post->post_author, false );
		$result->et_answers_count 	= et_count_answer($post->ID);
		$result->et_view_count 		= $result->et_view_count ? $result->et_view_count : 0;
		$result->et_answer_authors 	= is_array($result->et_answer_authors) ? $result->et_answer_authors : array();
		$result->answered 			= in_array($current_user->ID, (array)$result->et_answer_authors);
		$result->has_category 		= !empty($result->question_category);
		$result->content_filter		= apply_filters( 'the_content', $post->post_content );
		$result->content_edit       = et_the_content_edit($post->post_content);
		$result->author_name 		= get_the_author_meta('display_name', $post->post_author);
		$result->followed			= in_array($current_user->ID, (array)$result->et_users_follow);
		$result->reported  			= in_array($current_user->ID,(array)get_post_meta($post->ID, 'et_users_report', true ));

		return $result;
	}

	/**
	 * Refresh question's meta
	 */
	public static function update_meta($id){
		// refresh last update
		$last_answers = get_posts(array(
			'post_type' 	=> 'answer',
			'post_parent' 	=> $id,
			'numberposts' 	=> 1
		));

		if ( isset($last_answers[0]) ){
			$last_answer = $last_answers[0];

			// update last answer author
			update_post_meta( $id, 'et_last_author', $last_answer->post_author );
		} else {
			delete_post_meta( $id, 'et_last_author' );
		}
	}

	/**
	 * Additional methods in theme
	 */
	public static function change_status($id, $new_status){
		$available_statuses = array('pending', 'publish', 'trash');

		if (in_array($new_status, $available_statuses))
			return self::update(array(
				'ID' => $id,
				'post_status' => $new_status
			));
		else
			return false;
	}

	// add new question
	public static function insert_question($title, $content, $cats, $status = "publish" , $author = 0){
		global $current_user;

		if ( empty($cats) ) return new WP_Error(__('Category must not empty', ET_DOMAIN));

		$data = array(
			'post_title' 		=> $title,
			'post_content' 		=> $content,
			'post_type' 		=> self::POST_TYPE,
			'post_author' 		=> !$author ? $current_user->ID : $author,
			'post_status' 		=> $status,
			'tax_input'			=> $cats,
			'et_updated_date' 	=> current_time( 'mysql' ),
		);

		$question_id = self::insert($data);

		//update question count
		$count = et_count_user_posts($current_user->ID, 'question');
		update_user_meta( $current_user->ID, 'et_question_count', $count );

		//update following questions
		$follow_questions = (array) get_user_meta( $current_user->ID, 'qa_following_questions', true );
		if(!in_array($question_id, $follow_questions))
			array_push($follow_questions, $question_id);
		$follow_questions = array_unique(array_filter($follow_questions));
		update_user_meta( $current_user->ID, 'qa_following_questions', $follow_questions );

		return $question_id;
	}

	// add like into database
	public static function toggle_like($question_id, $author = false){
		global $current_user;
		// required logged in
		if ( !$current_user->ID ) return false;

		// auto author
		if ( !$author ) $author = $current_user->ID;

		// get current likes list
		$likes = get_post_meta( $question_id, 'et_likes', true );

		// clear array
		if (!is_array($likes)) $likes = array();

		// add new author id
		$index = array_search($author, $likes);

		if ( $index === false){
			//$likes[] = $author;
			array_unshift($likes, $author);
			fe_update_user_likes($question_id);
		} else {
			foreach ($likes as $i => $id) {
				if ( $id == $author )
					unset($likes[$i]);
			}
			fe_update_user_likes($question_id,'unlike');
		}

		// update to database
		update_post_meta( $question_id, 'et_likes', $likes);
		update_post_meta( $question_id, 'et_like_count', count($likes));

		return $likes;
	}

	public static function report($question_id){
		global $current_user;
		// required logged in
		if ( !$current_user->ID ) return false;

		// get reports list
		$reports = QA_Questions::get_field($question_id, 'et_reports');

		//
		if ( !is_array($reports) ) $reports = array();

		if ( !in_array($current_user->ID, $reports) )
			$reports[] = $current_user->ID;

		QA_Questions::update_field($question_id, 'et_reports', $reports);
		return true;
	}

	public static function close($question_id){
		global $current_user;

		if ( !current_user_can( 'close_questions' ) ) return new WP_Error('permission_denied', __('Permission denied', ET_DOMAIN));

		//
		$result = QA_Questions::update( array(
			'ID' 			=> $question_id,
			'post_status' 	=> 'closed'
		) );

		return $result;
	}

	/**
	 * Retrieve comment number of a question and save to database
	 */
	public static function count_comments($question_id){
		global $wpdb;

		$sql 	= "SELECT count(*) FROM {$wpdb->posts} WHERE post_parent = $question_id AND post_type = 'answer' AND post_status = 'publish'";
		$count 	= $wpdb->get_var($sql);

		// save
		update_post_meta($question_id, 'et_answers_count', (int) $count);

		return $count;
	}

	public static function update_field($id, $key, $value){
		$instance = self::get_instance();

		$instance->_update_field($id, $key, $value);
	}

	public static function get_field($id, $key){
		$instance = self::get_instance();

		return $instance->_get_field($id, $key);
	}

	// search
	public static function search($data){

		$data = wp_parse_args( $data, array(
			'post_type' 	=> array(self::POST_TYPE),
			'post_status' 	=> array('publish','closed')
		) );

		if ($data['s']){
			global $et_query;
			$et_query['s'] = explode(' ', $data['s']);
			//unset($data['s']);
		}

		$query = new WP_Query($data);

		return $query;
	}

	/**
	 * Add a question category and colors
	 * @param $name category name
	 * @param $color category color, a hex code
	 * @param $parent parent category id, this is optional
	 * @return return array of term id and taxonomy
	 */
	public static function add_category($name, $color, $parent = 0){
		if ( $parent )
			$result = wp_insert_term( $name, 'question_category', array('parent' => $parent));
		else
			$result = wp_insert_term( $name, 'question_category');

		if ( !is_wp_error( $result ) ){
			$colors 					= get_option('et_category_colors', array());
			$colors[$result['term_id']] = (int)$color;

			update_option('et_category_colors', $colors);
		}

		return $result;
	}

	/**
	 * Edit a question category
	 * @param int $id term id
	 * @param array $args argument contain new values (name and color)
	 */
	public static function update_category($id, array $args){
		if (!empty($args)){
			// update normal params
			if ( !empty($args['name']) ){
				wp_update_term( $id, 'question_category', array('name' => $args['name']) );
			}

			// update color
			if ( !empty($args['color']) ){
				$colors 					= get_option('et_category_colors', array());
				$colors[$result['term_id']] = $color;

				update_option('et_category_colors', $colors);
			}
		}
	}

	public static function get_questions($args = array()){
		$args = wp_parse_args(  $args, array(
			'post_type'   => 'question',
		) );
		$query = new WP_Query($args);
		return $query;
	}

	/**
	 * static function callto set question answer
	 * @param int $question_id
	 * @param int $answer_id
	*/
	static public function mark_answer( $question_id, $answer_id ) {
		global $user_ID;
		/**
		 * get question pre answer
		*/
		$pre_answer	=	self::get_field( $question_id, 'et_best_answer' );
		/**
		 * update question's answer
		*/
		self::update_field( $question_id, 'et_best_answer', $answer_id);
		/**
		 * set answer id is best answer
		*/
		QA_Answers::update_field( $answer_id , 'et_is_best_answer',  current_time('mysql') );

		/**
		 * delete pre answer
		*/
		if( $pre_answer ) {
			delete_post_meta( $pre_answer, 'et_is_best_answer' );
			/**
			 * do action when an answer was unmark best answer
			*/
			do_action('qa_remove_answer', $pre_answer );
		}
		/**
		 * do action when an question was mark answered
		*/
		do_action( 'qa_mark_answer', $question_id , $answer_id );
	}

}
// end QA_Questions
/**
 * Class QA_Answers
 */
class QA_Answers extends ET_PostType {
	CONST POST_TYPE = 'answer';

	static $instance = null;

	public function __construct(){
		$this->name = self::POST_TYPE;
		$this->args = array(
			'labels' => array(
			    'name' => __('Answers', ET_DOMAIN),
			    'singular_name' => __('Answer', ET_DOMAIN),
			    'add_new' => __('Add New', ET_DOMAIN),
			    'add_new_item' => __('Add New Answer', ET_DOMAIN),
			    'edit_item' => __('Edit Answer', ET_DOMAIN),
			    'new_item' => __('New Answer', ET_DOMAIN),
			    'all_items' => __('All Answers', ET_DOMAIN),
			    'view_item' => __('View Answer', ET_DOMAIN),
			    'search_items' => __('Search Answers', ET_DOMAIN),
			    'not_found' =>  __('No answers found', ET_DOMAIN),
			    'not_found_in_trash' => __('No answers found in Trash', ET_DOMAIN),
			    'parent_item_colon' => '',
			    'menu_name' => __('Answers', ET_DOMAIN)
			),
		    'public' => true,
		    'publicly_queryable' => true,
		    'show_ui' => true,
		    'show_in_menu' => true,
		    'query_var' => true,
		    'rewrite' => array( 'slug' => apply_filters( 'fe_answer_slug' , 'answer' )),
		    'capability_type' => 'post',
		    'has_archive' => 'answers',
		    'hierarchical' => false,
		    'menu_position' => null,
		    'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'custom-fields',  'revisions' )
		);
		$this->taxonomies = array();
		$this->meta_data = apply_filters( 'answer_meta_fields', array(
			'et_vote_count',
			'et_answer_authors',
			'et_answers_count',
			'et_vote_up_authors',
			'et_vote_down_authors',
			'et_is_best_answer'
		));

		parent::__construct( self::POST_TYPE , $this->args, $this->taxonomies, $this->meta_data );
	}

	static public function init(){
		$instance = self::get_instance();
		parent::_init( self::POST_TYPE , $instance->args, $instance->taxonomies);
	}

	/**
	 * Action trigger after delete post
	 */
	public function action_delete_post($post_id){
		$post = get_post($post_id);
		if ( !empty($post) && $post->post_status == self::POST_TYPE )
			$this->action_update_counter($post_id, $post);
	}

	public function action_update_counter($post_id, $post){
		if ( $post->post_type != self::POST_TYPE ) return;

		// get answer parent
		$answer_parent 	= get_post_meta( $post_id, 'et_answer_parent', true );

		if ( $answer_parent ){
			QA_Answers::count_comments($answer_parent);
		} else {
			QA_Questions::count_comments($post->post_parent);
		}
	}

	static public function get_instance(){
		if ( self::$instance == null){
			self::$instance = new QA_Answers();
		}
		return self::$instance;
	}

	public static function insert($data){

		global $user_ID;
		$data['post_author'] 	= isset($data['post_author']) ? $data['post_author'] : $user_ID;

		// perform action
		$instance 	= self::get_instance();
		$result 	=  $instance->_insert($data);

		return $result;
	}

	/**
	 * update_answer
	*/
	public static function update($data){
		global $current_user;
		if(isset($data['post_author']) && $current_user->ID != $data['post_author'] && !qa_user_can('edit_answer')) { // check user cap with edit answer
			/**
			 * get site privileges
			*/
			$privileges	=	qa_get_privileges();

			return new WP_Error('cannot_edit', sprintf( __("You must have %d points to edit answer.", ET_DOMAIN), $privileges->edit_answer) );

		}

		$instance = self::get_instance();
		$result =  $instance->_update($data);

		return $result;
	}
	/**
	 * Delete a answer and child answers
	 * @param int $id
	 * @param bool $force_delete
	 * @return bool $success
	 */
	public static function delete($id, $force_delete = false){
		$instance = self::get_instance();

		$answer   = get_post($id);
		$question = get_post( $answer->post_parent );
		$answer   = QA_Answers::convert($answer);
		$question = QA_Questions::convert($question);

		/* also delete question likes */
		$comments = get_comments(array(
	            'post_id'       => $id,
	            'parent'        => 0,
	            'status'        => 'approve',
	            'post_status'   => 'publish',
			));

		if (is_array($comments) && count($comments) > 0) {

		    foreach($comments as $comment){
		    	wp_delete_comment( $comment->comment_ID, $force_delete );
		    }
		}

		$success = $instance->_delete($id, $force_delete);

		if($success){

			//update answer count
			$count = et_count_user_posts($answer->post_author, 'answer');
			update_user_meta( $answer->post_author, 'et_answer_count', $count );

			//update status answered for question:
			$is_best_answer = get_post_meta( $id, 'et_is_best_answer', true );
			if($is_best_answer){
				delete_post_meta( $question->ID, 'et_best_answer' );
			}
		}

		return $success;
	}

	public static function get($id){
		return	self::get_instance()->_get($id);
	}

	public static function convert($post){
		global $current_user;
		$result = self::get_instance()->_convert($post);
		$parent = get_post($result->post_parent);

		$result->et_vote_up_authors 	= is_array($result->et_vote_up_authors) ? $result->et_vote_up_authors : array();
		$result->et_vote_down_authors 	= is_array($result->et_vote_down_authors) ? $result->et_vote_down_authors : array();
		$result->voted_down 			= in_array($current_user->ID, (array)$result->et_vote_down_authors);
		$result->voted_up 				= in_array($current_user->ID, (array)$result->et_vote_up_authors);
		$result->et_vote_count 			= get_post_meta( $post->ID, 'et_vote_count', true ) ? get_post_meta( $post->ID, 'et_vote_count', true ) : 0;
		$result->user_badge 		= qa_user_badge( $post->post_author, false, et_load_mobile() );
		$result->et_answers_count 	= $result->et_answers_count ? $result->et_answers_count : 0;
		$result->et_answer_authors 	= is_array($result->et_answer_authors) ? $result->et_answer_authors : array();
		$result->avatar 			= et_get_avatar( $result->post_author , 30 );
		$result->new_nonce			= wp_create_nonce( 'insert_comment' );
		$result->human_date 		= et_the_time(strtotime($result->post_date));
		$result->content_filter 	= apply_filters( 'the_content', $result->post_content );
		$result->content_edit       = et_the_content_edit($post->post_content);
		$result->parent_author		= $parent->post_author;
		$result->comments			= sprintf( __( 'Comment(%d) ', ET_DOMAIN ), $result->comment_count);
		$result->author_name 		= get_the_author_meta('display_name', $post->post_author);
		$result->author_url 		= get_author_posts_url($post->post_author);
		$result->reported  			= in_array($current_user->ID,(array)get_post_meta($post->ID, 'et_users_report', true ));
		return $result;
	}

	/**
	 * Additional methods in theme
	 */

	public static function insert_answer($question_id, $content, $author = false, $answer_id = 0){
		$instance = self::get_instance();

		global $current_user;

		if(!$current_user->ID)
			return new WP_Error('logged_in_required', __('Login Required',ET_DOMAIN));

		if($author == false)
			$author = $current_user->ID;

		$question = get_post($question_id);

		$content  = preg_replace('/\[quote\].*(<br\s*\/?>\s*).*\[\/quote\]/', '', $content);
		//strip all tag for mobile
		if(et_load_mobile())
			$content = strip_tags($content, '<p><br>');
		$data     = array(
			'post_title'       => 'RE: ' . $question->post_title,
			'post_content'     => $content,
			'post_parent'      => $question_id,
			'author'           => $author,
			'post_type'        => 'answer',
			'post_status'      => ae_get_option('pending_answers') && !(current_user_can( 'manage_options' ) || qa_user_can( 'approve_answer' )) ? 'pending' : 'publish',
			'et_answer_parent' => $answer_id
		);

		$result = $instance->_insert($data);

		// if item is inserted successfully, update statistic
		if ($result){
			//update user answers count
			$count = et_count_user_posts($current_user->ID, 'answer');
			update_user_meta( $current_user->ID, 'et_answer_count', $count );

			//update user following questions
			$follow_questions = (array) get_user_meta( $current_user->ID, 'qa_following_questions', true );
			if(!in_array($question_id, $follow_questions))
				array_push($follow_questions, $question_id);
			$follow_questions = array_unique(array_filter($follow_questions));
			update_user_meta( $current_user->ID, 'qa_following_questions', $follow_questions );

			// update question's update date
			update_post_meta( $question_id , 'et_updated_date', current_time( 'mysql' ));

			// update last update author
			update_post_meta( $question_id , 'et_last_author', $author);

			// update answer_authors
			$answer_authors = get_post_meta( $question_id , 'et_answer_authors', true );
			$answer_authors = is_array($answer_authors) ? $answer_authors : array();
			if ( !in_array($author, $answer_authors) ){
				$answer_authors[] = $author;
				update_post_meta( $question_id, 'et_answer_authors', $answer_authors );
			}
			// update answer author for answer
			if ( $answer_id ){
				$answer_authors = get_post_meta( $answer_id , 'et_answer_authors', true );
				$answer_authors = is_array($answer_authors) ? $answer_authors : array();
				if ( !in_array($author, $answer_authors) ){
					$answer_authors[] = $author;
					update_post_meta( $answer_id, 'et_answer_authors', $answer_authors );
				}
			}

			if ( $answer_id == 0 ){
				QA_Questions::count_comments($question->ID);
			} else {
				QA_Answers::count_comments($answer_id);
			}
		}
		return $result;
	}

	/**
	 * Retrieve comment number of a question and save to database
	 */
	public static function count_comments($parent){
		global $wpdb;

		$sql 	= "SELECT count(*) FROM {$wpdb->posts}
					INNER JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id AND {$wpdb->postmeta}.meta_key = 'et_answer_parent'
					WHERE {$wpdb->postmeta}.meta_value = $parent AND {$wpdb->posts}.post_type = 'answer' AND {$wpdb->posts}.post_status = 'publish' ";

		$count 	= $wpdb->get_var($sql);

		// save
		update_post_meta($parent, 'et_answers_count', (int) $count);

		return $count;
	}

	public static function update_field($id, $key, $value){
		$instance = self::get_instance();

		$instance->_update_field($id, $key, $value);
	}
}

/**
 * Class QA_Comments
 */
class QA_Comments {

	public static function convert($comment){
		global $current_user;
		$result = (object) $comment;
		$childs = get_children( array('post_parent'=> $result->comment_ID) );
		$author = get_user_by( 'id', $comment->user_id );

		$result->id             = $result->comment_ID;
		$result->et_votes       = get_comment_meta( $result->comment_ID, 'et_votes');
		$result->et_votes_count = !empty($result->et_votes) ? count($result->et_votes) : 0;
		$result->content_filter = apply_filters( 'the_content', $result->comment_content );
		$result->content_edit   = et_the_content_edit($comment->comment_content);
		$result->avatar         = et_get_avatar( $result->user_id ? $result->user_id : $result->comment_author_email , 30 );
		$result->human_date     = et_the_time(strtotime($result->comment_date));
		$result->total_childs   = sprintf( __( 'Comment(%d) ', ET_DOMAIN ), count($childs));
		$result->new_nonce      = wp_create_nonce( 'insert_comment' );
		$result->author         = $author->display_name;
		$result->author_url 	= get_author_posts_url($author->ID);

		return $result;
	}
	public static function insert($data){
		$meta_data 	= array('et_votes','et_votes_count','et_answer_authors','et_answers_count');
		//strip all tag for mobile
		if(et_load_mobile())
			$data['comment_content'] = strip_tags($data['comment_content'], '<p><br>');
		$result = wp_insert_comment($data);
		foreach ($meta_data as $key => $value) {
			add_comment_meta( $result, $value, '');
		}
		return $result;
	}
	public static function update($data){
		remove_filter( 'pre_comment_content', 'wp_filter_kses');
		return wp_update_comment( $data );
	}
	public static function delete($data){
	}
}

/**
 * Get last page of question
 */
function et_get_last_page($post_id){

	$number     = get_option( 'comments_per_page' );

	$all_comments       = get_comments( array(
	    'post_id' 	  => $post_id,
	    'parent'  	  => 0,
	    'status' 	  => 'approve',
	    'post_status' => 'publish',
	    'type'		  => 'answer',
	    'order' 	  => 'ASC'
	 ) );

	$total_comments = count($all_comments);
	$total_pages    = ceil($total_comments / $number);

	if(!get_option( 'et_infinite_scroll' ) && $total_pages > 1 )
		return add_query_arg(array('page'=> $total_pages ),get_permalink( $post_id ));
	else
		return get_permalink( $post_id );
}
