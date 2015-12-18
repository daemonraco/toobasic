$(document).ready(function () {
	$('a').not('[href^="http"]').each(function () {
		var href = $(this).attr('href');

		if (href) {
			if (href[0] !== "/" && href[0] !== "?") {
				$(this).attr('href', '?action=mddoc&doc=' + MD_DOC_URI + href);
			} else if (href[0] !== '?') {
				$(this).attr('href', '?action=mddoc&doc=' + href);
			}
		}
	});
	$('a[href^="https://"], a[href^="http://"]').each(function () {
		$(this).attr('target', '_blank');
	});
});
