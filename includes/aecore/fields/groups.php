<?php

/**
 * Class AE_Group
 * create a group of input element
 * @author Dakachi
 */
class AE_group
{
    
    /**
     * Field Constructor.
     *
     * @param array $params
     * - html tag
     * - id
     * - name
     * - class
     * - title
     * - desc
     * @param $field
     * @param $parent
     * @since AEFramework 1.0.0
     */
    function __construct($params = array() , $fields, $parent) {
        
        //parent::__construct( $parent->sections, $parent->args );
        $this->parent = $parent;
        $this->params = $params;
        
        $temp = array();
        
        $group_name = isset($this->params['name']) ? $this->params['name'] : '';
        
        // echo $group_name;
        
        foreach ($fields as $key => $field) {
            $type = 'AE_' . $field['type'];
            $name = $field['name'];
            
            if ($group_name == '') {
                $value = $parent->$name;
            } else {
                $value = $parent->$group_name;
                $value = (isset($value[$name])) ? $value[$name] : '';
            }
            
            $temp[] = new AE_BackendField(new $type($field, $value, $parent));
        }
        
        $this->fields = $temp;
        
        //$this->fields = $field;
        
        
    }
    
    /**
     * render group html
     * @author Dakachi
     */
    function render() {
        $fields = $this->fields;
        $group_name = isset($this->params['name']) ? 'data-name="' . $this->params['name'] . '"' : '';
        
        echo '<div class="' . $this->params['class'] . '" >';
        echo '<form ' . $group_name . '>';
        echo '<div class="title group-' . $this->params['id'] . '">' . $this->params['title'] . '</div>';
        
        /**
         * print group description
         */
        
        echo '<div class="desc">';
        if (isset($this->params['desc'])) echo $this->params['desc'];
        
        // render group field
        if (is_array($fields)) {
            
            /**
             * render group menus
             */
            foreach ($fields as $key => $field) {
                $field->render();
            }
        } else {
            $fields->render();
        }
        
        echo '</div>';
        echo '</form>';
        echo '</div>';
    }
}

/**
 * class adapt field to compatible with backend settings
 * @since 1.0
 * @author Dakachi
 */
class AE_BackendField
{
    public $field;
    function __construct($field) {
        $this->field = $field;
    }
    
    function render() {
        echo '<div class="form no-margin no-padding no-background"><div class="form-item">';
        $this->field->render();
        
        // echo '<span class="icon" data-icon="3"></span>';
        echo '</div></div>';
    }
}
