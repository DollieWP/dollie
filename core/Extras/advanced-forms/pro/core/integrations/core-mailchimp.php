<?php
	
class AF_Pro_Core_Mailchimp {
	
	function __construct() {
		
		add_action( 'af/form/submission', array( $this, 'add_subscriber_to_list' ), 10, 3 );
		
		add_filter( 'af/form/valid_form', array( $this, 'valid_form' ), 10, 1 );
		add_filter( 'af/form/from_post', array( $this, 'form_from_post' ), 10, 2 );
		
	}
	
	
	function add_subscriber_to_list( $form, $fields, $args ) {
		
		if ( $form['mailchimp'] && $form['mailchimp']['list'] ) {
			
			$list_id = $form['mailchimp']['list'];
			$api_key = _af_mailchimp_api_key();
			$data_center = _af_mailchimp_data_center();
			
			if ( ! $api_key || ! $data_center ) {
				return;
			}
			
			
			$endpoint = sprintf( 'https://%s.api.mailchimp.com/3.0/lists/%s/members', $data_center, $list_id );
			$auth_header = 'Basic ' . base64_encode( sprintf( 'mailchimp:%s', $api_key ) );
			
			
			$email = af_get_field( $form['mailchimp']['email']['field'] );
			
			$body = array(
				'email_address' => $email,
				'status' => 'subscribed',
				'merge_fields' => array(),
			);
			
			// First name
			if ( 'custom' == $form['mailchimp']['first_name']['field'] ) {
				$body['merge_fields']['FNAME'] = af_resolve_merge_tags( $form['mailchimp']['first_name']['format'] );
			} else if ( $form['mailchimp']['first_name']['field'] ) {
				$body['merge_fields']['FNAME'] = af_get_field( $form['mailchimp']['first_name']['field'] );
			}
			
			// Last name
			if ( 'custom' == $form['mailchimp']['last_name']['field'] ) {
				$body['merge_fields']['LNAME'] = af_resolve_merge_tags( $form['mailchimp']['last_name']['format'] );
			} else if ( $form['mailchimp']['last_name']['field'] ) {
				$body['merge_fields']['LNAME'] = af_get_field( $form['mailchimp']['last_name']['field'] );
			}

			// Construct and filter request
			$request = array(
				'headers' => array(
					'Authorization' => $auth_header,
				),
				'body' => json_encode( $body ),
			);

			$request = apply_filters( 'af/form/mailchimp/request', $request, $form, $args );
			$request = apply_filters( 'af/form/mailchimp/request/id=' . $form['post_id'], $request, $form, $args );
			$request = apply_filters( 'af/form/mailchimp/request/key=' . $form['key'], $request, $form, $args );
			
			// Perform API request
			wp_remote_post( $endpoint, $request );
			
		}
		
	}
	
	
	/**
	 * Add the slack field to the default valid form
	 *
	 * @since 1.4.0
	 *
	 */
	function valid_form( $form ) {
		
		$form['mailchimp'] = false;
		
		return $form;
		
	}
	
	
	/**
	 * Add any email settings to form object for forms loaded from posts
	 *
	 * @since 1.4.0
	 *
	 */
	function form_from_post( $form, $post ) {
		
		$mailchimp_enabled = get_field( 'form_mailchimp', $post->ID );
	
		if ( $mailchimp_enabled ) {
	
			$form['mailchimp'] = array(
				'list' => get_field( 'form_mailchimp_list', $post->ID ),
				'email' => get_field( 'form_mailchimp_email_field', $post->ID ),
				'first_name' => get_field( 'form_mailchimp_first_name', $post->ID ),
				'last_name' => get_field( 'form_mailchimp_last_name', $post->ID ),
			);
			
		}
		
		return $form;
		
	}
	
}

return new AF_Pro_Core_Mailchimp();