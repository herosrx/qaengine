<?php
function ae_get_url() {
	return TEMPLATEURL . '/includes/aecore';
}

function ae_get_path(){

}

function ae_get_template_part( $slug, $name ){
	if( $slug ) 
		$slug = '/includes/aecore/template/'.$slug;
	get_template_part( $slug, $name );
}

/**
 * get theme option
 * @param $name the name of option
 * @return $option_value
 * @author Dakachi
*/
function ae_get_option($name, $default = false) {
    $option = AE_Options::get_instance();
    return ($option->$name) ? $option->$name : $default;
}


/**
 * count user comment by email
 * @param emal $email (required) The email of user you want to count comments
 * @version 1.0
 * @package AE
 * @author Dakachi
*/
function ae_comment_count( $email ) {
	global $wpdb;
	$count = $wpdb->get_var('SELECT COUNT(comment_ID) FROM ' . $wpdb->comments. ' WHERE comment_author_email = "' . $email . '"');
	return $count;
}

/**
 * count user post by post type
 * @param (integer) $userID (required) The ID of the user to count posts for.
 * @param (string) $post_type The post type you want to count, Default is post.
 * @version 1.0
 * @author dakachi
 * @package AE
 * http://codex.wordpress.org/Function_Reference/count_user_posts
*/
function ae_count_user_posts_by_type( $userid, $post_type = 'post' ) {
	global $wpdb;

	$where = get_posts_by_author_sql( $post_type, true, $userid );

	$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts $where" );

  	return apply_filters( 'get_usernumposts', $count, $userid );
}


/*
 * return array wp_editor config
*/
function ae_editor_settings() {
	return apply_filters( 'ae_editor_settings', array(
		'quicktags'  	=> false,
		'media_buttons' => false,
		'wpautop'		=> false,
		'tabindex'		=>	'2',
		'teeny'			=> true,
		'tinymce'   	=> array(
			//'content_css'	=> get_template_directory_uri() . '/js/lib/tiny_mce/content.css',
			'height'   => 250,
			'autoresize_min_height'=> 250,  
			'autoresize_max_height'=> 550,
			'theme_advanced_buttons1' => 'bold,|,italic,|,underline,|,bullist,numlist,|,wp_fullscreen',
			'theme_advanced_buttons2' => '',
			'theme_advanced_buttons3' => '',
			'theme_advanced_statusbar_location' => 'none',
			'theme_advanced_resizing'	=> true ,
			'setup' =>  "function(ed){
				ed.onChange.add(function(ed, l) {
					var content	= ed.getContent();
					if( ed.isDirty() || content === '' ){
						ed.save();
						jQuery(ed.getElement()).blur(); // trigger change event for textarea
					}

				});

				// We set a tabindex value to the iframe instead of the initial textarea
				ed.onInit.add(function() {
					var editorId = ed.editorId,
						textarea = jQuery('#'+editorId);
					jQuery('#'+editorId+'_ifr').attr('tabindex', textarea.attr('tabindex'));
					textarea.attr('tabindex', null);
				});
			}"
		)
	));
}


/* add function */
/**
 * process uploaded image: save to upload_dir & create multiple sizes & generate metadata
 * @param  [type]  $file     [the $_FILES['data_name'] in request]
 * @param  [type]  $author   [ID of the author of this attachment]
 * @param  integer $parent=0 [ID of the parent post of this attachment]
 * @param  array [$mimes] [array of supported file extensions]
 * @return [int/WP_Error]	[attachment ID if successful, or WP_Error if upload failed]
 * @author anhcv
 */
// function et_process_file_upload( $file, $author=0, $parent=0, $mimes=array() ){

// 	global $user_ID;
// 	$author = ( 0 == $author || !is_numeric($author) ) ? $user_ID : $author;

// 	if( isset($file['name']) && $file['size'] > 0){

// 		// setup the overrides
// 		$overrides['test_form']	= false;
// 		if( !empty($mimes) && is_array($mimes) ){
// 			$overrides['mimes']	= $mimes;
// 		}

// 		// this function also check the filetype & return errors if having any
// 		if(!function_exists( 'wp_handle_upload' )) {
// 			require_once( ABSPATH . 'wp-admin/includes/file.php' );
// 		}
// 		$uploaded_file	=	wp_handle_upload( $file, $overrides );

// 		//if there was an error quit early
// 		if ( isset( $uploaded_file['error'] )) {
// 			return new WP_Error( 'upload_error', $uploaded_file['error'] );
// 		}
// 		elseif(isset($uploaded_file['file'])) {

