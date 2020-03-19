(function($) {

  af.calculated = {

    initialize: function( form ) {
      // Find all calculated fields and set them up
      $calculated_fields = form.$el.find( '.acf-field-calculated' );

      $calculated_fields.each(function( i, field ) {
        af.calculated.setupField( form, $(field) );
      })
    },

    setupField: function( form, $field ) {
      var self = this;

      // Perform an initial refresh to populate the field with empty data
      self.refreshField( form, $field );

      // Listen for form changes and refresh the field
      form.$el.change(function() {
        self.refreshField( form, $field );
      });
    },

    refreshField: function( form, $field ) {
      var self = this;

      // Prepare AJAX request with field key and serialized form data
      var key = $field.attr( 'data-key' );
      var data = acf.serialize( form.$el );

      data.action = 'af_calculated_field';
      data.calculated_field = key;

      data = acf.prepare_for_ajax( data );

      // Lock field to indicate loading
      self.lockField( $field );

      // Fetch updated field value through AJAX
      $.ajax({
        url: acf.get('ajaxurl'),
        data: data,
        type: 'post',
        success: function( data ){
          // Update field contents
          self.updateField( $field, data );
        },
        complete: function(){
          // Unlock field again once the request has finished (successfully or not)
          self.unlockField( $field );
        }
      });
    },

    updateField: function( $field, value ) {
      $field.find( '.af-input' ).html( value );
    },

    lockField: function( $field ) {
      $field.find( '.af-input' ).css( 'opacity', 0.5 );
    },

    unlockField: function( $field ) {
      $field.find( '.af-input' ).css( 'opacity', 1.0 );
    },

  };

  if ('addAction' in acf) {
    acf.addAction( 'af/form/setup', af.calculated.initialize );
  } else {
    acf.add_action( 'af/form/setup', af.calculated.initialize );
  }


  // Add post ID to ACF AJAX requests when editing a post
  af.addPostID = function( data ) {
    // Check if data has field key
    if ( ! data.hasOwnProperty( 'field_key' ) ) {
      return data;
    }

    // Find field with key
    var key = data.field_key;
    var $field = $('.af-field[data-key="' + key + '"]');
    if ( ! $field.length ) {
      return data;
    }

    var $post_id_input = $field.siblings( '.acf-hidden' ).find( 'input[name="post_id"]' );
    if ( $post_id_input.length ) {
      data.post_id = $post_id_input.val();
    }

    return data;
  };

  if ('addFilter' in acf) {
    acf.addFilter( 'prepare_for_ajax', af.addPostID );
  } else {
    acf.add_filter( 'prepare_for_ajax', af.addPostID );
  }

})(jQuery);