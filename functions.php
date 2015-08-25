<?php
define("ET_UPDATE_PATH",    "http://www.enginethemes.com/forums/?do=product-update");
define("ET_VERSION", '1.4');

if(!defined('ET_URL'))
 define('ET_URL', 'http://www.enginethemes.com/');

if(!defined('ET_CONTENT_DIR'))
 define('ET_CONTENT_DIR', WP_CONTENT_DIR.'/et-content/');

define( 'TEMPLATEURL', get_template_directory_uri() );
define( 'THEME_NAME' , 'qaengine');
define( 'ET_DOMAIN'  , 'enginetheme');

if(!defined('THEME_CONTENT_DIR ')) 	define('THEME_CONTENT_DIR', WP_CONTENT_DIR . '/et-content' . '/qaengine' );
if(!defined('THEME_CONTENT_URL'))	define('THEME_CONTENT_URL', content_url() . '/et-content' . '/qaengine' );

// theme language path
if(!defined('THEME_LANGUAGE_PATH') ) define('THEME_LANGUAGE_PATH', THEME_CONTENT_DIR.'/lang/');

if(!defined('ET_LANGUAGE_PATH') )
 define('ET_LANGUAGE_PATH', THEME_CONTENT_DIR . '/lang');

if(!defined('ET_CSS_PATH') )
 define('ET_CSS_PATH', THEME_CONTENT_DIR . '/css');

require_once TEMPLATEPATH.'/includes/index.php';
require_once TEMPLATEPATH.'/mobile/functions.php';

try {
	if ( is_admin() ){
		new QA_Admin();
	} else {
		new QA_Front();
	}
} catch (Exception $e) {
	echo $e->getMessage();
}
?>