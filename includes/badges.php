<?php
/**
 * class QA_BadgeOptions, store badges level and badges point to option
 * @package qaengine
 * @author Dakachi
*/
class QA_BadgeOptions extends AE_Options {
	static $instance	=	null;

	public static function get_instance() {
		if(self::$instance == null) {
			self::$instance	=	new QA_BadgeOptions ('qa_badges');
		}
		return self::$instance;
	}

}

/**
 * register post type pack to content badges level data
*/
add_action('init', 'ae_register_post_type');
function ae_register_post_type() {
	register_post_type('pack', array(
		'labels' => array(
		    'name' => __( 'Pack', ET_DOMAIN ),
		    'singular_name' => __('Pack', ET_DOMAIN ),
		    'add_new' => __('Add New', ET_DOMAIN ),
		    'add_new_item' => __('Add New Pack', ET_DOMAIN ),
		    'edit_item' => __('Edit Pack', ET_DOMAIN ),
		    'new_item' => __('New Pack', ET_DOMAIN ),
		    'all_items' => __('All Packs', ET_DOMAIN ),
		    'view_item' => __('View Pack', ET_DOMAIN ),
		    'search_items' => __('Search Packs', ET_DOMAIN ),
		    'not_found' =>  __('No Pack found', ET_DOMAIN ),
		    'not_found_in_trash' => __('NoPacks found in Trash', ET_DOMAIN ), 
		    'parent_item_colon' => '',
		    'menu_name' => __('Packs', ET_DOMAIN )
		),
	    'public' 			=> false,
	    'publicly_queryable'=> true,
	    'show_ui' 			=> true, 
	    'show_in_menu' 		=> true, 
	    'query_var' 		=> true,
	    'rewrite' 			=> true,
	    'capability_type' 	=> 'post',
	    'has_archive' 		=> 'packs', 
	    'hierarchical' 		=> false,
	    'menu_position' 	=> null,
	    'support'			=> array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'custom-fields' )
	));
}

/**
 * class QA_Pack control the way to act with post type pack
 * @author Dakachi
 * @package qaEngine
 * @version 1.0
*/
class QA_Pack extends AE_Posts {
	static $instance;
	/**
	 * return class $instance
	*/
	public static function get_instance() {
		if(self::$instance == null) {
			
			self::$instance	=	new QA_Pack ();
		}
		return self::$instance;
	}
	/**
	 * construct instance, set post_type and meta data
	 * @since 1.0
	*/
	function __construct() {
		$this->post_type	=	'pack';
		$this->meta			=	array('qa_badge_point', 'qa_badge_color' , 'option_name' );
		
		/**
		 * setup convert field of post data
		*/
		$this->convert	=	array('post_title', 'post_name', 'post_content', 'ID');
	}

	/**
	 * static function query badges
	 * @param array $args query params, see more about this on WP_Query 
	 * @return object WP_Query
	 * @since 1.0
	 * @author Dakachi
	*/
	public static function query( $args ) {
		$args['post_type']	=	'pack';
		$args['showposts']	=	-1;
		$args['orderby']	=	'meta_value_num';
		$args['meta_key']	=	'qa_badge_point';
		/**
		 * construct WP_Query object
		*/
		$post_query	=	new WP_Query($args);
		return $post_query;
	}
	/**
	 * convert a post to pack object, which contain meta and tax data
	 * @param $object Post
	 * @return $object Post with meta data
	 * @since 1.0
	 * @author Dakachi
	*/
	public static function qa_convert($post) {
		$instance	=	self::get_instance();
		$result =  $instance->convert( $post );
		$result->qa_point_text	= sprintf(__( '%d points' , ET_DOMAIN ), $result->qa_badge_point );
		return $result;
	}

}

/**
 * class QA_PackAction control all action do with object QA_Pack
 * @package qaengine
 * @version 1.0
*/
class QA_PackAction extends AE_Base {
	function __construct() {
		$this->post	=	QA_Pack::get_instance();

		// add an action to catch ajax request sync pack
		$this->add_ajax('ae-pack-sync', 'pack_sync', true, false ) ;

	}
	/**
	 * catch ajax request ae-pack-sync
	*/
	public function pack_sync () {

		$request	=	$_REQUEST;
		unset($request['action']);

		extract($request);
		
		$request['post_content']	=	__( 'content here' , ET_DOMAIN );
		$request['post_status']		=	'publish';

		$request['option_name']	=	'qa_level';

		/**
		 * call instance sync
		*/
		$result	=	$this->post->sync ( $request );
		if( $result && !is_wp_error( $result ) ) { // send back if success
			$result->qa_point_text	= sprintf(__( '%d points' , ET_DOMAIN ), $result->qa_badge_point );

			/**
			 * update badges options
			*/
			$badges		=	QA_Pack::query(array());
			while( $badges->have_posts() ) { $badges->the_post();
				global $post;
				$pack		 =	QA_Pack::qa_convert($post);
				$pack_list[] =  $pack;
			}
			update_option( 'qa_level' , $pack_list );

			wp_send_json( array( 'success' => true, 
								'data' => $result, 
								'msg' => __("Sync success.", ET_DOMAIN) 
							)
					);
		}else { // notice if false
			wp_send_json(array(	'success' => false,
								'msg' => $result->get_error_message()
							)
					);
		}
		
	}
}

