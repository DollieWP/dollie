<?php

if( ! class_exists('AF_Calculated_Admin') ) :

/**
 * Custom private ACF field used to define the contents of a forms calculated fields.
 *
 * @since 1.6.0
 *
 */
class AF_Calculated_Admin extends acf_field {
  
  
  /**
   * Set up field defaults
   *
   * @since 1.6.0
   *
   */
  function __construct() {
    
    // vars
    $this->name = 'calculated_admin';
    $this->label = _x('Calculated admin', 'noun', 'acf');
    $this->public = false;
    $this->defaults = array(
      'allow_null'  => 0,
      'allow_custom'  => 0,
      'field_types' => 'regular',
      'choices'   => array(),
      'default_value' => array(),
      'placeholder' => '',
    );
    
    
    // do not delete!
    parent::__construct();
      
  }
  
  
  /**
   * Render interface for field
   *
   * @since 1.6.0
   *
   */
  function render_field( $field ) {
    $calculated_fields = $this->get_calculated_fields();

    if ( empty( $calculated_fields ) ) {
      echo sprintf( '<p>%s</p>', __( 'There are no calculated fields assigned to this form. Add a calculated field with ACF and then come back here to configure its contents!', 'advanced-forms' ) );
    } else {
      echo '<div class="acf-fields -top -border">';

      foreach ( $calculated_fields as $calculated_field ) {
        $value = false;
        if ( isset( $field['value'][ $calculated_field['key'] ] ) ) {
          $value = $field['value'][ $calculated_field['key'] ];
        }

        $value_field = array(
          'key' => $calculated_field['key'],
          'label' => $calculated_field['label'],
          'name' => $calculated_field['name'],
          'type' => 'wysiwyg',
          'instructions' => '',
          'default_value' => '',
          'tabs' => 'all',
          'toolbar' => 'full',
          'media_upload' => 1,
          'delay' => 0,
          'prefix' => sprintf( 'acf[%s]', $field['key'] ),
          'value' => $value,
        );

        acf_render_field_wrap( $value_field );
      }

      echo '</div>';
    }
  }


  /**
   * Retrieve all calculated fields for the current form.
   *
   * @since 1.6.0
   *
   */
  function get_calculated_fields() {
    global $post;

    $calculated_fields = array();
    
    if ( $post && $form_key = get_post_meta( $post->ID, 'form_key', true ) ) {
      
      $fields = af_get_form_fields( $form_key );

      foreach ( $fields as $field ) {
        if ( 'calculated' == $field['type'] ) {
          $calculated_fields[] = $field;
        }
      }
      
    }

    return $calculated_fields;
  }


  /**
   * Format the individual values for the different fields.
   * Based on the formatting for WYSIWYG fields. 
   *
   * @since 1.6.0
   *
   */
  function format_value( $value, $post_id, $field ) {
    if( empty($value) ) {
      return $value;
    }
    
    foreach ( $value as $i=>$calculated_contents ) {
      $formatted_value = apply_filters( 'acf_the_content', $calculated_contents );
      $formatted_value = str_replace( ']]>', ']]&gt;', $formatted_value );
      $value[ $i ] = $formatted_value;
    }
  
    return $value;
  }
  
}


// initialize
acf_register_field_type( new AF_Calculated_Admin() );

endif; // class_exists check

?>