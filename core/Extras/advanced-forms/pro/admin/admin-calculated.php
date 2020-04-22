<?php
  
  
class AF_Pro_Admin_Calculated {
  
  function __construct() {
    add_filter( 'af/form/settings_fields', array( $this, 'add_form_settings_fields' ), 10, 1 );
  }
  
  function add_form_settings_fields( $field_group ) {
    
    $field_group['fields'][] = array(
      'key' => 'field_form_calculated_tab',
      'label' => '<span class="dashicons dashicons-hammer"></span>Calculated',
      'name' => '',
      'type' => 'tab',
      'instructions' => '',
      'required' => 0,
      'conditional_logic' => 0,
      'wrapper' => array (
        'width' => '',
        'class' => '',
        'id' => '',
      ),
      'placement' => 'left',
      'endpoint' => 0,
    );

    $field_group['fields'][] = array(
      'key' => 'field_form_calculated_fields',
      'label' => 'Calculated fields',
      'name' => 'form_calculated_fields',
      'type' => 'calculated_admin',
      'instructions' => '',
      'required' => 0,
      'conditional_logic' => 0,
      'wrapper' => array (
        'width' => '',
        'class' => '',
        'id' => '',
      ),
      'placement' => 'left',
      'endpoint' => 0,
    );
    
    return $field_group;
    
  }
  
}

return new AF_Pro_Admin_Calculated();