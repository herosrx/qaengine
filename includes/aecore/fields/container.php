<?php
/**
 * Class AE_container
 * create a elements container , it can contain anything
 * @author Dakachi
*/
Class AE_container {
    /**
     * Field Constructor.
     *
     * @param array $params
     * - html tag 
     * - id
     * - name 
     * - class 
     * - title 
     * @param array $sections 
     * @param $parent
     * @since AEFramework 1.0.0
    */
    function __construct( $params = array(), $sections , $parent ) {

        //parent::__construct( $parent->sections, $parent->args );
        $this->parent = $parent;
        $this->field = $params;
        $this->sections = $sections;

    }

    /**
     * render container element
    */
    function render() {

        $sections   =   $this->sections;
    
        echo '<div class="et-main-content'. $this->field['class'] .'" id="'. $this->field['id'] .'" >' ;
        // render menu if have  more then 1 section
        if( is_array( $sections ) ) {
            /**
             * render section menus
            */
            echo '<div class="et-main-left"><ul class="et-menu-content inner-menu">';  
                $first = true;
                foreach ( $sections as $key => $section ) {
                    $section->render_menu( $first );
                    $first = false;
                }
            echo '</ul></div>';

            echo '<div class="settings-content">';
                $first = true;
                foreach ( $sections as $key => $section ) {
                    $section->render($first);
                    $first = false;
                }

            echo '</div>';

        } else {
            echo '<div class="et-main-main one-column">';
            $sections->render ();
            echo '</div>';
        }

        echo '</div>';
    }

}
