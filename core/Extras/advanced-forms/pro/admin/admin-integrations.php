<?php
	
class AF_Pro_Admin_Integrations {
	
	
	function __construct() {

		add_filter( 'acf/load_field/name=form_slack_fields', array( $this, 'populate_slack_field_mapping_choices' ), 10, 1 );
		add_filter( 'acf/load_field/name=form_mailchimp_list', array( $this, 'populate_mailchimp_list_choices' ), 10, 1 );

		add_action( 'acf/render_field/type=url', array( $this, 'add_zapier_test_button' ), 10, 1 );
		add_action( 'wp_ajax_test_zapier_submission', array( $this, 'ajax_test_zapier_submission' ), 10, 0 );
		
		add_filter( 'af/form/settings_fields', array( $this, 'add_form_settings_fields' ), 10, 1 );
		
		add_filter( 'af/settings_fields', array( $this, 'add_general_settings_fields' ), 10, 1 );

	}
	
	
	function populate_slack_field_mapping_choices( $field ) {
		
		global $post;
		
		if ( $post && 'af_form' == $post->post_type ) {
			
			$form_key = get_post_meta( $post->ID, 'form_key', true );
			
			$field['choices'] = _af_form_field_choices( $form_key, 'regular' );
			
		}
		
		return $field;
		
	}
	
	
	function populate_mailchimp_list_choices( $field ) {
		
		$lists = _af_mailchimp_lists();
		
		if ( $lists ) {
			
			foreach ( $lists as $list ) {
				$field['choices'][ $list['id'] ] = $list['name'];
			}
			
		}

		return $field;
		
	}


	function add_zapier_test_button( $field ) {
		
		if ( $field['key'] != 'field_form_zapier_webhook' ) {
			return;
		}

		global $post;

		if ( $post ) {
			$form = af_form_from_post( $post );

			echo sprintf( '<button class="zapier-test-button button floating" data-form-key="%s" data-text="%2$s">%2$s</button>', $form['key'], __( 'Send test submission', 'advanced-forms' ) );
		}

	}