// 			// The wp_insert_attachment function needs the literal system path, which was passed back from wp_handle_upload
// 			$file_name_and_location = $uploaded_file['file'];

// 			// Generate a title for the image that'll be used in the media library
// 			$file_title_for_media_library = preg_replace('/\.[^.]+$/', '', basename($file['name']));

// 			$wp_upload_dir = wp_upload_dir();

// 			// Set up options array to add this file as an attachment
// 			$attachment = array(
// 				'guid'				=> $uploaded_file['url'],
// 				'post_mime_type'	=> $uploaded_file['type'],
// 				'post_title'		=> $file_title_for_media_library,
// 				'post_content'		=> '',
// 				'post_status'		=> 'inherit',
// 				'post_author'		=> $author
// 			);

// 			// Run the wp_insert_attachment function. This adds the file to the media library and generates the thumbnails. If you wanted to attch this image to a post, you could pass the post id as a third param and it'd magically happen.
// 			$attach_id = wp_insert_attachment( $attachment, $file_name_and_location, $parent );
// 			require_once(ABSPATH . "wp-admin" . '/includes/image.php');
// 			$attach_data = wp_generate_attachment_metadata( $attach_id, $file_name_and_location );
// 			wp_update_attachment_metadata($attach_id,  $attach_data);
// 			return $attach_id;

// 		} else { // wp_handle_upload returned some kind of error. the return does contain error details, so you can use it here if you want.
// 			return new WP_Error( 'upload_error', __( 'There was a problem with your upload.', ET_DOMAIN ) );
// 		}
// 	}
// 	else { // No file was passed
// 		return new WP_Error( 'upload_error', __( 'Where is the file?', ET_DOMAIN ) );
// 	}
// }
/**
 * handle file upload prefilter to tracking error
*/
//remove_filter( 'wp_handle_upload_prefilter','check_upload_size' );
add_filter ( 'wp_handle_upload_prefilter', 'et_handle_upload_prefilter', 9);
function et_handle_upload_prefilter ($file) {
	if(!is_multisite()) return $file;

	if ( get_site_option( 'upload_space_check_disabled' ) )
		return $file;

	if ( $file['error'] != '0' ) // there's already an error
		return $file;

	if ( defined( 'WP_IMPORTING' ) )
		return $file;

	$space_allowed = 1048576 * get_space_allowed();
	$space_used = get_dirsize( BLOGUPLOADDIR );
	$space_left = $space_allowed - $space_used;
	$file_size = filesize( $file['tmp_name'] );
	if ( $space_left < $file_size )
		$file['error'] = sprintf( __( 'Not enough space to upload. %1$s KB needed.', ET_DOMAIN ), number_format( ($file_size - $space_left) /1024 ) );
	if ( $file_size > ( 1024 * get_site_option( 'fileupload_maxk', 1500 ) ) )
		$file['error'] = sprintf(__('This file is too big. Files must be less than %1$s KB in size.', ET_DOMAIN), get_site_option( 'fileupload_maxk', 1500 ) );
	if ( function_exists('upload_is_user_over_quota') && upload_is_user_over_quota( false ) ) {
		$file['error'] = __( 'You have used your space quota. Please delete files before uploading.',ET_DOMAIN );
	}


	// if ( $file['error'] != '0' && !isset($_POST['html-upload']) )
	// 	wp_die( $file['error'] . ' <a href="javascript:history.go(-1)">' . __( 'Back' ) . '</a>' );
	return $file;
}

/**
 * Return all sizes of an attachment
 * @param 	$attachment_id
 * @return 	an array with [key] as the size name & [value] is an array of image data in that size
 *             e.g:
 *             array(
 *             	'thumbnail'	=> array(
 *             		'src'	=> [url],
 *             		'width'	=> [width],
 *             		'height'=> [height]
 *             	)
 *             )
 * @since 1.0
 */
function et_get_attachment_data($attach_id, $size = array() ){

	// if invalid input, return false
	if (empty($attach_id) || !is_numeric($attach_id)) return false;

	$data		= array(
		'attach_id'	=> $attach_id
		);

	if(!empty($size)) {
		$all_sizes	=	$size;	
	}else {
		$all_sizes	= get_intermediate_image_sizes();
	} 
	
	foreach ($all_sizes as $size) {
		$data[$size]	= wp_get_attachment_image_src( $attach_id, $size );
	}
	return $data;
}