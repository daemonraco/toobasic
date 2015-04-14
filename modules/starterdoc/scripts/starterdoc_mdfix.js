$(document).ready(function() {
	$('a').not('[href^="http"]').each(function() {
		var href = $(this).attr("href");
		if (href[0] !== "/") {
			$(this).attr("href", "?action=mddoc&doc=" + MD_DOC_URI + href);
		} else {
			$(this).attr("href", "?action=mddoc&doc=" + href);
		}
	});
});
