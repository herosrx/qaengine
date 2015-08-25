<?php
if(!defined('DEFAULT_LANGUAGE_PATH')) {
	define('DEFAULT_LANGUAGE_PATH', get_template_directory() .'/lang');
}
class AE_language_list {

    /**
     * Field Constructor.
     *
     * @param array $field
     * - id
     * - name
     * - placeholder
     * - readonly
     * - class
     * - title
     * @param $value
     * @param $parent
     * @since AEFramework 1.0.0
    */
    function __construct( $field = array(), $value ='', $parent ) {

        //parent::__construct( $parent->sections, $parent->args );
        $this->parent = $parent;
        $this->field = $field;
        $this->value = $value;

    }

  //   /**
  //    * get all available language list in theme
  //   */
  //   function get_language_list(){
  //   	if( defined('THEME_LANGUAGE_PATH') )
		// 	$custom_langs	=	get_available_languages(THEME_LANGUAGE_PATH);
		// else
		// 	$custom_langs	=	array();

		// $default_langs	=	get_available_languages(DEFAULT_LANGUAGE_PATH);

		// foreach ($default_langs as $key => $value) {
		// 	if(!in_array($value, $custom_langs))
		// 		$custom_langs[]	=	$value;
		// }
		// return $custom_langs;
  //   }

    /**
     * Field Render Function.
     *
     * Takes the vars and outputs the HTML for the field in the settings
     *
     * @since AEFramework 1.0.0
    */
    function render() {
        $language       =   AE_Language::get_instance();
    	$langArr		=	$language->get_language_list();
    	$selected_lang	=	$this->value;

    ?>
		<ul class="list-language">
    	<?php foreach ($langArr as $value) { ?>
        	<li>
        		<a class="<?php if($selected_lang == $value) echo "active"?>" title="<?php echo $value?>" href="#et-change-language" rel="<?php echo  $value ?>"><?php echo $value?> </a>
        	</li>
        <?php }?>
        	<li class="new-language">
        		<button class="add-lang"><?php _e('Add a new language', ET_DOMAIN) ?><span class="icon" data-icon="+"></span></button>
        		<div class="lang-field-wrap">
        			<input id="" type="text" placeholder="<?php _e("Enter language name", ET_DOMAIN) ?>" name="lang_name" class="input-new-lang">
        		</div>
        	</li>
        </ul>

		<div class="no-padding">
			<div class="show-new-language">
				<div class="item form no-background no-padding no-margin">
					<div class="form-item form-item-short">
						<!-- <div class="label"><?php _e("Language name", ET_DOMAIN)?>:</div> -->
						<input id="new-language-name" class="bg-grey-input" type="text" placeholder="<?php _e("Enter the language's name", ET_DOMAIN)?>" />
						<button id="add-new-language" ><?php _e('Add language', ET_DOMAIN) ?><span class="icon" data-icon="+"></span></button>
						<a class="cancel" id="cancel-add-lang"><?php _e('Cancel', ET_DOMAIN) ?></a>
					</div>
				</div>
			</div>
		</div>

    <?php
    }//render

}
