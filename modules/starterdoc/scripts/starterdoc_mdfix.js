$(document).ready(function () {
	$('a').not('[href^="http"]').each(function () {
		var href = $(this).attr('href');

		if (href) {
			$(this).attr('data-old-href', href);

			if (href[0] !== "/" && href[0] !== "?") {
				$(this).attr('href', '?action=mddoc&doc=' + MD_DOC_URI + href);
			} else if (href[0] !== '?') {
				$(this).attr('href', '?action=mddoc&doc=' + href);
			}
		}
	});
	$('img').not('[src^="http"]').each(function () {
		var src = $(this).attr('src');

		if (src) {
			$(this).attr('data-old-src', src);

			if (src[0] !== "/" && src[0] !== "?") {
				$(this).attr('src', MD_DOC_URI + src);
			} else if (src[0] !== '?') {
				$(this).attr('src', src);
			}
		}
	});
	$('a[href^="https://"], a[href^="http://"]').each(function () {
		$(this).attr('target', '_blank');
	});
});
