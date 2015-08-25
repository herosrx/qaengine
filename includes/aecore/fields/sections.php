<?php


/**
 * Class AE_Group
 * create a group of input element 
 * @author Dakachi
*/
Class AE_section {
	/**
     * Field Constructor.
     *
     * @param array $params 
     * - html tag
     * - id
     * - name 
     * - placeholder 
     * - readonly 
     * - class 
     * - title 
     * @param $groups 
     * @param $parent
     * @since AEFramework 1.0.0
    */
    function __construct( $params = array(), $groups , $parent ) {

        //parent::__construct( $parent->sections, $parent->args );
        $this->parent = $parent;
        $this->field = $params;
        $temp   =   array ();
        foreach ($groups as $key => $group) {
            $temp[] =   new AE_group ( $group['args'] , $group['fields'] , $parent );
        }
        $this->groups = $temp;

    }

    function render( $first  =   false) {
        $groups =   $this->groups;

        /**
         * show the first section
        */
        $display   =    '';
        if(!$first) {
            $display    =   'style="display:none"';
        }
        echo '<div '. $display .' class="et-main-main clearfix inner-content '. $this->field['class'] .'" id="'. $this->field['id'] .'" >';

        if(is_array($groups)) {
            /**
             * render group menus
            */
            foreach ( $groups as $key => $group ) {
                $group->render();
            }    

        } else {
            $groups->render ();
        }

        echo '</div>';
    }

    function render_menu ( $first =  false ) {
        $class= '';
        if($first) $class= 'active';

        if( isset( $this->field['title'] )) {

            echo '<li>
                <a href="#'. $this->field['id'] .'" menu-data="" class="'. $class .'">
                    <span class="icon" data-icon="'. $this->field['icon'] .'"></span>'. $this->field['title'] .
                '</a>
            </li>';
        }
    }

}


