<?php
	
class AF_Pro_Core_Editing {
	
	function __construct() {
		
		add_action( 'af/form/submission', array( $this, 'handle_editing_form' ), 10, 3 );
		add_action( 'af/form/args', array( $this, 'evaluate_current' ), 10, 2 );
		
		add_action( 'af/field/prefill_value', array( $this, 'prefill_fields' ), 10, 4 );
		add_action( 'af/form/hidden_fields', array( $this, 'add_post_id' ), 10, 2 );

		add_filter( 'af/merge_tags/custom', array( $this, 'add_custom_merge_tags' ), 10, 2 );
		add_filter( 'af/merge_tags/resolve', array( $this, 'resolve_merge_tag' ), 10, 2 );
		
		add_filter( 'af/form/valid_form', array( $this, 'valid_form' ), 10, 1 );
		add_filter( 'af/form/from_post', array( $this, 'form_from_post' ), 10, 2 );
		
	}
	
	
	/**
	 * Handle form editing on submit
	 *
	 * @since 1.4.0
	 *
	 */
	function handle_editing_form( $form, $fields, $args ) {

		if ( $form['editing']['post'] && isset( $args['post'] ) ) {
			
			$this->handle_post_edit( $form, $args['post'], $args );
			
		}
		
		
		if ( $form['editing']['user'] && isset( $args['user'] ) ) {
			
			$this->handle_user_edit( $form, $args['user'], $args );
			
		}
		
	}


