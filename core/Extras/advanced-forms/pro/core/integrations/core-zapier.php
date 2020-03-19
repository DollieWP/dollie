<?php

class AF_Pro_Zapier {

  function __construct() {

    add_action( 'af/form/submission', array( $this, 'trigger_zap' ), 15, 3 );

    add_filter( 'af/form/valid_form', array( $this, 'valid_form' ), 10, 1 );
    add_filter( 'af/form/from_post', array( $this, 'form_from_post' ), 10, 2 );

  }


  function trigger_zap( $form, $fields, $args ) {
    
    if ( $form['zapier'] ) {

      $values = array();

      foreach ( af_get_form_fields( $form['key'], 'all' ) as $field ) {
        $values[ $field['name'] ] = af_get_field( $field['name'] );
      }

      // Construct and filter request
      $request = array(
        'body' => $values,
      );

      $request = apply_filters( 'af/form/zapier/request', $request, $form, $args );
      $request = apply_filters( 'af/form/zapier/request/id=' . $form['post_id'], $request, $form, $args );
      $request = apply_filters( 'af/form/zapier/request/key=' . $form['key'], $request, $form, $args );

      // Perform API request
      wp_remote_post( $form['zapier']['webhook_url'], $request );
      
    }
    
  }


  /**
   * Add the zapier field to the default valid form
   *
   * @since 1.4.0
   *
   */
  function valid_form( $form ) {
    
    $form['zapier'] = false;
    
    return $form;
    
  }


  /**
   * Add any email settings to form object for forms loaded from posts
   *
   * @since 1.4.0
   *
   */
  function form_from_post( $form, $post ) {
    
    $zapier_enabled = get_field( 'form_integrations_zapier', $post->ID );
  
    if ( $zapier_enabled ) {
  
      $form['zapier'] = array(
        'webhook_url' => get_field( 'form_zapier_webhook', $post->ID ),
      );
      
    }
    
    return $form;
    
  }

}

return new AF_Pro_Zapier();