$(document).ready(function() {
	$('h1,h2,h3,h4,h5,h6').each(function() {
		var name = $(this).text().toLowerCase();
		name = name.replace(/__/g, '_');
		name = name.replace(/(_ | _)/g, ' ');
		name = name.replace(/(.*)_$/g, '$1');
		name = name.replace(/^_(.*)/g, '$1');
		name = name.replace(/([\?!'.:>\(\)\$])/g, '');
		name = name.replace(/ /g, '-');

		$(this).attr('id', name);
	});
	$('.navbar').each(function() {
		if (window.location.href.match(/(.*)#(.+)/)) {
			var height = $(this).height();
			window.setTimeout(function() {
				window.scrollBy(0, 0 - height);
			}, 10);
		}
	});
});