	/**
	 * Evaluate current post or user when post or user argument is set to "current"
	 *
	 * @since 1.6.0
	 *
	 */
	function evaluate_current( $args, $form ) {
		// Default to "new" if no post argument is set
		if ( $form['editing']['post'] && ! isset( $args['post'] ) ) {
			$args['post'] = 'new';
		}

		// Add current post ID if post argument is "current"
		if ( $form['editing']['post'] && isset( $args['post'] ) && 'current' == $args['post'] ) {
			if ( $current_post_id = get_the_ID() ) {
				$args['post'] = $current_post_id;
			}
		}

		// Add current user ID if user argument is "current"
		if ( $form['editing']['user'] && isset( $args['user'] ) && 'current' == $args['user'] ) {
			if ( $current_user_id = get_current_user_id() ) {
				$args['user'] = $current_user_id;
			}
		}

		return $args;
	}
	
	
	/**
	 * Handle form editing of post, either create or edit
	 *
	 * @since 1.4.0
	 *
	 */
	function handle_post_edit( $form, $post_id, $args ) {

		if ( 'new' != $post_id && ! is_numeric( $post_id ) ) {
			return false;
		}
		
		$post_data = array(
			'post_type' => $form['editing']['post']['post_type'],
		);
		
		
		// Get post title from fields
		$post_title = _af_resolve_field_picker_value( $form['editing']['post']['post_title'] );
		if ( false !== $post_title ) {
			$post_data['post_title'] = $post_title;
		}
		
		// Get post content from fields
		$post_content = _af_resolve_field_picker_value( $form['editing']['post']['post_content'] );
		if ( false !== $post_content ) {
			$post_data['post_content'] = $post_content;
		}
		
		
		if ( 'new' == $post_id ) {
		
			// Either title, content, or excerpt must be non-empty.
			// Hence content defaults to " ".
			$post_data = wp_parse_args( $post_data, array(
				'post_content' => ' ',
				'post_status' => 'publish',
			));
			
		} else {
			
			$post_data = wp_parse_args( $post_data, array(
				'ID' => $post_id, 
				'post_status' => 'publish',
			));
			
		}
		
		
		// Filter post data before insert/update
		$post_data = apply_filters( 'af/form/editing/post_data', $post_data, $form, $args );
		$post_data = apply_filters( 'af/form/editing/post_data/id=' . $form['post_id'], $post_data, $form, $args );
		$post_data = apply_filters( 'af/form/editing/post_data/key='. $form['key'], $post_data, $form, $args );
		
		
		if ( 'new' == $post_id ) {
			$updated_post_id = wp_insert_post( $post_data, true );
		} else {
			$updated_post_id = wp_update_post( $post_data, true );
		}
		
		if ( ! $updated_post_id || is_wp_error( $updated_post_id ) ) {
			return false;
		}
		
		
		$post = get_post( $updated_post_id );
		
		if ( ! $post || is_null( $post ) ) {
			return false;
		}
		
		
		// Transfer custom fields
		if ( $form['editing']['post']['custom_fields'] ) {
			
			foreach ( $form['editing']['post']['custom_fields'] as $field_key ) {
				
				af_save_field( $field_key, $post->ID );
					
			}
			
		}


		// Save post ID to submission object
		AF()->submission['post'] = $post->ID;
		
		
		// Trigger action after post has been created/updated
		$action = ( 'new' == $post_id ) ? 'af/form/editing/post_created' : 'af/form/editing/post_updated';
		
		do_action( $action, $post, $form, $args );
		do_action( $action . '/id=' . $form['post_id'], $post, $form, $args );
		do_action( $action . '/key=' . $form['key'], $post, $form, $args );
		
		
		return true;
		
	}
	
	
	function handle_user_edit( $form, $user_id, $args ) {
		
		if ( 'new' != $user_id && ! is_numeric( $user_id ) ) {
			return false;
		}
		
		
		$user_data = array();


		// Get email from fields
		if ( $email = _af_resolve_field_picker_value( $form['editing']['user']['email'] ) ) {
			
			$user_data['user_email'] = $email;
			
		}
		
		// Get first name from fields
		if ( $first_name = _af_resolve_field_picker_value( $form['editing']['user']['first_name'] ) ) {
			
			$user_data['first_name'] = $first_name;
			
		}
		
		// Get last name from fields
		if ( $last_name = _af_resolve_field_picker_value( $form['editing']['user']['last_name'] ) ) {
			
			$user_data['last_name'] = $last_name;
			
		}

		
		if ( 'new' == $user_id ) {

			// Get username from fields
			if ( 'same_as_email' == $form['editing']['user']['username']['field'] ) {
				
				$user_data['user_login'] = $email;
				
			} else if ( $username = _af_resolve_field_picker_value( $form['editing']['user']['username'] ) ) {
				
				$user_data['user_login'] = $username;
				
			}

			// Generate password or get from fields
			if ( 'generate' == $form['editing']['user']['password']['field'] ) {
				
				$user_data['user_pass'] = wp_generate_password();
				
			} else if ( $password = _af_resolve_field_picker_value( $form['editing']['user']['password'] ) ) {
				
				$user_data['user_pass'] = $password;
				
			}
		
			$user_data = wp_parse_args( $user_data, array(
				'role' => $form['editing']['user']['role'],
				'user_pass' => wp_generate_password(),
			));
			
		} else {
			
			$user_data['ID'] = $user_id;
			
		}

		
		// Filter user data before insert/update
		$user_data = apply_filters( 'af/form/editing/user_data', $user_data, $form, $args );
		$user_data = apply_filters( 'af/form/editing/user_data/id=' . $form['post_id'], $user_data, $form, $args );
		$user_data = apply_filters( 'af/form/editing/user_data/key='. $form['key'], $user_data, $form, $args );
		
		if ( 'new' == $user_id ) {
			$updated_user_id = wp_insert_user( $user_data );
		} else {
			$updated_user_id = wp_update_user( $user_data );
		}
		
		if ( ! $updated_user_id || is_wp_error( $updated_user_id ) ) {
			return false;
		}


		$user = get_user_by( 'id', $updated_user_id );
		
		if ( ! $user || is_null( $user ) ) {
			return false;
		}


		// Transfer custom fields
		if ( $form['editing']['user']['custom_fields'] ) {
			
			foreach ( $form['editing']['user']['custom_fields'] as $field_key ) {
				
				af_save_field( $field_key, 'user_' . $user->ID );
					
			}
			
		}
		
		
		if ( $form['editing']['user']['send_notification'] && 'new' == $user_id ) {
			
			wp_new_user_notification( $user->ID, null, 'both' );
			
		}


		// Save user ID to submission object
		AF()->submission['user'] = $user->ID;


		// Trigger action after post has been created/updated
		$action = ( 'new' == $user_id ) ? 'af/form/editing/user_created' : 'af/form/editing/user_updated';
		
		do_action( $action, $user, $form, $args );
		do_action( $action . '/id=' . $form['post_id'], $user, $form, $args );
		do_action( $action . '/key=' . $form['key'], $user, $form, $args );


		return true;
		
	}
	
	
	/**
	 * Prefill form fields when editing a post or user
	 *
	 * @since 1.4.0
	 *
	 */
	function prefill_fields( $value, $field, $form, $args ) {
		
		// Check if form edits a post
		if ( $form['editing']['post'] && isset( $args['post'] ) && is_numeric( $args['post'] ) ) {
			$post = get_post( $args['post'] );

			if ( ! $post ) {
				return $value;
			}
			
			// Post title
			if ( $form['editing']['post']['post_title']['field'] == $field['key'] ) {
				return $post->post_title;
			}
			
			// Post content
			if ( $form['editing']['post']['post_content']['field'] == $field['key'] ) {
				return $post->post_content;
			}
			
			
			// Field is mapped to itself
			if ( in_array( $field['key'], $form['editing']['post']['custom_fields'] ) ) {
				return acf_get_value( $args['post'], $field );
			}
			
		}

		// Check if form edits a user
		if ( $form['editing']['user'] && isset( $args['user'] ) && is_numeric( $args['user'] ) ) {
			$user = get_user_by( 'id', $args['user'] );

			if ( ! $user ) {
				return $value;
			}			

			// Email
			if ( $form['editing']['user']['email']['field'] == $field['key'] ) {
				return $user->user_email;
			}

			// First name
			if ( $form['editing']['user']['first_name']['field'] == $field['key'] ) {
				return $user->user_firstname;
			}

			// Last name
			if ( $form['editing']['user']['last_name']['field'] == $field['key'] ) {
				return $user->user_lastname;
			}
			
			
			// Field is mapped to itself
			if ( in_array( $field['key'], $form['editing']['user']['custom_fields'] ) ) {
				return acf_get_value( 'user_' . $args['user'], $field );
			}
			
		}
		
		
		return $value;
		
	}


