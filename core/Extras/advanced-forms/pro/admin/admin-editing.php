<?php
	
	
class AF_Pro_Admin_Editing {
	
	
	function __construct() {
		
		add_filter( 'af/form/settings_fields', array( $this, 'add_form_settings_fields' ), 10, 1 );
		
		add_filter( 'acf/load_field/name=form_editing_custom_fields', array( $this, 'populate_field_mapping_choices' ), 10, 1 );
		
		add_filter( 'acf/load_field/name=form_editing_user_role', array( $this, 'populate_field_user_roles' ), 10, 1 );
		add_filter( 'acf/load_field/name=form_editing_post_type', array( $this, 'populate_field_post_types' ), 10, 1 );
		add_filter( 'acf/load_field/name=form_editing_taxonomy', array( $this, 'populate_field_taxonomies' ), 10, 1 );
		
		add_action( 'acf/render_field/type=text', array( $this, 'add_field_inserter' ), 20, 1 );
		
	}
	
	
	function populate_field_mapping_choices( $field ) {
		
		global $post;
		
		if ( $post && 'af_form' == $post->post_type ) {
			
			$form_key = get_post_meta( $post->ID, 'form_key', true );
			
			
			$types = 'regular';
			
			if ( 'form_editing_custom_fields' == $field['name'] ) {
				$types = 'all';
			}
			
			$choices = _af_form_field_choices( $form_key, $types );
			
			if ( 'form_editing_custom_fields' == $field['name'] ) {
				$field['choices'] = array_merge( $field['choices'], $choices );
			} else {
				$field['choices']['Fields'] = $choices;
			}
			
		}
		
		return $field;
		
	}
	
	
	function populate_field_user_roles( $field ) {
		
		global $wp_roles;
		
		foreach ( $wp_roles->roles as $slug=>$role ) {
			
			$field['choices'][ $slug ] = $role['name'];
			
		}
		
		return $field;
		
	}
	
	
	function populate_field_post_types( $field ) {
		$post_types = acf_get_post_types(array(
			'show_ui'	=> 1, 
			'exclude'	=> array( 'attachment', 'af_form', 'af_entry' ),
		));

		foreach ( acf_get_pretty_post_types( $post_types ) as $post_type => $label ) {
			$field['choices'][ $post_type ] = $label;
		}
		
		return $field;
	}
	
	
	function populate_field_taxonomies( $field ) {
		
		$taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );
		
		foreach ( $taxonomies as $slug=>$taxonomy ) {
			
			$field['choices'][ $slug ] = $taxonomy->label;
			
		}
		
