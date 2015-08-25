<?php
// if(!defined('ET_DOMAIN')) {
// 	wp_die('API NOT SUPPORT');
//}
/**
 * Class AE posts, control all action with post data
 * @author Dakachi
 * @version 1.0
 * @package AE
 * @since 1.0
*/
class AE_Posts {
	static $instance;

	public $current_post;

	/**
	 * return class $instance
	*/
	public static function get_instance() {
		if(self::$instance == null) {
			
			self::$instance	=	new AE_Posts ();
		}
		return self::$instance;
	}

	/**
	 * contruct a object post with meta data
	 * @param string $post_type object post name
	 * @param array $taxs array of tax name assigned to post type
	 * @param array $meta_data all post meta data you want to control
	 * @author Dakachi
	 * @since 1.0 
	*/
	public function __construct( $post_type, $taxs = array() , $meta_data = array()){

		$this->post_type	=	$post_type;
		$this->taxs			=	$taxs;
		$defaults			=	array('location', 'address', 'avatar', 'post_count', 'comment_count' );
		$this->meta	=	wp_parse_args( $meta_data, $defaults );	

		/**
		 * setup convert field of post data
		*/
		$this->convert	=	array('post_title', 'post_name', 'post_content', 'ID');
		
	}
	/**
	 * convert post data to an object with meta data
	 * @param object $post 
	 * @return post object after convert
	 * 		   - wp_error object if post invalid
	 * @author Dakachi
	 * @since 1.0
	*/
	public function convert( $post ){
		$result = array();
		$post	= (array)$post;
		/**
		 * convert need post data
		*/
		foreach ($this->convert as $key ) {
			if( isset( $post[$key]) )  $result[$key]	=	$post[$key];
		}

		// generate post taxonomy
		if( !empty($this->taxs) ) {
			foreach($this->taxs as $name) {
				$result[$name]	 = wp_get_object_terms( $post['ID'], $name );
			}
		}

		// generate meta data
		if( !empty($this->meta) ) {
			foreach ($this->meta as $key) {
				$result[$key] 	= get_post_meta( $post['ID'], $key, true );
			}
		}
		
		unset($result['post_password']);
		$result['id']	=	$post['ID'];

		/**
		 * assign convert post to current post
		*/
		$this->current_post	=	apply_filters( 'ae_convert_post', (object)$result );

		return $this->current_post;
	}

	/**
	 * insert postdata and post metadata to an database
	 # used wp_insert_post
	 # used update_post_meta
	 # post AE_Posts function convert
	 * @param 	array $post data 
	 			# wordpress post fields data
	 			# post custom meta data
	 * @return 	post object after insert
	 	 		# wp_error object if post data invalid
	 * @author Dakachi
	 * @since 1.0
	*/
	public function insert( $args ){

		global $current_user, $user_ID;

		$args = wp_parse_args( $args, array(
            'post_type'     => $this->post_type, 
            'post_status'   => 'pending',
        ) );

        if( !isset($args['post_author']) || empty($args['post_author']) ) $args['post_author'] = $current_user->ID;

		//if ( empty( $args['post_author'] ) ) return new WP_Error('missing_author', __('Missing Author', ET_DOMAIN));

        // pre filter filter post args
        $args = apply_filters( 'ae_pre_insert_' . $this->post_type, $args );

        /**
         * insert post by wordpress function 
        */
        $result = wp_insert_post( $args, true );
        /**
         * update custom field and tax
        */
        if( $result != false && !is_wp_error( $result ) ){
        	$this->update_custom_field ($result, $args );
        	$args['ID']	=	$result;
        	$args['id']	=	$result;
        	// do action here
        	do_action('ae_insert_' . $this->post_type, $result, $args );

        	$result	=	(object)$args;
		}
        

        return $result;
	}