	function ajax_test_zapier_submission() {

		if ( ! isset( $_POST['form_key'] ) || ! isset( $_POST['webhook_url'] ) ) {
			return;
		}

		$webhook_url = $_POST['webhook_url'];

		$form_key = $_POST['form_key'];
		$fields = af_get_form_fields( $form_key );

		$values = array();

		// Construct a mock request with each field either having its default value or empty string
		foreach ( $fields as $field ) {
			$values[ $field['name'] ] = $field['default_value'] ?: "";
		}

		wp_remote_post( $webhook_url, array(
      'body' => $values,  
    ));

		wp_die();

	}
	
	
	function add_form_settings_fields( $field_group ) {
		
		$field_group['fields'][] = array(
			'key' => 'field_form_integrations_tab',
			'label' => '<span class="dashicons dashicons-external"></span>Integrations',
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


		// Slack
		$field_group['fields'][] = array(
			'key' => 'field_form_slack',
			'label' => sprintf( '<img src="%s" />Slack', AF()->url . 'pro/assets/images/slack_logo.png' ),
			'name' => 'form_slack',
			'type' => 'true_false',
			'instructions' => 'Receive your form submissions as Slack messages.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'message' => '',
			'default_value' => 0,
			'ui' => 1,
			'ui_on_text' => 'Enabled',
			'ui_off_text' => 'Disabled',
		);
		
		$field_group['fields'][] = array (
			'key' => 'field_form_slack_webhook',
			'label' => 'Webhook URL',
			'name' => 'form_slack_webhook',
			'type' => 'url',
			'instructions' => 'Follow our <a href="https://advancedforms.github.io/pro/configuration/setting-up-slack-notifications/">simple guide</a> to get a webhook for your account.',
			'required' => 1,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_form_slack',
						'operator' => '==',
						'value' => '1',
					),
				),
			),
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		);
		
		$field_group['fields'][] = array (
			'key' => 'field_form_slack_message',
			'label' => 'Message',
			'name' => 'form_slack_message',
			'type' => 'textarea',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_form_slack',
						'operator' => '==',
						'value' => '1',
					),
				),
			),
			'wrapper' => array (
				'width' => '50',
				'class' => '',
				'id' => '',
			),
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'rows' => 4,
			'maxlength' => '',
		);
		
		$field_group['fields'][] = array (
			'key' => 'field_form_slack_fields',
			'label' => 'Fields to include',
			'name' => 'form_slack_fields',
			'type' => 'checkbox',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_form_slack',
						'operator' => '==',
						'value' => '1',
					),
				),
			),
			'wrapper' => array (
				'width' => '50',
				'class' => '',
				'id' => '',
			),
			'choices' => array (
			),
			'allow_custom' => 0,
			'save_custom' => 0,
			'default_value' => array (
			),
			'layout' => 'vertical',
			'toggle' => 1,
			'return_format' => 'value',
		);

		$field_group['fields'][] = array(
			'key' => 'field_form_slack_divider',
			'name' => 'Slack divider',
			'type' => 'divider',
		);

		
		// Mailchimp
		$field_group['fields'][] = array(
			'key' => 'field_form_mailchimp',
			'label' => sprintf( '<img src="%s" />Mailchimp', AF()->url . 'pro/assets/images/mailchimp_logo.png' ),
			'name' => 'form_mailchimp',
			'type' => 'true_false',
			'instructions' => 'Use your form to sign people up for campaigns.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'message' => '',
			'default_value' => 0,
			'ui' => 1,
			'ui_on_text' => 'Enabled',
			'ui_off_text' => 'Disabled',
		);
		
		if ( _af_mailchimp_api_key() ) {

			$field_group['fields'][] = array(
				'key' => 'field_form_mailchimp_list',
				'label' => 'List',
				'name' => 'form_mailchimp_list',
				'type' => 'select',
				'instructions' => '',
				'required' => 1,
				'conditional_logic' => array(
					array (
						array (
							'field' => 'field_form_mailchimp',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'choices' => array(),
				'wrapper' => array (
					'width' => '50',
					'class' => '',
					'id' => '',
				),
				'message' => '',
				'default_value' => 0,
			);
			
			$field_group['fields'][] = array(
				'key' => 'field_form_mailchimp_email_field',
				'label' => 'Email field',
				'name' => 'form_mailchimp_email_field',
				'type' => 'field_picker',
				'instructions' => '',
				'required' => 1,
				'conditional_logic' => array(
					array (
						array (
							'field' => 'field_form_mailchimp',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array (
					'width' => '50',
					'class' => '',
					'id' => '',
				),
				'allow_null' => true,
				'message' => '',
				'default_value' => 0,
			);
			
			$field_group['fields'][] = array(
				'key' => 'field_form_mailchimp_first_name',
				'label' => 'First name',
				'name' => 'form_mailchimp_first_name',
				'type' => 'field_picker',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array (
						array (
							'field' => 'field_form_mailchimp',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array (
					'width' => '50',
					'class' => '',
					'id' => '',
				),
				'allow_null' => true,
				'allow_custom' => true,
				'placeholder' => 'None',
				'default_value' => 0,
			);
			
			$field_group['fields'][] = array(
				'key' => 'field_form_mailchimp_last_name',
				'label' => 'Last name',
				'name' => 'form_mailchimp_last_name',
				'type' => 'field_picker',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array (
						array (
							'field' => 'field_form_mailchimp',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
				'wrapper' => array (
					'width' => '50',
					'class' => '',
					'id' => '',
				),
				'allow_null' => true,
				'allow_custom' => true,
				'placeholder' => 'None',
				'default_value' => 0,
			);

		} else {

			$field_group['fields'][] = array(
				'key' => 'field_form_mailchimp_missing_key',
				'label' => 'Missing API key',
				'name' => 'form_mailchimp_missing_key',
				'type' => 'message',
				'message' => 'Go to Forms &rarr; Settings to enter a Mailchimp API key. Read the doucumentation to <a href="https://advancedforms.github.io/pro/configuration/configuring-mailchimp/">learn where to get one</a>.',
				'required' => 0,
				'conditional_logic' => array(
					array (
						array (
							'field' => 'field_form_mailchimp',
							'operator' => '==',
							'value' => '1',
						),
					),
				),
			);

		}

		$field_group['fields'][] = array(
			'key' => 'field_form_mailchimp_divider',
			'name' => 'Mailchimp divider',
			'type' => 'divider',
		);
		
		
		// Zapier
		$field_group['fields'][] = array(
			'key' => 'field_form_integrations_zapier',
			'label' => sprintf( '<img src="%s" />Zapier', AF()->url . 'pro/assets/images/zapier_logo.png' ),
			'name' => 'form_integrations_zapier',
			'type' => 'true_false',
			'instructions' => 'Create zaps triggered by a submission and connect your form to hundreds of services.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'message' => '',
			'default_value' => 0,
			'ui' => 1,
			'ui_on_text' => 'Enabled',
			'ui_off_text' => 'Disabled',
		);

		$field_group['fields'][] = array(
			'key' => 'field_form_zapier_webhook',
			'label' => 'Webhook URL',
			'name' => 'form_zapier_webhook',
			'type' => 'url',
			'instructions' => 'Read our <a href="https://advancedforms.github.io/pro/configuration/setting-up-slack-notifications/">guide</a> on how to connect Zapier.',
			'required' => 1,
			'conditional_logic' => array(
				array (
					array (
						'field' => 'field_form_integrations_zapier',
						'operator' => '==',
						'value' => '1',
					),
				),
			),
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'placeholder' => 'Check guide for instructions'
		);
		
		
		return $field_group;
		
	}
	
	
	function add_general_settings_fields( $field_group ) {
		
		$field_group['fields'][] = array (
			'key' => 'field_af_mailchimp_api_key',
			'label' => 'Mailchimp API Key',
			'name' => 'af_mailchimp_api_key',
			'type' => 'text',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'hide_admin' => 0,
			'default_value' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'maxlength' => '',
		);
		
		return $field_group;
		
	}
	
}

return new AF_Pro_Admin_Integrations();