<?php
class AE_Page extends AE_Base {
	function __construct() {
		$this->add_action('admin_print_styles' , 'print_styles');
		$this->add_action('admin_enqueue_scripts' , 'print_scripts');
	}
	/**
	 * add hook to register a men page , the hook admin_menu should be call in subclass
	*/
	function add_menu_page() {
		$args	=	$this->args;

		add_menu_page( 	
			$args['page_title'], 
			$args['menu_title'], 
			'manage_options', 
			$args['slug'], 
			'',
			'',
			4
		);

	}

	/**
	 * add hook to add submen page , the hook admin_menu should be call in subclass
	*/
	function sub_menu_page() {
		$args	=	$this->args;
		add_submenu_page( 	
			$args['parent_slug'],
			$args['page_title'], 
			$args['page_title'], 
			'manage_options', 
			$args['slug'], 
			array($this, 'render_frame') 
		);
	}

	public function print_styles () {
		$this->add_style('admin' , ae_get_url().'/assets/css/admin.css' );
		$this->add_style('ae-colorpicker' , ae_get_url().'/assets/css/colorpicker.css' );
	}

	public function print_scripts () {
		
		$this->add_existed_script( 'jquery' );
		// tam thoi add de xai 
		$this->add_script('jquery-validator', 	TEMPLATEURL . '/js/libs/jquery.validate.min.js','jquery');

		$this->add_script('ae-colorpicker' , ae_get_url().'/assets/js/colorpicker.js', array ( 'jquery') );
		// ae core js appengine
		$this->add_script('appengine' , ae_get_url().'/assets/js/appengine.js', array ('jquery' , 'underscore', 'backbone' , 'plupload' , 'ae-colorpicker') );
		// control backend user list
		$this->add_script('backend-user' , ae_get_url().'/assets/js/user-list.js', array ( 'appengine') );
		//  option settings and save
		$this->add_script('option-view' , ae_get_url().'/assets/js/option-view.js', array ( 'appengine') );
		// control option translate 
		$this->add_script('language-view' , ae_get_url().'/assets/js/language-view.js', array ( 'appengine', 'option-view') );
		// control pack view add delete update pack
		$this->add_script('pack-view' , ae_get_url().'/assets/js/pack-view.js', array ( 'appengine', 'option-view') );
		// backend js it should be separate by theme
		$this->add_script('backend' , ae_get_url().'/assets/js/backend.js', array ( 'appengine') );		

		wp_localize_script( 'appengine', 'ae_globals', 
						array(
							'ajaxURL'         => apply_filters( 'ae_ajax_url', admin_url( 'admin-ajax.php' ) ),
							'pending_answers' => ae_get_option("pending_answers", 0),
							'imgURL'          => ae_get_url().'/assets/img/',
							'jsURL'           => ae_get_url().'/assets/js/',
							'loadingImg'      => '<img class="loading loading-wheel" src="'. ae_get_url() . '/assets/img/loading.gif" alt="'.__('Loading...', ET_DOMAIN).'">',
							'loading'         => __('Loading', ET_DOMAIN),
							'plupload_config' => array(
								'max_file_size'       => '3mb',
								'url'                 => admin_url('admin-ajax.php'),
								'flash_swf_url'       => includes_url('js/plupload/plupload.flash.swf'),
								'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
								'filters'             => array( array( 'title' => __('Image Files',ET_DOMAIN), 'extensions' => 'jpg,jpeg,gif,png' ) )
							)

						) );
	}
	/**
	 * render the frame with is used by all page in backend
	*/
	function render_frame () {
	?>
		<!-- ================================ -->
		<!-- Admin Frame                      -->
		<!-- ================================ -->
		<div class="wrap">
			<div class="et-body">
				<div class="et-header">
					<div class="logo">
						<a href="http://www.enginethemes.com/"> Powered by <img src="<?php echo ae_get_url(); ?>/assets/img/engine-logo.png" /> </a>
					</div>
					<div class="slogan"><span><?php _e('Administration',ET_DOMAIN) ?></span>. <?php _e('You are an admin. Here you administrate.',ET_DOMAIN) ?></div>	    	
				</div>
				<div class="et-wrapper clearfix">
					<div class="et-left-column">						
							<?php $this->render_menu(); ?>
					</div>
					<div id="engine_setting_content" class="et-main-column clearfix">
						<?php 
							
							// admin page html body
							$this->render(); 
						?>

						<?php 
					//if( current_user_can( 'manage_options' ) || is_page_template( 'page-account-listing.php' )){
						echo '<div class="hidden">';
						wp_editor( 'div_load_tiny','div_load_tiny', ae_editor_settings());	
						echo '</div>';
					//}	
				?>
					</div>
				</div>
				

				<div class="et-footer"></div>
				<!--
				<div class="et-footer">
					If you have any troubles you can <a  href="javascript:void(0)">watch a video about this page <span class="icon" data-icon="V"></span></a>or  <a href="javascript:void(0)">send us a message <span class="icon" data-icon="M"></span></a>.
				</div>
				-->
			</div>
		</div><!-- wrap -->
		
		<?php 
	}

