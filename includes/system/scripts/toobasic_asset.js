/**
 * @file toobasic_asset.js
 * @author Alejandro Dario Simi
 */
//
// Checking if jQuery was previously included.
if (window.jQuery) {
	//
	// Adding a new metod to reload ajax insertion.
	$.fn.tooBasicReload = function() {
		//
		// Obtaining target URL.
		var uri = $(this).attr('data-toobasic-insert');
		//
		// If there's no URL, this is not an ajax insertion '<div/>'.
		if (uri) {
			//
			// Log information of what is going to happen.
			console.log("TooBasic::ajaxInsert(): loading '" + uri + "'");
			//
			// Flagging tag as 'loading'.
			$(this).attr('data-toobasic-status', 'loading');
			//
			// Temporary pointer.
			var mthis = $(this);
			//
			// Requesting the data to insert.
			$.get(uri, function(data) {
				//
				// The actual data insertion.
				mthis.html(data);
				//
				// Flagging tag as 'loaded'.
				mthis.attr('data-toobasic-status', 'loaded');
			});
		}
		//
		// Returning the element for further operations.
		return $(this);
	};
	//
	// Triggering ajax insertions after the document is fully loaded.
	$(document).ready(function() {
		$('div[data-toobasic-insert]').tooBasicReload();
	});
} else {
	//
	// Prompting an error when jQuery is not present.
	console.log("'toobasic_asset.js' requires jQuery to be included before it.");
}
