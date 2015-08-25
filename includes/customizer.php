<?php
/**
 * Contains methods for customizing the theme customization screen.
 *
 * @link http://codex.wordpress.org/Theme_Customization_API
 * @since MyTheme 1.0
 */
class QA_Customize {
	 /**
		* This hooks into 'customize_register' (available as of WP 3.4) and allows
		* you to add new sections and controls to the Theme Customize screen.
		*
		* Note: To enable instant preview, we have to actually write a bit of custom
		* javascript. See live_preview() for more.
		*
		* @see add_action('customize_register',$func)
		* @param \WP_Customize_Manager $wp_customize
		* @link http://ottopress.com/2012/how-to-leverage-the-theme-customizer-in-your-own-themes/
		* @since MyTheme 1.0
		*/
	 public static function register ( $wp_customize ) {
			//1. Define a new section (if desired) to the Theme Customizer
			$wp_customize->add_section( 'qa_customizer_options',
				 array(
						'title' => __( 'QA Options', ET_DOMAIN ),
						'priority' => 35,
						'capability' => 'edit_theme_options',
						'description' => __('Allows you to customize some example settings for QAEngine.', ET_DOMAIN), //Descriptive tooltip
				 )
			);

			//2. Register new settings to the WP database...
			$wp_customize->add_setting( 'link_textcolor',
				 array(
						'default' => '#15a4fa',
						'type' => 'theme_mod',
						'capability' => 'edit_theme_options',
						'transport' => 'postMessage',
				 )
			);

			$wp_customize->add_setting( 'main_action_color',
				 array(
						'default' => '#3498db',
						'type' => 'theme_mod',
						'capability' => 'edit_theme_options',
						'transport' => 'postMessage',
				 )
			);

			$wp_customize->add_setting( 'sidebar_color',
				 array(
						'default' => '#eef1f7',
						'type' => 'theme_mod',
						'capability' => 'edit_theme_options',
						'transport' => 'postMessage',
				 )
			);

			$wp_customize->add_setting( 'header_color',
				 array(
						'default' => '#2f364a',
						'type' => 'theme_mod',
						'capability' => 'edit_theme_options',
						'transport' => 'postMessage',
				 )
			);

			$wp_customize->add_setting( 'header_menus_color',
				 array(
						'default' => '#19202e',
						'type' => 'theme_mod',
						'capability' => 'edit_theme_options',
						'transport' => 'postMessage',
				 )
			);

			//3. Finally, we define the control itself (which links a setting to a section and renders the HTML controls)...
			$wp_customize->add_control( new WP_Customize_Color_Control(
				$wp_customize,
				'link_textcolor',
				array(
					'label' => __( 'Link Color', ET_DOMAIN ),
					'section' => 'colors',
					'settings' => 'link_textcolor',
					'priority' => 10,
				)
			) );

			$wp_customize->add_control( new WP_Customize_Color_Control(
				$wp_customize,
				'main_action_color',
				array(
					'label' => __( 'Main Action Color', ET_DOMAIN ),
					'section' => 'colors',
					'settings' => 'main_action_color',
					'priority' => 10,
				)
			) );

			$wp_customize->add_control( new WP_Customize_Color_Control(
				$wp_customize,
				'sidebar_color',
				array(
					'label' => __( 'Sidebar Background Color', ET_DOMAIN ),
					'section' => 'colors',
					'settings' => 'sidebar_color',
					'priority' => 10,
				)
			) );

			$wp_customize->add_control( new WP_Customize_Color_Control(
				$wp_customize,
				'header_color',
				array(
					'label' => __( 'Header Background Color', ET_DOMAIN ),
					'section' => 'colors',
					'settings' => 'header_color',
					'priority' => 10,
				)
			) );

			$wp_customize->add_control( new WP_Customize_Color_Control(
				$wp_customize,
				'header_menus_color',
				array(
					'label' => __( 'Header Menus Background Color', ET_DOMAIN ),
					'section' => 'colors',
					'settings' => 'header_menus_color',
					'priority' => 10,
				)
			) );

			//4. We can also change built-in settings by modifying properties. For instance, let's make some stuff use live preview JS...
			$wp_customize->get_setting( 'blogname' )->transport = 'postMessage';
			$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';
			$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';
			$wp_customize->get_setting( 'background_color' )->transport = 'postMessage';
	 }

