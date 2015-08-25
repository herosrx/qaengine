<?php
class AE_switch {

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
        $args   =   $this->field;
        if( !$this->value ) 
            echo '<div class="inner no-border btn-left">
                    <div class="payment"> 
                        <div class="button-enable font-quicksand switch">
                            <a href="javascript:void(0)" rel="'. $args['name'] .'" title="" class="toggle-button deactive selected">
                                <span>Disable</span>
                            </a>
                            <a href="javascript:void(0)" rel="'. $args['name'] .'" title="" class="toggle-button active ">
                                <span>Enable</span>
                            </a>
                            <input type="hidden" name="'. $args['name'] .'" value="0" />
                        </div>
                    </div>
                </div>';
        else echo '<div class="inner no-border btn-left">
                    <div class="payment"> 
                        <div class="button-enable font-quicksand switch">
                            <a href="javascript:void(0)" rel="'. $args['name'] .'" title="" class="toggle-button deactive ">
                                <span>Disable</span>
                            </a>
                            <a href="javascript:void(0)" rel="'. $args['name'] .'" title="" class="toggle-button active selected ">
                                <span>Enable</span>
                            </a>
                            <input type="hidden" name="'. $args['name'] .'" value="0" />
                        </div>
                    </div>
                </div>';

    }//render

}
