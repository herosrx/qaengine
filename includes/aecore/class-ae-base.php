<?php
/**
 * interface
 * @package AE Core
 */
if ( !class_exists('AE_Base') ){
class AE_Base{

	// protected $filter_scripts = 'et_admin_enqueue_script';
	// protected $filter_style = 'et_admin_enqueue_style';

	const AJAX_PREFIX = 'wp_ajax_';
	const AJAX_NOPRIV_PREFIX = 'wp_ajax_nopriv_';

	const FILTER_SCRIPT = 'et_enqueue_script';
	const FILTER_STYLE = 'et_enqueue_style';

	//public function __construct() {}

	/**
	 * Add an action hook
	 * @param $hook
	 * @param $callback
	 * @param $priority
	 * @param $accepted_args
	 */
	public function add_action($hook, $callback, $priority = 10, $accepted_args = 1){
		add_action($hook, array($this, $callback), $priority, $accepted_args);
	}


	/**
	 * remove an action
	 * @param $hook
	 * @param $callback
	*/
	public function remove_action($hook, $callback){
		remove_action($hook, array($this, $callback));
	}

	/**
	 * Add a filter hook
	 * @param $hook
	 * @param $callback
	 * @param $priority
	 * @param $accepted_args
	 */
	public function add_filter($hook, $callback, $priority = 10, $accepted_args = 1){
		add_filter($hook, array($this, $callback), $priority, $accepted_args);
	}

	/**
	 * remove an action
	 * @param string $hook
	 * @param $callback
	*/
	public function remove_filter($hook, $callback){
		remove_filter($hook, array($this, $callback));
	}

	/**
	 * Add ajax action for short
	 * @param $hook
	 * @param $callback
	 * @param $priv
	 * @param $no_priv
	 */
	public function add_ajax($hook, $callback, $priv = true, $no_priv = true, $priority = 10, $accepted_args = 1 ){
		if ( $priv )
			$this->add_action( self::AJAX_PREFIX . $hook, $callback, $priority, $accepted_args );
		if ( $no_priv )
			$this->add_action( self::AJAX_NOPRIV_PREFIX . $hook, $callback, $priority, $accepted_args );
	}

	/**
	 * Register script and add it into queue
	 * @param $handle
	 * @param $src
	 * @param array $deps
	 * @param $ver
	 * @param $in_footer
	 */
	public function add_script($handle, $src, $deps = array(), $ver = false, $in_footer = true){
		$scripts = apply_filters( self::FILTER_SCRIPT, array(
			'handle' 	=> $handle,
			'src' 		=> $src,
			'deps' 		=> $deps,
			'ver' 		=> $ver,
			'in_footer' 	=> $in_footer
		));
		wp_register_script( $scripts['handle'], $scripts['src'], $scripts['deps'], $scripts['ver'], $scripts['in_footer']);
		wp_enqueue_script( $scripts['handle'] );
	}

	/**
	 * Register script 
	 * @param $handle
	 * @param $src
	 * @param array $deps
	 * @param $ver
	 * @param $in_footer
	 */

	public function register_script($handle, $src, $deps = array(), $ver = false, $in_footer = true){
		$scripts = apply_filters( self::FILTER_SCRIPT, array(
			'handle' 	=> $handle,
			'src' 		=> $src,
			'deps' 		=> $deps,
			'ver' 		=> $ver,
			'in_footer' 	=> $in_footer
		));
		wp_register_script( $scripts['handle'], $scripts['src'], $scripts['deps'], $scripts['ver'], $scripts['in_footer']);
	}

	/**
	 * enqueue an existed script
	 * @param $handle
	 * @param $src
	 * @param array $deps
	 * @param $ver
	 * @param $in_footer
	 */
	public function add_existed_script($handle , $src = '', $deps = array(), $ver=false, $in_footer = true ){
		wp_enqueue_script( $handle,  $src, $deps, $ver, $in_footer );
	}

	/**
	 * Register style and add it into queue
	 * @param $handle
	 * @param $src
	 * @param array $deps
	 * @param $ver
	 * @param $media
	 */
	public function add_style($handle, $src = false, $deps = array(), $ver = false, $media = 'all'){
		$style = apply_filters( self::FILTER_STYLE, array(
			'handle' 	=> $handle,
			'src' 		=> $src,
			'deps' 		=> $deps,
			'ver' 		=> $ver,
			'media' 	=> $media
		));
		wp_register_style( $style['handle'], $style['src'], $style['deps'], $style['ver'], $style['media'] );
		wp_enqueue_style( $style['handle'] );
	}

	/**
	 * Register script
	 * @param $handle
	 * @param $src
	 * @param array $deps
	 * @param $ver
	 * @param $media
	 */
	public function register_style($handle, $src = false, $deps = array(), $ver = false, $media = 'all'){
		$style = apply_filters( self::FILTER_STYLE, array(
			'handle' 	=> $handle,
			'src' 		=> $src,
			'deps' 		=> $deps,
			'ver' 		=> $ver,
			'media' 	=> $media
		));
		wp_register_style( $style['handle'], $style['src'], $style['deps'], $style['ver'], $style['media'] );
		wp_enqueue_style( $style['handle'] );
	}

	/**
	 * enqueue existed style
	 * @param $handle
	 */
	public function add_existed_style($handle){
		wp_enqueue_style( $handle );
	}
}
}

