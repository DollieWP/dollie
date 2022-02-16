( function( $ ){

	function reloadPage() {
		window.location.href = window.location.href;
	};

	function pauseUpgrade() {
		reloadPage();
	};

	function processNextItem() {
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				_ajax_nonce: DOLLIE_SETUPUpgrades.nonce,
				action: 'dollie_setup_handle_upgrade',
				upgrade: DOLLIE_SETUPUpgrades.upgrade
			},
			beforeSend: function() {
				$('#dollie_setup-upgrade-start')
					.text( DOLLIE_SETUPUpgrades.text.processing )
					.prop( 'disabled', true );
			},
			success: function( response ) {
				$(document).trigger( 'itemprocessed', [ response ] )

				if ( ! response.data.is_finished ) {
					processNextItem();
				} else {
					$('#dollie_setup-upgrade-start').text( DOLLIE_SETUPUpgrades.text.start );
				}
			},
			error: function( error ) {
				console.log( error );
			},
		});

	}

	$(document).on( 'itemprocessed', function( event, response ) {
		var data = response.data;
		var percentage = data.percentage;

		if ( data.name ) {
			$('#dollie_setup-upgrade-name').text( data.name );
		}

		$('.dollie_setup-upgrade-progress-bar-inner').css( 'width', percentage +'%' );
		$('#dollie_setup-upgrade-total').text( data.total_items );
		$('#dollie_setup-upgrade-processed').text( data.total_processed );
		$('#dollie_setup-upgrade-percentage').text( '(' +percentage+ '%)' );

		if ( data.is_finished ) {
			$('#dollie_setup-upgrade-start, #dollie_setup-upgrade-pause').prop( 'disabled', true );
		}
	} );

	$(document).on( 'click', '#dollie_setup-upgrade-start', processNextItem );
	$(document).on( 'click', '#dollie_setup-upgrade-pause', pauseUpgrade );
} )( jQuery );
