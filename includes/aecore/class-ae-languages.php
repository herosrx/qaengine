<?php
/**
 * Class AE_Languge control how to addnew language to site
 * support static function to load text domain
 * @author Dakachi
 * @version 1.0
*/
class AE_Language extends AE_Base {

	protected $_pot_name;
	protected $_pot;

	protected $_mo_name;
	/**
	 * a mo
	*/
	protected $_mo;
	/**
	 * current site language, get from AE_Options website_language attribute
	*/
	protected $_selected_lang;
	/**
	 * all language have in site
	*/
	protected $_language_list;
	/**
	 * wp includes dir
	*/
	protected $_wp_include_dir;
	/**
	 * site option instance AE_Options
	*/
	protected $option ;

	/**
	 * a static instance for singleton
	*/
	static $instance;


	public static function get_instance() {
		if(self::$instance == null) {
			self::$instance	=	new AE_Language ();
		}
		return self::$instance;
	}

	/**
	 * construct AE_Language instance, init hook
	 * @since 1.0
	*/
	function __construct () {

		if( !defined('THEME_LANGUAGE_PATH') ) {
			die('You have to defined THEME_LANGUAGE_PATH to use languge feature');
		}

		$this->_wp_include_dir 	= preg_replace('/wp-content$/', 'wp-includes', WP_CONTENT_DIR);

		if(!class_exists('PO')) {
			require_once $this->get_includes_dir ().'/pomo/po.php';
		}

		$this->_file_name 	= 'engine';
		// $this->_pot			= new MO();

		$this->_mo			= new MO();

		

		$this->option			=	AE_Options::get_instance();
		$this->_selected_lang	=	$this->option->current_language;
		
		/**
		 * request for add new language, generate mo file
		*/
		$this->add_action('wp_ajax_ae-language-sync', 'add_new_lang');
		/**
		 * request for change language  on site, update to AE_Options website_language attribute
		*/
		$this->add_ajax('ae-language-change', 'change_language', true, false);
		/**
		 * trigger save in translation form to update language 
		*/
		$this->add_action ('wp_ajax_et-save-language', 'save_language');
		/**
		 * request for load translation form, return a form of string to translate
		*/
		$this->add_action ('wp_ajax_et-load-translation-form', 'load_translation_form');
		// add_action('after_setup_theme', array ( 'AE_Language' ,'load_text_domain' ) );
		//ae-language-change

		$this->create_content_directory();
	}

	/**
	 * save site language to option
	 * @since 1.0
	*/
	public function set_site_language($lang_name) {
		$this->option->website_language = $lang_name;
		$this->option->save();
	}
	
	/** 
	 * add new lang to host, base on request lang_name
	 * send an json back to client
	 * @since 1.0
	*/
	function add_new_lang () {
	
		$lang_name	=	str_replace(' ', '-',$_POST['lang_name']);
		$lang_arr	=	$this->get_language_list();
		// validate language name
		if( $lang_name== "" || in_array($lang_name, $lang_arr)) {
			$return =	array (
				'success'	=> false,
				'msg'		=> __("Invalid file name!",ET_DOMAIN)
			);

		} else {
			if( $this->generate_mo($lang_name) ) {
				// update site language options
				
				$this->set_site_language( $lang_name );

				$return =	array (
					'success'	=> true,
					'msg'		=> __("Adding new language successfully.",ET_DOMAIN),
					'data' 		=> array( 'lang_name'	=> $lang_name,
									'ID'		=> $lang_name
								)
				);
			}
		}
		// wp send json function
		wp_send_json($return);
	}

