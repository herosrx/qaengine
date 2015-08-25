<?php
/**
 * Plugin updater for Engine Themes
 */
class ET_Update
{
	/**
	 * Product version
	 * @var string
	 */
	public $current_version;
	/**
	 * Product update path
	 * @var string
	 */
	public $update_path;
	/**
	 * Product info url
	 * @var string
	 */
	public $product_url;
	/**
	 * User license key
	 * @var string
	 */
	public $license_key;

	/**
	 * Initialize a new instance of the Engine Theme Auto-Update class
	 *
	 * @param string $current_version
	 * @param string $update_path
	 * @param string $plugin_slug
	 */
	function __construct($current_version, $update_path, $product_slug){
		$this->current_version 	= $current_version;
		$this->update_path 		= $update_path;
		$this->product_slug 	= $product_slug;
		$this->product_url 		= $update_path;
		$this->license_key 		= get_option('et_license_key');

		//add_filter('upgrader_clear_destination', array(&$this, 'delete_old_theme'), 10, 4 );
	}

	/**
	 * Add our self-hosted autoupdate plugin to the filter transient
	 * @param $transient
	 * @return object $ transient
	 */
	public function check_update($update_info)
	{
		global $wp_version;
		
		if ( empty($update_info->checked) )
			return $update_info;

		// get remote version
		$remote_version = $this->get_remote_version();
		// if a new version is alvaiable, add the update
		if ( version_compare( $this->current_version, $remote_version, '<')){
			$obj 				= new stdClass();
			$obj->slug 			= $this->product_slug;
			$obj->new_version 	= $remote_version;
			$obj->url 			= $this->product_url;
			$obj->package 		= add_query_arg('key', $this->license_key ,$this->update_path);
			$update_info->response[$this->product_slug] = $obj;
		}
		return $update_info;
	}

	public function delete_old_theme($removed, $local_destination, $remote_destination, $theme){
		if ( isset($theme['theme']) && $theme['theme'] == 'jobengine' ){

		}
	}

	/**
	 * Return the remote version 
	 * @return string $remote_version
	 */
	public function get_remote_version()
	{
		// send version request
		$request = wp_remote_post($this->update_path, array(
			'body' => array(
				'action' 		=> 'version',
				'product' 		=> $this->product_slug,
				'key' 			=> $this->license_key
			)));
		// check request if it is valid
		if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {  
			return preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $request['body']);//$request['body']; 
		}  
		return false;
	}
	
	/**
	 * Return the status of the plugin licensing
	 * @return boolean $remote_license
	 */
	public function getRemote_license()
	{
	}
}

/**
 * Handle updating themes for engine themes
 */
class ET_Theme_Updater extends ET_Update{
	public function __construct($current_version, $update_path, $product_slug, $product_url = ''){
		parent::__construct($current_version, $update_path, $product_slug);
		$this->product_url = $product_url;

		// define the alternative API for updating checking  
		add_filter('pre_set_site_transient_update_themes', array(&$this, 'check_update'));
	}
	/**
	 * Add our self-hosted autoupdate plugin to the filter transient
	 * @param $transient
	 * @return object $ transient
	 */
	public function check_update($update_info)
	{
		global $wp_version;
		
		if ( empty($update_info->checked) )
			return $update_info;

		// get remote version
		$remote_version = $this->get_remote_version();
		// if a new version is alvaiable, add the update
		if ( version_compare( $this->current_version, $remote_version, '<')){
			$obj 				= new stdClass();
			$obj->slug 			= $this->product_slug;
			$obj->new_version 	= $remote_version;
			$obj->url 			= $this->product_url;
			$obj->package 		= add_query_arg( array(
				'key' 	=> $this->license_key,
				'type' 	=> 'theme'
				), $this->update_path); //$this->update_path; 
			$update_info->response[$this->product_slug] = (array)$obj;
		}
		return $update_info;
	}
}

/**
 * Declare new backend menu for update information
 */
class ET_Menu_Update extends AE_Base{
	public function __construct(){
		add_action('wp_ajax_et-update-license-key', array($this, 'update_license'));
	}

	// update license
	function update_license(){
		try {
			if (empty($_POST['key'])) throw new Exception(__('Key is invalid', ET_DOMAIN));
			update_option('et_license_key', stripslashes($_POST['key']));
			$resp = array(
				'success' 	=> true,
				'msg' 		=> ''
			);
		} catch (Exception $e) {
			$resp = array(
				'success' 	=> false,
				'msg' 		=> $e->getMessage()
			);
		}
		header( 'HTTP/1.0 200 OK' );
		header( 'Content-type: application/json' );
		echo json_encode($resp);
		exit;
	}
}

add_action('init', 'et_add_update_menu');
function et_add_update_menu(){
}

// initialize theme update
add_action('init', 'et_check_update');
function et_check_update(){
	global $et_themes_updater, $et_plugins_updater;

	// install themes updater
	$update_path = ET_UPDATE_PATH . '&product=qaengine&type=theme';
	new ET_Theme_Updater(ET_VERSION, $update_path, 'qaengine', 'http://enginethemes.com');
	// add menu update
	new ET_Menu_Update();

}
?>