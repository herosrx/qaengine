<?php
class AE_image {

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
        $uploaderID = $this->field['name'];
        $size       = $this->field['size'];
        
    ?>
        <div class="customization-info" id="<?php echo $this->field['name']; ?> ">
            <div class="input-file upload-logo" id="<?php echo $uploaderID; ?>_container" data-id="<?php echo $uploaderID; ?>" data-w="<?php echo $size[0] ?>" data-h="<?php echo $size[1] ?>" >
                <div class="left clearfix">
                    <div class="image" id="<?php echo $uploaderID;?>_thumbnail" style="<?php echo 'width:'. $size[0] .'px; height:'. $size[1] .'px; text-align:center;';  ?> ">
                        <img style="max-height: <?php echo $size[1] ?>px;" src="<?php echo $this->value['thumbnail'][0] ?>" />
                    </div>
                </div>
                
                <span class="et_ajaxnonce" id="<?php echo wp_create_nonce( $uploaderID . '_et_uploader' ); ?>"></span>
                <span class="bg-grey-button button btn-button" id="<?php echo $uploaderID;?>_browse_button" style="height:50px;margin-top:10px;">
                    <?php _e('Browse', ET_DOMAIN);?>
                    <span class="icon" data-icon="o"></span>
                </span>

            </div>
        </div>
        <div style="clear:left"></div>
    <?php

    }//render

}