	/**
	 *	create a directory et-content in wp-content to store engine themes content
	 *	@param : array sub directory path
	 *  @return : void
	  * @since 1.0
	*/
	public  function create_content_directory () {

		if(!is_dir(WP_CONTENT_DIR.'/et-content')) {
			mkdir(WP_CONTENT_DIR.'/et-content', 0755);
			$fh = fopen(WP_CONTENT_DIR.'/et-content/index.html', 'w');
		}

		if(!is_dir(WP_CONTENT_DIR.'/et-content/'.THEME_NAME )) {
			mkdir( WP_CONTENT_DIR.'/et-content/'. THEME_NAME , 0755);
			$fh = fopen(WP_CONTENT_DIR.'/et-content/'. THEME_NAME.'/index.html', 'w');
		}

		if(!is_dir(WP_CONTENT_DIR.'/et-content/'.THEME_NAME.'/lang/' )) {
			mkdir( WP_CONTENT_DIR.'/et-content/'. THEME_NAME .'/lang/' , 0755);
			$fh = fopen(WP_CONTENT_DIR.'/et-content/'. THEME_NAME.'/lang/index.html', 'w');
		}
	}

	

	/**
	 * get wp_includes dir
	 * return string : wp_includes dir 
	 * @since 1.0
	 */
	function get_includes_dir () {
	    return $this->_wp_include_dir;
	}
	
	/**
	 * list all language mo file in theme
	 * @since 1.0
	*/
	public function get_language_list () {
		/**
		 * use wp function get_available_languages to get language list
		*/
		if( defined('THEME_LANGUAGE_PATH') )
			$custom_langs	=	get_available_languages(THEME_LANGUAGE_PATH);
		else 
			$custom_langs	=	array();

		$default_langs	=	get_available_languages(DEFAULT_LANGUAGE_PATH);
		foreach ($default_langs as $key => $value) {
			if(!in_array($value, $custom_langs))
				$custom_langs[]	=	$value;
		}

		$this->_language_list =  $custom_langs;
		return $this->_language_list;
	}


	/**
	 * generate a po file, deprecated
	 * @param string $file_name : file name;
	 * @since 1.0
	 */
	function generate_pot () {
		// check enginethem.po exist or not
		$b	=	glob(DEFAULT_LANGUAGE_PATH.$this->_pot_name );
		
		if(!empty($b)) {
			return false;
		}
		
		// $file_name	=	$this->_pot_name;
		// $makePOT	=	new MakePOT();
		// $makePOT->xgettext('wp-theme', TEMPLATEPATH, DEFAULT_LANGUAGE_PATH.'/'.$file_name);
			
		
		// $pot		=	$this->_pot ;
		// $pot->import_from_file(DEFAULT_LANGUAGE_PATH.$file_name);
		
		// // set file header
		// $pot->set_header( 'Project-Id-Version', 'Job Engine v'.CE_VERSION);
		// $pot->set_header( 'Report-Msgid-Bugs-To', ET_URL );
		// $pot->set_header( 'POT-Creation-Date', gmdate( 'Y-m-d H:i:s+00:00' ) );
		// $pot->set_header( 'MIME-Version', '1.0' );
		// $pot->set_header( 'Content-Type', 'text/plain; charset=UTF-8' );
		// $pot->set_header( 'Content-Transfer-Encoding', '8bit' );
		// $pot->set_header( 'PO-Revision-Date', '2010-MO-DA HO:MI+ZONE' );
		// $pot->set_header( 'Last-Translator', 'Engine Themes <contact@enginethemes.com>' );
		// $pot->set_header( 'Language-Team', 'Engine Themes <contact@enginethemes.com>' );
		// $pot->set_header('Plural-Forms', 'nplurals=2; plural=n == 1 ? 0 : 1');
		
		// $pot->export_to_file(DEFAULT_LANGUAGE_PATH.$file_name,true );
		// return true;	

	}
	/**
	 * generate mo file 
	 * @param string $file_name
	 * @since 1.0
	 */
	function generate_mo ( $file_name ) {
		$this->_mo_name	=	$file_name .'.mo';
		// check file exist or not
		$b	=	glob(DEFAULT_LANGUAGE_PATH.$this->_mo_name);
		
		if(!empty($b)) {
			return false;
		}
		
		$this->generate_pot();
		
		$mo	=	$this->_mo;
		
		$mo->set_header( 'Project-Id-Version', THEME_NAME . 'v'.ET_VERSION );
		$mo->set_header( 'Report-Msgid-Bugs-To', ET_URL );
		$mo->set_header( 'MO-Creation-Date', gmdate( 'Y-m-d H:i:s+00:00' ) );
		$mo->set_header( 'MIME-Version', '1.0' );
		$mo->set_header( 'Content-Type', 'text/plain; charset=UTF-8' );
		$mo->set_header( 'Content-Transfer-Encoding', '8bit' );
		$mo->set_header( 'MO-Revision-Date', '2010-MO-DA HO:MI+ZONE' );
		$mo->set_header( 'Last-Translator', 'Engine Themes <contact@enginethemes.com>' );
		$mo->set_header( 'Language-Team', 'Engine Themes <contact@enginethemes.com>' );
		
		$mo->export_to_file(THEME_LANGUAGE_PATH.'/'.$file_name.'.mo',true );
		return true;	
		
	}