	/**
	 * render pages list menu title
	*/
	public function render_menu () {

		if(!empty($this->pages)) {
			echo '<ul class="et-menu-items font-quicksand">';
			$active	=	'';
			foreach ($this->pages as $key => $page) {
				$args	=	$page['args'];
				if($_REQUEST['page'] == $args['slug']) { // set current page active
					$active	=	'active';
				}

				if( !$args['icon'] ) {
					$args['icon'] = 'gear';
				}

				echo '<li>
						<a class="engine-menu '. $active .'" href="?page='. $args['slug'] .'">
							<div class="engine-menu-icon icon-'. $args['icon'] .'"></div><div class="">'. $args['menu_title'] .'</div>
						</a>
					</li>';
				$active	=	'';
			}
			echo '</ul>';
		}
	}

	/**
	 * render page container 
	*/
	public function render () {
		// admin page header
		if(isset($this->header)) {
			$this->header->render();
		}else{
			$this->header();
		}		

		$this->container->render();
	}
	/**
	 * render page header
	*/
	public function header () {
	?>
		<div class="et-main-header">
			<div class="title font-quicksand"><?php echo $this->args['menu_title']; ?></div>
			<?php if( isset($this->args['desc']) ) { ?>
				<div class="desc"><?php echo $this->args['desc']; ?></div>
			<?php } ?>
		</div>
	<?php 
	}
}

/**
 * this class just use to create an admin menu
*/
class AE_Menu extends AE_Page {
	static $instance = null;
	function __construct ( $pages ) {
		/**
		 * add action to add menu
		 * callback add_menu_page in parent class AE_Page
		*/
		$this->add_action('admin_menu', 'add_menu_page');
		/**
		 * ajax option sync
		*/
		$this->add_action( 'wp_ajax_ae-option-sync', 'action_sync' );
		/**
		 * ajax branding sync
		*/
		$this->add_action( 'wp_ajax_et-change-branding', 'change_branding' );

		/**
		 * add action to add menu to admin bar
		*/
		$this->add_action( 'admin_bar_menu', "admin_bar_menu" , 200);

		/**
		 * ajax fetch users sync
		*/
		// $this->add_action( 'wp_ajax_ae-fetch-users', 'fetch_user' );

		$this->args = array(
			'page_title' 	=> __('Engine Settings', ET_DOMAIN),
			'menu_title' 	=> __('Engine Settings', ET_DOMAIN),
			'cap' 			=> 'administrator',
			'slug' 			=> 'et-overview',
			'icon_url' 		=> '',
			'pos' 			=> 3
		);

		$this->pages	=	$pages;

		self::$instance	=	$this;

		$meta_data = array(
			'register_status',
			'qa_point',
			'is_ban',
			'et_question_count',
			'et_answer_count'
			);

		$user_action	=	new AE_UserAction( new AE_Users( $meta_data ) );
		$language		=	new AE_Language();

		$this->add_action( 'updated_option' , 'update_option', 10, 3);

	}

	public static function get_instance() {
		return self::$instance;
	}