	/**
	 * Add post ID to hidden field if editing a post. Used for ACF AJAX requests.
	 *
	 * @since 1.6.0
	 *
	 */
	function add_post_id( $form, $args ) {
		if ( $form['editing']['post'] && isset( $args['post'] ) && is_numeric( $args['post'] ) ) {
			echo sprintf( '<input type="hidden" name="post_id" value="%s">', $args['post'] );
		}
	}


	/**
	 * Add merge tags for post and user ID.
	 *
	 * @since 1.6.0
	 *
	 */
	function add_custom_merge_tags( $tags, $form ) {
		if ( $form['editing']['post'] ) {
			$tags[] = array(
				'value' => 'post_id',
				'label' => __( 'Post ID', 'advanced-forms' ),
			);

			$tags[] = array(
				'value' => 'post_url',
				'label' => __( 'Post URL', 'advanced-forms' ),
			);
		}

		return $tags;
	}


	/**
	 * Resolve custom merge tags for posts and users.
	 *
	 * @since 1.6.0
	 *
	 */
	function resolve_merge_tag( $output, $tag ) {
		if ( 'post_id' == $tag ) {
			if ( isset( AF()->submission['post'] ) ) {
				return AF()->submission['post'];
			}
		}

		if ( 'post_url' == $tag ) {
			if ( isset( AF()->submission['post'] ) ) {
				return get_permalink( AF()->submission['post'] );
			}	
		}

		return $output;
	}
	
	
	/**
	 * Add the editing fields to the default valid form
	 *
	 * @since 1.4.0
	 *
	 */
	function valid_form( $form ) {
		
		$form['editing'] = array(
			'user' => false,
			'post' => false,
			'term' => false,
		);
		
		return $form;
		
	}
	
	
	/**
	 * Add any editing settings to form object for forms loaded from posts
	 *
	 * @since 1.4.0
	 *
	 */
	function form_from_post( $form, $post ) {
		
		$form['editing'] = array(
			'user' => false,
			'post' => false,
			'term' => false,
		);
		
		
		$editing_type = get_field( 'form_editing_type', $post->ID );
	
		if ( 'post' == $editing_type ) {
	
			$form['editing']['post'] = array(
				'post_type' => get_field( 'form_editing_post_type', $post->ID ),
				'post_title' => get_field( 'form_editing_post_title', $post->ID ),
				'post_content' => get_field( 'form_editing_post_content', $post->ID ),
				'custom_fields' => get_field( 'form_editing_custom_fields', $post->ID ) ?: array(),
			);
			
		}
		
		
		if ( 'user' == $editing_type ) {
	
			$form['editing']['user'] = array(
				'role' => get_field( 'form_editing_user_role', $post->ID ),
				'email' => get_field( 'form_editing_user_email', $post->ID ),
				'username' => get_field( 'form_editing_user_username', $post->ID ),
				'first_name' => get_field( 'form_editing_user_first_name', $post->ID ),
				'last_name' => get_field( 'form_editing_user_last_name', $post->ID ),
				'password' => get_field( 'form_editing_user_password', $post->ID ),
				'send_notification' => get_field( 'form_editing_user_send_notification', $post->ID ),
				'custom_fields' => get_field( 'form_editing_custom_fields', $post->ID ) ?: array(),
			);
			
		}
		
		return $form;
		
	}
	
}

return new AF_Pro_Core_Editing();