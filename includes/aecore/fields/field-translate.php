<?php
/**
 * class AE_translator render lanaguage list and field to translate it, 
 * this class require class AE_Language to work
 * @author Dakachi
 * @version 1.0
*/
class AE_translator {

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

    /**
     * Field Render Function.
     *
     * Takes the vars and outputs the HTML for the field in the settings
     *
     * @since AEFramework 1.0.0
    */
    function render() {
        $language   =   AE_Language::get_instance();
        $langArr    =   $language->get_language_list();
    ?>
    	
		<div class="f-left-all width100p clearfix">
			<div class="" >
				<select id="base-language">
					<option class="empty" value=""><?php _e('Choose a Language', ET_DOMAIN) ?></option>
					<?php foreach ($langArr as $value) {?>
						<option value="<?php echo $value?>"><?php echo $value ?></option>
					<?php }?>
				</select>		
			</div>

			<div class="btn-language">
				<button id="save-language"><?php _e('Save', ET_DOMAIN) ?> <span class="icon" data-icon="~"></span></button>
			</div>
		</div>
        <p>
            <?php _e("The system will automatically save your translation after every 20 strings.", ET_DOMAIN); ?>
        </p>
		<div id="translate-form" style="height: 600px;overflow-y: scroll;margin-top: 30px;" >		        			

		</div>
		<div id="pager"></div>
				
    <?php
    }//render

}
