<?php
/**
 * Class AE_Options: equivalent to an option in wp_engine, we use this to group many options of our themes into 1
 * Create an option group in wp_options using the name provided when construct the object, e.g.: 
 * 		$et_{theme}_general = new ET_Options("et_options");
 * Get & Set new option in this group using assignments & save() function, e.g.: 
 * 		$et_{theme}_general->logo = {url}; 
 * 		$et_{theme}_general->save();
 * 		echo $et_{theme}_general->logo;
 * Options in this group will be stored as an array, however to get an option, use -> instead of [], since this class use __get & __set methods.
 * This class keeps the old ways of retrieving options, so you can also use $et_{theme}_general->get_option() & update_options(), add_options() 
 * @author AnhCV
 * @version 1.0
 * @copyright enginethemes.com team
 * @license enginethemes.com team
 */
if(!class_exists( 'AE_Options' )) {
class AE_Options{
	protected $option_group; 			// equivalent 'option_name' of wp_options table. 'option_value' contains all options of the theme
	protected $options_arr = array(); // array contains the option_value of the option_name 
	
	static $instance	=	null;

	public static function get_instance() {
		if(self::$instance == null) {
			self::$instance	=	new AE_Options ('et_options');
		}
		return self::$instance;
	}

	/** 
	 * construct the option using the provided option_name,
	 * @param string $option_name 
	 */
	public function __construct( $option_name = 'et_options ' ) {
		$this->option_group = trim( $option_name );
		// get the current value of this option
		$existed			= get_option( $this->option_group );
		// if there is an existed value, assign it to the array
		if ($existed) $this->options_arr = $existed;
	}
	
	/**
	 * create or update an option name if not existed in this option group 
	 * this function is run when trying to write to an inaccessible property
	 * @param $option_name
	 * @param $option_value
	 * @return return empty string if setting new value or return old value if overwriting
	 */
	public function __set( $option_name, $option_value ){
		return $this->options_arr[$option_name] = $option_value;
			//$this->save ();
	}
	
	/**
	 * read an option in this option group
	 * this function is run when trying to read an inaccessible property
	 * @param $option_name
	 */
	public function __get( $option_name ){

		if ( array_key_exists( $option_name, $this->options_arr ) ) {
			
			return $this->options_arr[$option_name];

		}

		return false;
	}
	
	/**
	 * check if an option is existed or not
	 * this function is run when trying to isset() or empty() an inaccessible property.
	 * @param $option_name
	 */
	public function __isset( $option_name ){
		return isset( $this->options_arr[$option_name] );
	}
	
	/**
	 * unset an option if existed
	 * this function is run when trying to unset() an inaccessible property
	 * @param $option_name
	 */
	public function __unset( $option_name ){
		unset( $this->options_arr[$option_name] );
	}
	
	/**
	 * save the current option values into database
	 */
	public function save(){
		return update_option( $this->option_group, $this->options_arr );
	}
	/**
	 * save the current option values into database
	 */
	public function reset( $option_arr = array() ){
		$this->options_arr	=	$option_arr;
		return update_option( $this->option_group, $option_arr );
	}

	/**
	 * use 'echo' to print all options of the theme
	 */
	public function __toString() {
		return '<pre>' . print_r( $this->options_arr, true ) . '</pre>';
	}
	
	/**
	 * return option with option name corresponding
	 * 
	 * @author dakachi
	 * 
	 * @param option_name
	 * @return value of option (string or array)
	 */
	public function get_option( $option_name , $default	=	false ) {
		if ( ! isset( $this->options_arr[$option_name] ) ) {
			return $default;
		}
		return $this->options_arr[$option_name];
	}
	
	/**
	 * update option value
	 * 
	 * @author dakachi
	 * 
	 * if options containing null, return false. 
	 * if successful return true 
	 * @param string $option_name
	 * @param $option_value
	 */
	public function update_option( $option_name, $new_value ) {		
		if(current_user_can('manage_options')) {
			$this->options_arr[$option_name] = $new_value;
			return update_option($this->option_group, $this->options_arr);
		} else 
			return false;
	}

	/**
	 * add new option
	 * 
	 * @author dakachi
	 * 
	 * if options containing null, return false. 
	 * if successful return true 
	 * @param string $option_name
	 * @param $option_value
	 */
	public function add_option( $option_name, $value ) {
		if(current_user_can('manage_options')) {
			return self::update_option($option_name, $value );
		} else 
			return false;
	}
	
	/**
	 *  return current option values of this object
	 */
	public function get_all_current_options () {
		return $this->options_arr;
	}
	
	/**
	 * return option values of this object in database
	 */
	public function get_all_options_in_database () {
		return get_option( $this->option_group );
	}
	
	/**
	 * validate option
	 * @param $type data type 
	 * @param $value will be validate
	 * @return bool
	*/
	protected  static function  validate ( $type , $value ) {
		$validate	=	new ET_Validator();
		return $validate->validate( $type, $value);
	}
	
}
}