/**
 * @file toobasic_asset.js
 * @author Alejandro Dario Simi
 */
if (window.jQuery) {
	$(document).ready(function () {
		$.fn.tooBasicReload = function () {
			var uri = $(this).attr('data-toobasic-insert');
			if (uri) {
				console.log("TooBasic::ajaxInsert(): loading '" + uri + "'");
				$(this).attr('data-toobasic-status', 'loading');

				var mthis = $(this);
				$.get(uri, function (data) {
					mthis.html(data);
					mthis.attr('data-toobasic-status', 'loaded');
				});
			}
		};

		$('div[data-toobasic-insert]').tooBasicReload();
	});
} else {
	console.log("'toobasic_asset.js' requires jQuery to be included before it.");
}