	/**
	 * generate translate string from engine.po
	 * @since 1.0
	*/
	function get_translate_string () {
		$this->_pot		=	new PO();
		$this->generate_pot();
		$this->_pot->import_from_file(DEFAULT_LANGUAGE_PATH.'/engine.po',true );
		return apply_filters( 'et_get_translate_string', $this->_pot->entries);
	}


	/**
	 * static function to load text domain, 
	 * it should be call by add_action ('after_setup_theme', array ('AE_Language', 'load_text_domain'));
	 * @since 1.0
	*/
	public static function load_text_domain () {
		//load mo file and localize 
		$options		=	AE_Options::get_instance();
		$selected_lang	=	$options->website_language;

		if( in_array($selected_lang, get_available_languages(THEME_LANGUAGE_PATH)) )
			load_textdomain(ET_DOMAIN, THEME_LANGUAGE_PATH."/$selected_lang.mo");
		else 
			load_textdomain(ET_DOMAIN, DEFAULT_LANGUAGE_PATH."/$selected_lang.mo");
	}

	/** 
	 * update language translate, catch request from ajax update translate string to po and mo file
	 * @since 1.0
	*/
	function save_language () {
		
		$selected_lang	=	$_POST['lang_name'];
		$langArr		=	$this->get_language_list(THEME_LANGUAGE_PATH);
		//file name invalid
		if(	$selected_lang == '' 	 || $selected_lang == null 
			||  $selected_lang == 'null' || !in_array($selected_lang, $langArr))  {
			wp_send_json(
				array (
					'success'	=>	false,
					'msg'		=> 	__("Invalid file name!",ET_DOMAIN)
				)
			);
			exit;
		}
		
		$singular		=	isset($_POST['singular'])  ? $_POST['singular'] : array();
		$translation	=	isset($_POST['translations']) ? $_POST['translations'] : array();
		$context		=	isset($_POST['context']) ? $_POST['context'] : array ();
		
		if(empty($singular) || empty($translation) || empty($context)) {
			wp_send_json(
			array (
				'success'	=>	true,
				'msg'		=> 	__("There was no changes in your translation.",ET_DOMAIN)
			));
			
		}


		$mo 			=	new MO();
		$po				=	new PO();

		$language_files	=	array ('mo' =>$mo , 'po' => $po);
		
		foreach ($language_files as $type => $mo) {
			
			$mo->set_header( 'Project-Id-Version', THEME_NAME . 'v'.ET_VERSION );
			$mo->set_header( 'Report-Msgid-Bugs-To', ET_URL );
			$mo->set_header( 'MO-Creation-Date', gmdate( 'Y-m-d H:i:s+00:00' ) );
			$mo->set_header( 'MIME-Version', '1.0' );
			$mo->set_header( 'Content-Type', 'text/plain; charset=UTF-8' );
			$mo->set_header( 'Content-Transfer-Encoding', '8bit' );
			$mo->set_header( 'MO-Revision-Date', '2010-MO-DA HO:MI+ZONE' );
			$mo->set_header( 'Last-Translator', 'JOB <EMAIL@ADDRESS>' );
			$mo->set_header( 'Language-Team', 'ENGINETHEMES.COM <enginethemes@enginethemes.com>' );

			// import language file from et_content/lang if exist
			if($type == 'mo') { // mo file
				if( in_array( $selected_lang, get_available_languages(THEME_LANGUAGE_PATH) ) )
					$mo->import_from_file(THEME_LANGUAGE_PATH.'/'.$selected_lang.'.mo');
				else 
					$mo->import_from_file(DEFAULT_LANGUAGE_PATH.'/'.$selected_lang.'.mo');	
			}else { // po file
				if(file_exists( THEME_LANGUAGE_PATH.'/'.$selected_lang.'.po' )) {
					$mo->import_from_file( THEME_LANGUAGE_PATH.'/'.$selected_lang.'.po' );
				}else {
					$mo->import_from_file( DEFAULT_LANGUAGE_PATH.'/engine.po' );	
				}				
			}

			foreach ( $singular as $key => $value) {
				
				// if( $translation[$key] == "" && $type == 'mo' ) {
				// 	if(isset( $mo->entries[$value] ))
				// 		unset($mo->entries[$value]);	
				// 	continue;
				// }
				
				if( $context[$key] != '' ) {
					$mo->add_entry(new Translation_Entry( 
										array (	
											'singular' => trim ( stripcslashes($value),''), 
											'context'  => trim (stripcslashes($context[$key]),''),	
											'translations' => array('0'=> trim((stripcslashes($translation[$key])), '') 
										)
									)
								)
						);
				}else {
					$mo->add_entry(new Translation_Entry( 
										array (	
											'singular' => trim ( stripcslashes($value),''),	
											'translations' => array('0'=> trim((stripcslashes( $translation[$key]) ), '') 
										)
									)
								)
						);
				} 
					
			}

			$mo->export_to_file(THEME_LANGUAGE_PATH.'/'.$selected_lang.'.'.$type);
		}
		
		wp_send_json(
			array (
				'success'	=>	true,
				'msg'		=> 	__("Translation saved! ",ET_DOMAIN)
		));
		
	}
	

