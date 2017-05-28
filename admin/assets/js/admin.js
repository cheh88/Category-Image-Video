( function ( $ ) {
	'use strict';

	$( function() {
		var frame,
			addImageBtn    = $( '#cvi-image-add' ),
			removeImageBtn = $( '#cvi-image-remove' ),
			imgContainer   = $( '#cvi-image-container' ),
			imgPlaceholder = $( '.cvi-img-comtainer__placeholder', imgContainer ),
			imgIdInput     = $( '#cvi-image' ),
			removeVideoBtn = $( '#cvi-video-remove' ),
			videoInput     = $( '#cvi-video' ),
			videoContainer = $( '#cvi-video-container' );

		addImageBtn.on( 'click', imageAddHandler );
		removeImageBtn.on( 'click', imageRemoveHandler );
		removeVideoBtn.on( 'click', videoRemoveHandler );

		function imageAddHandler( event ) {
			event.preventDefault();

			// If the media frame already exists, reopen it.
			if ( frame ) {
				frame.open();
				return;
			}

			// Create a new media frame.
			frame = wp.media( {
				title: cvi.media_frame_title,
				button: {
					text: cvi.media_frame_btn
				},
				multiple: false
			} );


			// When an image is selected in the media frame...
			frame.on( 'select', function() {

				// Get media attachment details from the frame state
				var attachment = frame.state().get( 'selection' ).first().toJSON();

				// Send the attachment URL to our custom image input field.
				imgContainer.append( '<img src="' + attachment.sizes.thumbnail.url + '" alt="">' );

				// Hide the placeholder.
				imgPlaceholder.addClass( 'hidden' );

				// Send the attachment id to hidden input.
				imgIdInput.val( attachment.id );

				// Hide the add image button.
				addImageBtn.addClass( 'hidden' );

				// Show the remove image button.
				removeImageBtn.removeClass( 'hidden' );
			} );

			// Finally, open the modal on click
			frame.open();
		}

		function imageRemoveHandler( event ){
			event.preventDefault();

			// Clear out the preview image.
			imgContainer.find( 'img' ).remove();

			// Show the placeholder.
			imgPlaceholder.removeClass( 'hidden' );

			// Show the add image button.
			addImageBtn.removeClass( 'hidden' );

			// Hide the delete image button.
			removeImageBtn.addClass( 'hidden' );

			// Delete the image id from the hidden input.
			imgIdInput.val( '' );
		}

		function videoRemoveHandler( event ) {
			videoInput.val( '' );
			videoContainer.find( 'img' ).remove();
		}
	} );

}( jQuery ) );