/**
 * ajax option sync
*/
add_action( 'wp_ajax_ae-badge-sync', 'qa_badge_sync' );
function qa_badge_sync() {

	$request	=	$_REQUEST;
	$name		=	$request['name'];
	$value		=	array();
	if(isset( $request['group'] ) && $request['group'] )
		parse_str( $request['value'] , $value);		
	else $value	=	$request['value'];
	/**
	 * save option to database
	*/
	$options		=	QA_BadgeOptions::get_instance();
	$options->$name	=	$value;
	$options->save();
	/**
	 * search index id in option array
	*/
	$options_arr	=	$options->get_all_current_options();
	$id	=	array_search( $name, array_keys( $options_arr ) );
	$response	=	array( 'success' => true , 
							'data' => array('ID' => $id ) , 
							'msg' => __("Update option successfully!", ET_DOMAIN) 
						);
	wp_send_json( $response );
	
}

/**
 * get privileges options with privileges and point
 * @return array option which user can do by his point
 * @author Dakachi
*/
function qa_get_privileges() {
	$badge	=	QA_BadgeOptions::get_instance();
	return (object)$badge->privileges;
}

/**
 * get badge options 
 * @return array
 * @author dakachi
*/
function qa_get_badge_point() {
	$badge	=	QA_BadgeOptions::get_instance();
	return (object)array_merge( $badge->pos_badges, $badge->neg_badges );
}
/**
 * return array of privileges that user can do on site
*/
function qa_privileges() {
	return  array(
		//'create_post'  => __("Ask a question or contribute an answer", ET_DOMAIN),
		'vote_up'        => __("Vote up a question/answer", ET_DOMAIN),
		'add_comment'    => __("Leave comments on other people's posts", ET_DOMAIN),
		'vote_down'      => __("Vote down a question/answer", ET_DOMAIN),
		//'edit_qa'      => __("Edits to any question/answer", ET_DOMAIN),
		'create_tag'     => __("Add new tags to the site", ET_DOMAIN),
		'edit_question'  => __("Edit any question", ET_DOMAIN),
		'edit_answer'    => __("Edit any answer", ET_DOMAIN),
		'approve_answer' => __("Approve any answer", ET_DOMAIN)
		 
	);
}

/**
 * get user point
 * @param int $user_id 
 * @return int user point
 * @author Dakachi
 * @package qaengine
*/
function qa_get_user_point ($user_id) {
	return get_user_meta( $user_id, 'qa_point', true );
}

/**
 * check user point and then get the badges ossociate with user
 * @param int $user_id 
 * @param bool $echo 
 * @author Dakachi
 * @package qaengine
*/
function qa_user_badge( $user_id, $echo = true, $mobile = false ) {
	
	$user_point	=	qa_get_user_point( $user_id );
	$levels		=	get_option( 'qa_level', array() );
	$badge		=	'';

	if(!empty($levels)){
		foreach($levels as $key => $level) {
			if( $level->qa_badge_point <= $user_point ) {

				if(!$mobile)
					$badge	.=	'<span title="'. $user_point .'" class="user-badge" style="background-color:'. $level->qa_badge_color .';">';
				else
					$badge	.=	'<span title="'. $user_point .'" class="user-badge" style="color:'. $level->qa_badge_color .';">';

				$badge	.=	 $level->post_title;
				break;
			}
		}
		$badge		.=	'</span>';
	} 

	$badge = $badge != '</span>' ? $badge : '<span title="'.__("Default", ET_DOMAIN).'" class="user-badge">'.__("Default", ET_DOMAIN).'</span>'; 

	if($echo) echo $badge;
	else  return $badge;
}

/**
 * check what use can do
 * @param string $cap
 * @return bool 
 * @author Dakachi
 * @package qaengine
*/
function qa_user_can( $cap = 'create_post' ) {
	global $user_ID;
	if( !$user_ID ) return false;
	/**
	 * get site privileges settings
	*/
	$privileges	=	qa_get_privileges();
	/**
	 * get user current point
	*/
	$point		=	qa_get_user_point($user_ID);

	/**
	 * set all cap for admininstrator
	*/
	if(current_user_can( 'manage_options' )) return true;
	/**
	 * check cap edit to all
	*/
	if($cap == 'edit' ) {
		if( $point >= $privileges->edit_qa ) return true;
	}
	/**
	 * if cap existe and user point is greater than cap required point return true
	*/
	if( isset($privileges->$cap) && ($point >= $privileges->$cap) ) {
		return true;
	}
	
	return false;
}
/**
 * get all user qa caps base on user point
 * @return array 
*/
function qa_get_user_caps() {
	global $user_ID;
	$cap	=	array ();
	if( $user_ID ) {
		$privileges	=	qa_get_privileges();
		$user_point	=	qa_get_user_point($user_ID);
	
		foreach ( $privileges as $privi => $point ) {
			if( $point > $user_point && !current_user_can( 'manage_options' ) ) continue;
			$cap[$privi]	=	true;
		}

		if( current_user_can('manage_options') /*|| $privileges->edit_qa < $user_point */) {
			$cap['edit']	=	true;
		}
	}

	return $cap;
}
// wp_list_post_revisions