	/**
	 * update postdata and post metadata to an database
	 # used wp_update_post ,get_postdata
	 # used update_post_meta
	 # used AE_Users function convert
	 * @param 	array $post data 
	 			# wordpress post fields data
	 			# post custom meta data
	 * @return 	post object after insert
	 	 		# wp_error object if post data invalid
	 * @author Dakachi
	 * @since 1.0
	*/
	public function update( $args ){
		global $current_user;

		$args = wp_parse_args( $args );

		// filter args
        $args = apply_filters( 'ae_pre_update_' . $this->post_type, $args );

		// if missing ID, return errors
        if ( empty($args['ID']) ) return new WP_Error('ae_missing_ID', __('Post not found!', ET_DOMAIN));   

    	// update post data into database use wordpress function
        $result = wp_update_post( $args, true );
        /**
         * update custom field and tax
        */
        if( $result != false && !is_wp_error( $result ) ){
			$this->update_custom_field ($result, $args );
			// make an action so develop can modify it
			do_action('ae_update_' . $this->post_type, $result);
			$result	= $this->convert( get_post($result) ) ;
		}

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
	public function update_custom_field( $result, $args ) {

		if( !empty($this->taxs) ) {
			foreach ($this->taxs as $tax_name => $tax_args) {
				//if ( isset($args['tax_input'][$tax_name]) && term_exists($args['tax_input'][$tax_name], $tax_name) ){
				if ( isset($args['tax_input'][$tax_name]) ){
					$terms = wp_set_object_terms( $result, $args['tax_input'][$tax_name], $tax_name );
				}
			}
		}
		// update post meta
		if( !empty($this->meta) ) {
	    	foreach ($this->meta as $key => $value) {
	    		if( isset($args[$value]) ) update_post_meta( $result, $value, $args[$value] );
	    	}
	    }
        
	}
	/**
	 * delete post from site
	 * @param int $ID post id want to delete
	 * @param bool $force_delete defautl is false
	 * @author Dakachi
	 * @since version 1.0
	*/
	public function delete( $ID, $force_delete = false){
		if ( $force_delete ){
			$result = wp_delete_post( $ID, true );
		} else {
			$result = wp_trash_post( $ID );
		}
		if ( $result )
			do_action('et_delete_' . $this->post_type, $ID);

		return $this->convert( $result );
	}

	/**
	 * get postdata
	 * @param int $ID post id want to get
	 * @return object $post 
	 * @author Dakachi
	 * @since version 1.0
	*/
	public function get( $ID ){
		$result	= $this->convert( get_post( $ID ) ) ;
		return $result;
	}

	/**
	 * sync request from client, 
	 * request should have attribute method to specify which action want to do
	 * @param array $request
	 * @author Dakachi
	 * @since 1.0
	*/
	function sync ( $request ) {

		extract( $request );
		unset($request['method']);

		switch ( $method ) {
			case 'create':
				$result	=	$this->insert( $request );
				break;
			case 'update':
				$result	=	$this->update( $request );
				break;
			case 'remove':
				$result	=	$this->delete( $request['ID'] );
				break;
			case 'read':
				$result	=	$this->get( $request['ID'] );
				break;
			default : 
				return new WP_Error('invalid_method', __("Invalid method", ET_DOMAIN) );
		}

		return $result;
	}

	/**
	 * fetch postdata from database, use function convert
	 * @param array $args query options, see more WP_Query args
	 * @return array of objects post
	 * @author Dakachi
	 * @since 1.0
	*/
	public function fetch( $args ) {
		$args['post_type']	=	$this->post_type;

		$query	=	new WP_Query( $args );
		$data	=	array();

		// loop post
		if($query->have_posts()) {
			while( $query->have_posts() ) { $query->the_post();
				global $post;
				// convert post data
				$data[]	=	$this->convert($post);
			}
		}

		if(!empty($data)) {
			/**
			 * return array of data if success
			*/
			return array( 	'posts' => $data, // post data
							'post_count' => $query->post_count , // total post count
							'max_num_pages' => $query->max_num_pages, // total pages
							'query' => $query // wp_query object
						);
		} else {
			return false;
		}

	}
	
}