	/**
	 * ajax function load translation form and send a html form back to client
	 * @since 1.0
	 */
	function load_translation_form () {
		$lang_name	=	$_POST['lang_name'];
		$pot		=	new PO();
		/*et_generate_pot();
		$pot->import_from_file(DEFAULT_LANGUAGE_PATH.'/engine.po',true );
		*/
		$translated	=	array ();	
		$mo			=	new MO();
		
		$langArr	=	$this->get_language_list();

		if( in_array( $lang_name, $langArr ) ){
			if( in_array( $lang_name, get_available_languages(THEME_LANGUAGE_PATH) ) )
				$mo->import_from_file(THEME_LANGUAGE_PATH.'/'.$lang_name.'.mo');
			else 
				$mo->import_from_file(DEFAULT_LANGUAGE_PATH.'/'.$lang_name.'.mo');

			$translated	=	$mo->entries;
		} 

		$trans_arr	=	$this->get_translate_string ();

		$data		=	'';
		$i 			=	0;

		foreach ($trans_arr as $key =>  $value ) {
			

			if(isset($translated[$key])) continue;
			if($value->context != '') continue;
			
			$singular			=	htmlentities(stripcslashes( $value->singular ),ENT_COMPAT, "UTF-8" );
			if( empty($value->translations)) {
				$translate_txt	=	'';//$singular;
			} else {
				$translate_txt	=	htmlentities(stripcslashes( $value->translations[0] ),ENT_COMPAT, "UTF-8" );
			}

			if( $i == 0 ) {
				$data .= '<div class="slide" >';
			}
			

			$data	.=	
				'<div class="form-item">
					<div class="label">'. $singular. '</div>
					<input type="hidden" value="'.$singular.'" name="singular[]">
					<input type="hidden" value="'.$value->context.'" name="context[]">
					<textarea type="text"  name="translations[]" class="autosize" row="1" style="height: auto;overflow: visible;"
						placeholder="'. __("Type the translation in your language",ET_DOMAIN).'" >'.$translate_txt.'</textarea>
				</div>';

			$i++;

			if($i == 11 ) {
				$data	.=	'</div>';
				$i = 0;
			}

		}

		foreach ($translated as $key =>  $value ) {
			$singular			=	htmlentities(stripcslashes( $value->singular ),ENT_COMPAT, "UTF-8" );
			if( empty($value->translations)) {
				$translate_txt	=	'';//$singular;
			} else {
				$translate_txt	=	htmlentities(stripcslashes( $value->translations[0] ),ENT_COMPAT, "UTF-8" );
			}
			
			if($i == 0 ) {
				$data .= '<div class="slide" >';
			}
			

			$data	.=	'
				<div class="form-item">
					<div class="label">'. $singular. '</div>
					<input type="hidden" value="'.$singular.'" name="singular[]">
					<input type="hidden" value="'.$value->context.'" name="context[]">
					<textarea  type="text"  name="translations[]" class="autosize" row="1" style="height: auto;overflow: visible;"
					placeholder="'. __("Type the translation in your language",ET_DOMAIN).'" >'.$translate_txt.'</textarea>
				</div>';

			$i++;

			if($i == 11 ) {
				$data	.=	'</div>';
				$i = 0;
			}

		}
		$return 	=	array (
			'success'	=> true,
			'data'		=> $data,
			'msg'		=> __("Loading successfully!",ET_DOMAIN),
			'tran_arr'	=> $trans_arr
		);
		
		wp_send_json($return);
		
	}

