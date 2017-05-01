(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */
	 $(function() {
	 	var printButton = $('.recipe-pro-print');
	 	if (printButton.length) {
	 		printButton.on('click', function( event ){
		 		$(event.target).closest('.recipe-pro-recipe').printThis({removeInline:true});
		 	});
	 	}
	 	 $(function() {
	 	var printButton = $('.rp-source');
	 	if (printButton.length) {
	 		printButton.on('click', function( event ){
		 		$(event.target).closest('.rp-source').printThis({removeInline:true});
		 	});
	 	}
	 });
})( jQuery );