	 /**
		* This will output the custom WordPress settings to the live theme's WP head.
		*
		* Used by hook: 'wp_head'
		*
		* @see add_action('wp_head',$func)
		* @since MyTheme 1.0
		*/
	 public static function header_output() {
	 	if(et_load_mobile()){
		?>
			<!--Customizer CSS-->
			<style type="text/css">
				<?php self::generate_css('.post-question-btn, .list-pagination-wrapper ul li span.current, .list-pagination-wrapper ul li a:hover, .post-answers-wrapper .btn-post-answers, .vote-wrapper .number-vote, .form-post-answers input[type="submit"], .form-post-answers a.close-form-post-answers, .group-btn-post .submit-post-question', 'background-color', 'main_action_color'); ?>
				<?php self::generate_css('.menu-push .list-categories li a', 'color', 'link_textcolor'); ?>
				<?php self::generate_css('header, .menu-push', 'background-color', 'header_color') ?>
				<?php self::generate_css('.right-sidebar', 'background-color', 'sidebar_color') ?>
				<?php self::generate_css('.header-menu', 'background-color', 'header_menus_color') ?>
			</style>
			<!--/Customizer CSS-->
		<?php
		} else {
		?>
			<!--Customizer CSS-->
			<style type="text/css">
				<?php self::generate_css('.paginations ul li a:hover, .paginations ul li span.current, .submit-wrapper button, .ask-question, .modal-submit-questions .btn-submit-question, .question-main-content .vote-block span, #upload_images .button-event button', 'background-color', 'main_action_color'); ?>
				<?php self::generate_css('.q-right-content .question-control ul li a.show-comments.active, a.add-comment, a.hide-comment, span.back a, .term-texts a, .widget a,.question-category a,.copyright a,.widget-menus ul li a', 'color', 'link_textcolor'); ?>
				<?php self::generate_css('#header,ul.dropdown-profile', 'background-color', 'header_color') ?>
				<?php self::generate_css('.right-sidebar', 'background-color', 'sidebar_color') ?>
				<?php self::generate_css('.header-menu', 'background-color', 'header_menus_color') ?>
			</style>
			<!--/Customizer CSS-->
		<?php
		}
	 }

	 /**
		* This outputs the javascript needed to automate the live settings preview.
		* Also keep in mind that this function isn't necessary unless your settings
		* are using 'transport'=>'postMessage' instead of the default 'transport'
		* => 'refresh'
		*
		* Used by hook: 'customize_preview_init'
		*
		* @see add_action('customize_preview_init',$func)
		* @since MyTheme 1.0
		*/
	 public static function live_preview() {
			wp_enqueue_script(
					 'qa-themecustomizer', // Give the script a unique ID
					 get_template_directory_uri() . '/js/customizer.js', // Define the path to the JS file
					 array(  'jquery', 'customize-preview' ), // Define dependencies
					 '', // Define a version (optional)
					 true // Specify whether to put in footer (leave this true)
			);
	 }

		/**
		 * This will generate a line of CSS for use in header output. If the setting
		 * ($mod_name) has no defined value, the CSS will not be output.
		 *
		 * @uses get_theme_mod()
		 * @param string $selector CSS selector
		 * @param string $style The name of the CSS *property* to modify
		 * @param string $mod_name The name of the 'theme_mod' option to fetch
		 * @param string $prefix Optional. Anything that needs to be output before the CSS property
		 * @param string $postfix Optional. Anything that needs to be output after the CSS property
		 * @param bool $echo Optional. Whether to print directly to the page (default: true).
		 * @return string Returns a single line of CSS with selectors and a property.
		 * @since MyTheme 1.0
		 */
		public static function generate_css( $selector, $style, $mod_name, $prefix='', $postfix='', $echo=true ) {
			$return = '';
			$mod = get_theme_mod($mod_name);
			if ( ! empty( $mod ) ) {
				 $return = sprintf('%s { %s:%s ; }',
						$selector,
						$style,
						$prefix.$mod.$postfix
				 );
				 if ( $echo ) {
						echo $return;
				 }
			}
			return $return;
		}
}

//Setup the Theme Customizer settings and controls...
add_action( 'customize_register' , array( 'QA_Customize' , 'register' ) );

// Output custom CSS to live site
add_action( 'wp_footer' , array( 'QA_Customize' , 'header_output' ) );

// Enqueue live preview javascript in Theme Customizer admin screen
add_action( 'customize_preview_init' , array( 'QA_Customize' , 'live_preview' ) );