		return $field;
		
	}
	
	
	/**
	 * Add an "Insert field" button to post title, post content
	 *
	 * @since 1.4.0
	 *
	 */
	function add_field_inserter( $field ) {
		
		global $post;
		
		if ( ! $post ) {
			return;
		}
		
		
		$form = af_form_from_post( $post );
		
		if ( ! $form ) {
			return;
		}
		
		$fields_to_add = array(
			'field_form_editing_post_title_format',
			'field_form_editing_post_content',
		);
		
		
		if ( in_array( $field['key'], $fields_to_add ) ) {
			
			_af_field_inserter_button( $form, 'regular', true );
			
		}
		
	}
	
	
	function add_form_settings_fields( $field_group ) {
		
		$field_group['fields'][] = array(
			'key' => 'field_form_editing_tab',
			'label' => '<span class="dashicons dashicons-edit"></span>Editing',
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
		
		$field_group['fields'][] = array (
			'key' => 'field_form_editing_type',
			'label' => 'Use this form for creating/editing',
			'name' => 'form_editing_type',
			'type' => 'radio',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array (
				'width' => '50',
				'class' => '',
				'id' => '',
			),
			'choices' => array(
				'' => 'Nothing',
				'user' => 'Users',
				'post' => 'Posts',
			),
			'layout' => 'horizontal',
		);
		
		
		$field_group['fields'][] = array (
			'key' => 'field_form_editing_post_type',
			'label' => 'Post type',
			'name' => 'form_editing_post_type',
			'type' => 'select',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_form_editing_type',
						'operator' => '==',
						'value' => 'post',
					),
				),
			),
			'wrapper' => array (
				'width' => '100',
				'class' => '',
				'id' => '',
			),
			'choices' => array (
			),
			'default_value' => array (
			),
			'allow_null' => 0,
			'multiple' => 0,
			'ui' => 0,
			'ajax' => 0,
			'return_format' => 'value',
			'placeholder' => '',
		);
		
		
		$field_group['fields'][] = array (
			'key' => 'field_form_editing_user_role',
			'label' => 'User role',
			'name' => 'form_editing_user_role',
			'type' => 'select',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_form_editing_type',
						'operator' => '==',
						'value' => 'user',
					),
				),
			),
			'wrapper' => array (
				'width' => '100',
				'class' => '',
				'id' => '',
			),
			'choices' => array (
			),
			'default_value' => array (
				'subscriber'
			),
			'allow_null' => 0,
			'multiple' => 0,
			'ui' => 0,
			'ajax' => 0,
			'return_format' => 'value',
			'placeholder' => '',
		);
		
		
		$field_group['fields'][] = array (
			'key' => 'field_form_editing_taxonomy',
			'label' => 'Taxonomy',
			'name' => 'form_editing_taxonomy',
			'type' => 'select',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_form_editing_type',
						'operator' => '==',
						'value' => 'term',
					),
				),
			),
			'wrapper' => array (
				'width' => '100',
				'class' => '',
				'id' => '',
			),
			'choices' => array (
			),
			'default_value' => array (
				'category'
			),
			'allow_null' => 0,
			'multiple' => 0,
			'ui' => 0,
			'ajax' => 0,
			'return_format' => 'value',
			'placeholder' => '',
		);
		
		
		$field_group['fields'][] = array (
			'key' => 'field_field_mappings_message',
			'label' => 'Field mappings',
			'name' => '',
			'type' => 'message',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_form_editing_type',
						'operator' => '!=',
						'value' => '',
					),
				),
			),
			'wrapper' => array (
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'message' => 'Use the settings below to map title, content, and other data to your form fields',
			'new_lines' => 'wpautop',
			'esc_html' => 0,
		);
		
		
		/**
		 * Users
		 */
		$field_group['fields'][] = array (
			'key' => 'field_form_editing_user_email',
			'label' => 'Email',
			'name' => 'form_editing_user_email',
			'type' => 'field_picker',
			'instructions' => 'Must be a valid email address',
			'required' => 1,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_form_editing_type',
						'operator' => '==',
						'value' => 'user',
					),
				),
			),
			'wrapper' => array (
				'width' => '50',
				'class' => '',
				'id' => '',
			),
			'allow_null' => 1,
			'placeholder' => 'None',
			'allow_custom' => 1,
		);
		
		$field_group['fields'][] = array (
			'key' => 'field_form_editing_user_username',
			'label' => 'Username',
			'name' => 'form_editing_user_username',
			'type' => 'field_picker',
			'instructions' => 'Only used for new users',
			'required' => 1,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_form_editing_type',
						'operator' => '==',
						'value' => 'user',
					),
				),
			),
			'wrapper' => array (
				'width' => '50',
				'class' => '',
				'id' => '',
			),
			'choices' => array(
				'same_as_email' => 'Same as email',
			),
			'default_value' => array(
				'field' => 'same_as_email'
			),
			'allow_null' => 1,
			'placeholder' => 'None',
			'allow_custom' => 1,
		);
		
		$field_group['fields'][] = array (
			'key' => 'field_form_editing_user_first_name',
			'label' => 'First name',
			'name' => 'form_editing_user_first_name',
			'type' => 'field_picker',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_form_editing_type',
						'operator' => '==',
						'value' => 'user',
					),
				),
			),
			'wrapper' => array (
				'width' => '50',
				'class' => '',
				'id' => '',
			),
			'allow_null' => 1,
			'placeholder' => 'None',
			'allow_custom' => 1,
		);
		
		$field_group['fields'][] = array (
			'key' => 'field_form_editing_user_last_name',
			'label' => 'Last name',
			'name' => 'form_editing_user_last_name',
			'type' => 'field_picker',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_form_editing_type',
						'operator' => '==',
						'value' => 'user',
					),
				),
			),
			'wrapper' => array (
				'width' => '50',
				'class' => '',
				'id' => '',
			),
			'allow_null' => 1,
			'placeholder' => 'None',
			'allow_custom' => 1,
		);
		
		$field_group['fields'][] = array (
			'key' => 'field_form_editing_user_password',
			'label' => 'Password',
			'name' => 'form_editing_user_password',
			'type' => 'field_picker',
			'instructions' => 'Only used for new users',
			'required' => 0,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_form_editing_type',
						'operator' => '==',
						'value' => 'user',
					),
				),
			),
			'wrapper' => array (
				'width' => '50',
				'class' => '',
				'id' => '',
			),
			'choices' => array(
				'generate' => 'Auto-generate',
			),
			'default_value' => array(
				'field' => 'generate',
			),
			'allow_null' => 0,
			'allow_custom' => 1,
		);
		
		$field_group['fields'][] = array (
			'key' => 'field_form_editing_user_send_notification',
			'label' => 'Send notification',
			'name' => 'form_editing_user_send_notification',
			'type' => 'true_false',
			'instructions' => 'Send new users an email about their account',
			'required' => 0,
			'ui' => 1,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_form_editing_type',
						'operator' => '==',
						'value' => 'user',
					),
				),
			),
			'wrapper' => array (
				'width' => '50',
				'class' => '',
				'id' => '',
			),
		);
		
		
		/**
		 * Posts
		 */
		$field_group['fields'][] = array (
			'key' => 'field_form_editing_post_title',
			'label' => 'Post title',
			'name' => 'form_editing_post_title',
			'type' => 'field_picker',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_form_editing_type',
						'operator' => '==',
						'value' => 'post',
					),
				),
			),
			'wrapper' => array (
				'width' => '50',
				'class' => '',
				'id' => '',
			),
			'allow_null' => 1,
			'placeholder' => 'None',
			'allow_custom' => 1,
		);
		
		$field_group['fields'][] = array (
			'key' => 'field_form_editing_post_content',
			'label' => 'Post content',
			'name' => 'form_editing_post_content',
			'type' => 'field_picker',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_form_editing_type',
						'operator' => '==',
						'value' => 'post',
					),
				),
			),
			'wrapper' => array (
				'width' => '50',
				'class' => '',
				'id' => '',
			),
			'wrapper' => array (
				'width' => '50',
				'class' => '',
				'id' => '',
			),
			'allow_null' => 1,
			'placeholder' => 'None',
			'allow_custom' => 1,
		);
		
		
		/**
		 * Custom fields
		 */
		$field_group['fields'][] = array (
			'key' => 'field_form_editing_custom_fields',
			'label' => 'Custom fields',
			'name' => 'form_editing_custom_fields',
			'type' => 'checkbox',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array (
				array (
					array (
						'field' => 'field_form_editing_type',
						'operator' => '!=',
						'value' => '',
					),
				),
			),
			'wrapper' => array (
				'width' => '',
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
		
		return $field_group;
		
	}
	
}

return new AF_Pro_Admin_Editing();