	/**
	 * change language : if language file not exist return false
	 * if language file not in THEME_LANGUAGE_PATH copy it from DEFAULT_LANG to THEME_LANGUAGE_PATH 
	 * @since 1.0
	*/
	function change_language ( ) {
		$lang	=	$_REQUEST['lang_name'];
		
		if( !in_array( $lang, $this->get_language_list() ) ) {
			wp_send_json( array('success' => false) );
		}

		if(!in_array( $lang, get_available_languages(THEME_LANGUAGE_PATH)) ) {
			$mo 			=	 new MO ();
		
			$mo->set_header( 'Project-Id-Version', THEME_NAME . 'v'.ET_VERSION  );
			$mo->set_header( 'Report-Msgid-Bugs-To', ET_URL );
			$mo->set_header( 'MO-Creation-Date', gmdate( 'Y-m-d H:i:s+00:00' ) );
			$mo->set_header( 'MIME-Version', '1.0' );
			$mo->set_header( 'Content-Type', 'text/plain; charset=UTF-8' );
			$mo->set_header( 'Content-Transfer-Encoding', '8bit' );
			$mo->set_header( 'MO-Revision-Date', '2010-MO-DA HO:MI+ZONE' );
			$mo->set_header( 'Last-Translator', 'JOB <EMAIL@ADDRESS>' );
			$mo->set_header( 'Language-Team', 'ENGINETHEMES.COM <enginethemes@enginethemes.com>' );

			$mo->import_from_file(DEFAULT_LANGUAGE_PATH.'/'.$lang.'.mo');

			$mo->export_to_file(THEME_LANGUAGE_PATH.'/'.$lang.'.mo') ;
		}

		$this->set_site_language( $lang );

		wp_send_json( array('success' => true, 'data' => array('ID' => $lang, 'lang_name' => $lang )) );
	}

}