<?php

if( ! class_exists('AF_Calculated_Field') ) :

/**
 * Custom ACF field for calculated fields.
 *
 * @since 1.6.0
 *
 */
class AF_Calculated_Field extends acf_field {

  function initialize() {
    $this->name = 'calculated';
    $this->label = __( 'Calculated','advanced-forms' );
    $this->category = 'Forms';
    $this->defaults = array(
      'hide_admin' => true,
    );
  }

  function render_field( $field ) {
    echo acf_get_text_input(array(
      'type' => 'hidden',
      'class' => 'af-calculated-value',
      'name' => $field['name'],
    ));

    echo '<div class="af-calculated-content"></div>';
  }

  function update_value( $value, $post_id, $field ) {
    // Never save calculated value to database.
    return false;
  }
  
  function render_field_settings( $field ) {
    // Instructions
    acf_render_field_setting( $field, array(
      'label'     => __( 'Instructions', 'advanced-forms' ),
      'type'      => 'message',
      'name'      => 'instruction_message',
      'message' => __( 'After creating your calculated field here you need to define its contents. The contents can be edited in your form settings under the "Calculated" tab.', 'advanced-forms' ),
    ));
  }  
}


// initialize
acf_register_field_type( 'AF_Calculated_Field' );

endif;

?>