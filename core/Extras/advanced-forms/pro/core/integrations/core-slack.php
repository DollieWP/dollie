<?php
	
class AF_Pro_Core_Slack {
	
	function __construct() {
		
		add_action( 'af/form/submission', array( $this, 'send_slack_notification' ), 15, 3 );
		
		add_filter( 'af/form/valid_form', array( $this, 'valid_form' ), 10, 1 );
		add_filter( 'af/form/from_post', array( $this, 'form_from_post' ), 10, 2 );
		
	}
	
	
	function send_slack_notification( $form, $fields, $args ) {
		
		if ( $form['slack'] ) {
			
			$payload = array(
				'username' => $form['title'],
				'text' => $form['slack']['message'],
			);
			
			// Add form fields to message payload
			if ( ! empty( $form['slack']['fields'] ) ) {
				$payload['attachments'] = array();
				
				$attachment = array( 'fields' => array() );	
				
				foreach ( $form['slack']['fields'] as $field_key ) {
					$field = af_get_field_object( $field_key );
					
					$attachment['fields'][] = array(
						'title' => $field['label'],
						'value' => _af_render_field_include( $field ),
						'short' => true,
					);
				}
				
				$payload['attachments'][] = $attachment;
			}

			// Construct and filter request
      $request = array(
        'body' => array(
					'payload' => json_encode( $payload ),
				),
      );

      $request = apply_filters( 'af/form/slack/request', $request, $form, $args );
      $request = apply_filters( 'af/form/slack/request/id=' . $form['post_id'], $request, $form, $args );
      $request = apply_filters( 'af/form/slack/request/key=' . $form['key'], $request, $form, $args );
			
			// Perform API request
			wp_remote_post( $form['slack']['webhook_url'], $request );
			
		}
		
	}
	
	
	/**
	 * Add the slack field to the default valid form
	 *
	 * @since 1.4.0
	 *
	 */
	function valid_form( $form ) {
		
		$form['slack'] = false;
		
		return $form;
		
	}
	
	
	/**
	 * Add any email settings to form object for forms loaded from posts
	 *
	 * @since 1.4.0
	 *
	 */
	function form_from_post( $form, $post ) {
		
		$slack_enabled = get_field( 'form_slack', $post->ID );
	
		if ( $slack_enabled ) {
	
			$form['slack'] = array(
				'webhook_url' => get_field( 'form_slack_webhook', $post->ID ),
				'message' => get_field( 'form_slack_message', $post->ID ),
				'fields' => get_field( 'form_slack_fields', $post->ID ),
			);
			
		}
		
		return $form;
		
	}
	
}

return new AF_Pro_Core_Slack();