	/**
	 * option sync , catch ajax option-sync 
	*/
    function action_sync() {
        
		$request = $_REQUEST;
		$name    = $request['name'];
		$value   = array();
        if (isset($request['group']) && $request['group']) parse_str($request['value'], $value);
        else $value = $request['value'];
        
        /**
         * save option to database
         */
        $options = AE_Options::get_instance();
        $options->$name = $value;
        $options->save();
        
        if ($name == 'blogname' || $name == 'blogdescription' || $name == 'et_license_key') update_option($name, $value);
        
        do_action('ae_save_option' , $name, $value);


        /**
         * search index id in option array
         */
		$options_arr = $options->get_all_current_options();
		$id          = array_search($name, array_keys($options_arr));
		$response    = array(
			'success' => true,
			'data'    => array(
			'ID'      => $id
			) ,
			'msg'     => __("Update option successfully!", ET_DOMAIN)
        );
        wp_send_json($response);
    }

	/**
	 * catch hook update option blog name
	*/
	public function update_option( $option, $old, $new ) {
		/**
		 * save option to database
		*/
		if($option == 'et_options') return ;
		$options			=	AE_Options::get_instance();
		$options->$option	=	$new;
		$options->save();
	}

	/**
	 * update branding : logo, mobile icon
	*/
	function change_branding () {
		$res	= array(
			'success'	=> false,
			'msg'		=> __('There is an error occurred', ET_DOMAIN ),
			'code'		=> 400,
		);
		
		// check fileID
		if(!isset($_POST['fileID']) || empty($_POST['fileID']) || !isset($_POST['imgType']) || empty($_POST['imgType']) ){
			$res['msg']	= __('Missing image ID', ET_DOMAIN );
		}
		else {
			$fileID		= $_POST["fileID"];
			$imgType	= $_POST['imgType'];

				
			// check ajax nonce
			if ( !check_ajax_referer( $imgType . '_et_uploader', '_ajax_nonce', false ) ){
				$res['msg']	= __('Security error!', ET_DOMAIN );
			}
			elseif(isset($_FILES[$fileID])){

				// handle file upload
				$attach_id	=	et_process_file_upload( $_FILES[$fileID], 0, 0, array(
									'jpg|jpeg|jpe'	=> 'image/jpeg',
									'gif'			=> 'image/gif',
									'png'			=> 'image/png',
									'bmp'			=> 'image/bmp',
									'tif|tiff'		=> 'image/tiff'
									)
								);

				if ( !is_wp_error($attach_id) ){

					try {
						$attach_data	= et_get_attachment_data($attach_id);

						$options	=	AE_Options::get_instance();

						
						// save this setting to theme options
						$options->$imgType	=	$attach_data;						
						$options->save();

						$res	= array(
							'success'	=> true,
							'msg'		=> __('Branding image has been uploaded successfully', ET_DOMAIN ),
							'data'		=> $attach_data
						);
					}
					catch (Exception $e) {
						$res['msg']	= __( 'Error when updating settings.', ET_DOMAIN );
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
		wp_send_json($res);
	}

	/**
	 * add add page to admin menu bar
	 * @since 1.0
	 * @package AE
	 * @author Dakachi
	 */
	public function admin_bar_menu() {
		global $et_admin_page, $wp_admin_bar;
		//
		//if ( !method_exists($et_admin_page, 'get_menu_items') ) return false;
		if ( !current_user_can('manage_options') || !apply_filters( 'ae_admin_bar_menu', true ) ) return false;

		$parent = 'ae_menu';

		$wp_admin_bar->add_menu(array(
			'id' 		=> $parent,
			'title' 	=> __('Site Dashboard', ET_DOMAIN),
			'href' 		=> false
		));

		foreach ($this->pages as $key => $item) {
			$page_arg	=	$item['args'];
			$page	=	array (
					'parent' 	=> $parent,
					'id' 		=> $page_arg['slug'],
					'title' 	=> $page_arg['page_title'],
					'href' 		=> admin_url( '/admin.php?page='.$page_arg['slug'] )
				);

			$wp_admin_bar->add_menu( $page );
		}
	}

}

/**
 * register a admin submenu child of AE_Menu
*/
class AE_Submenu extends AE_Page {
	static $instance	=	null;
	function __construct ( $args , $pages ) {

		/**
		 * add action to add submenu
		 * callback sub_menu_page in parent class AE_Page
		*/
		$this->add_action('admin_menu', 'sub_menu_page');

		$this->args = $args['args'];
		// page container
		$this->container	=	$args['container'];
		// page header
		if( isset($args['header']) ) {
			$this->header	=	$args['header'];
		}			
		// all pages list
		$this->pages		=	$pages;

		self::$instance	=	$this;	
		parent::__construct();
	}
}





