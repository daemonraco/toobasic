/**
 * @file toobasic_rest_asset.js
 * @author Alejandro Dario Simi
 */
//
// @class RestManager
// This manager acts as interface between JavaScript code and rest calls
// against TooBasic resources.
//
// Allowed calls:
// 	| URL                           | GET | PUT | POST | DELETE |
// 	|:------------------------------|:---:|:---:|:----:|:------:|
// 	| resource/<resource-name>      |  Y  |  N  |  Y   |   N    |
// 	| resource/<resource-name>/<id> |  Y  |  Y  |  N   |   Y    |
// 	| stats/<resource-name>         |  Y  |  N  |  N   |   N    |
// 	| search/<resource-name>        |  Y  |  N  |  N   |   N    |
//
// Known actions:
// 	| URL                           | Method | Name    |
// 	|:------------------------------|:------:|:-------:|
// 	| resource/<resource-name>      | GET    | index   |
// 	| resource/<resource-name>      | POST   | create  |
// 	| resource/<resource-name>/<id> | GET    | show    |
// 	| resource/<resource-name>/<id> | PUT    | update  |
// 	| resource/<resource-name>/<id> | DELETE | destroy |
// 	| search/<resource-name>        | GET    | search  |
// 	| stats/<resource-name>         | GET    | stats   |
//
//
// Checking if jQuery was previously included.
if (window.jQuery) {
	//
	// Checking namespace.
	if (typeof window.TooBasic === 'undefined') {
		window.TooBasic = {};
	}
	/**
	 * Class constructor.
	 *
	 * @param {string} resourceName Name of the resource to use.
	 * @param {string} url This optional parameter let's specify a URL where the
	 * TooBasic RESTful service is.
	 * @return {TooBasic.RestManager} Returns an object useful to interact with
	 * a resource.
	 */
	window.TooBasic.RestManager = function (resourceName, url) {
		//
		// Properties.
		/**
		 * @var string Resource name shortcut.
		 */
		this.resourceName = resourceName;
		/**
		 * @var string Current URL shortcut including the parameter
		 * 'rest'.
		 */
		this.url = false;
		//
		// Actions.
		/**
		 * This method uses the RESTful path 'resource/<resource-name>' (on
		 * method GET).
		 *
		 * @param {type} successFunc Success callback function.
		 * @param {type} errorFunc Error callback function.
		 * @returns {jqXHR}
		 */
		this.index = function (params, successFunc, errorFunc) {
			//
			// Fixing parameters.
			console.log('DEBUG', typeof params);
			if (typeof params === 'function') {
				errorFunc = false;
				successFunc = params;
				params = {};
			}
			//
			// Fixing callbacks.
			successFunc = fixCallback(successFunc);
			errorFunc = fixCallback(errorFunc);
			//
			// Building URL.
			var url = this.url + 'resource/' + this.resourceName + paramsToString(params);
			this.logUrl(url);

			return $.ajax({
				dataType: 'json',
				url: url,
				data: {},
				success: successFunc,
				error: errorFunc
			});
		};
		// | resource/<resource-name>      | POST   |
		this.create = function (data, successFunc, errorFunc) {
			//
			// Fixing callbacks.
			successFunc = fixCallback(successFunc);
			errorFunc = fixCallback(errorFunc);
			//
			// Building URL.
			var url = this.url + 'resource/' + this.resourceName;
			this.logUrl(url);

			return $.ajax({
				url: url,
				type: 'POST',
				dataType: 'json',
				contentType: 'application/json',
				data: JSON.stringify(data),
				success: successFunc,
				error: errorFunc
			});
		};
		// | resource/<resource-name>/<id> | GET    |
		this.show = function (id, params, successFunc, errorFunc) {
			//
			// Fixing parameters.
			if (typeof params === 'function') {
				errorFunc = false;
				successFunc = params;
				params = {};
			}
			//
			// Fixing callbacks.
			successFunc = fixCallback(successFunc);
			errorFunc = fixCallback(errorFunc);
			//
			// Building URL.
			var url = this.url + 'resource/' + this.resourceName + '/' + id + paramsToString(params);
			this.logUrl(url);

			return $.ajax({
				dataType: 'json',
				url: url,
				data: {},
				success: successFunc,
				error: errorFunc
			});
		};
		// | resource/<resource-name>/<id> | PUT    |
		this.update = function (id, data, successFunc, errorFunc) {
			//
			// Fixing callbacks.
			successFunc = fixCallback(successFunc);
			errorFunc = fixCallback(errorFunc);
			//
			// Building URL.
			var url = this.url + 'resource/' + this.resourceName + '/' + id;
			this.logUrl(url);

			return $.ajax({
				url: url,
				type: 'PUT',
				dataType: 'json',
				contentType: 'application/json',
				data: JSON.stringify(data),
				success: successFunc,
				error: errorFunc
			});
		};
		// | resource/<resource-name>/<id> | DELETE |
		this.destroy = function (id, successFunc, errorFunc) {
			//
			// Fixing callbacks.
			successFunc = fixCallback(successFunc);
			errorFunc = fixCallback(errorFunc);
			//
			// Building URL.
			var url = this.url + 'resource/' + this.resourceName + '/' + id;
			this.logUrl(url);

			return $.ajax({
				url: url,
				type: 'DELETE',
				dataType: 'json',
				data: {},
				success: successFunc,
				error: errorFunc
			});
		};
		// | search/<resource-name>        | GET    |
		this.search = function (conditions, params, successFunc, errorFunc) {
			//
			// Fixing parameters.
			if (typeof params === 'function') {
				errorFunc = false;
				successFunc = params;
				params = {};
			}
			//
			// Fixing callbacks.
			successFunc = fixCallback(successFunc);
			errorFunc = fixCallback(errorFunc);
			//
			// Building URL.
			var conditionsStr = [];
			$.each(conditions, function (k, v) {
				conditionsStr.push(k);
				conditionsStr.push(v);
			});
			conditionsStr = conditionsStr.join('/');

			var url = this.url + 'search/' + this.resourceName + '/' + conditionsStr + paramsToString(params);
			this.logUrl(url);

			return $.ajax({
				dataType: 'json',
				url: url,
				data: {},
				success: successFunc,
				error: errorFunc
			});
		};
		// | stats/<resource-name>         | GET    |
		this.stats = function (successFunc, errorFunc) {
			//
			// Fixing callbacks.
			successFunc = fixCallback(successFunc);
			errorFunc = fixCallback(errorFunc);
			//
			// Building URL.
			var url = this.url + 'stats/' + this.resourceName;
			this.logUrl(url);

			return $.ajax({
				dataType: 'json',
				url: url,
				data: {},
				success: successFunc,
				error: errorFunc
			});
		};
		//
		// Methods.
		this.logUrl = function (url) {
			console.log('RestManager[' + this.resourceName + '] call: ' + url);
		};
		//
		// Protected methods.
		/**
		 * This simple function is used when a callback is not specified.
		 */
		var dummyFunc = function () {
			// Nothing to do here.
		};
		var fixCallback = function (callback) {
			if (typeof callback !== 'function') {
				callback = dummyFunc;
			}
			return callback;
		}
		var paramsToString = function (params) {
			out = [];

			$.each(params, function (k, v) {
				out.push(k + '=' + v);
			});
			out = out.join('&');

			return out ? '&' + out : '';
		}
		//
		// ---------------------------------------------------------------
		// Initialization.
		//
		// Building URL @{
		if (typeof url === 'undefined') {
			this.url = window.location.href;
		} else {
			this.url = url;
		}
		this.url += this.url.indexOf('?') > -1 ? '&' : '?';
		this.url += 'rest=';
		// @}
	};
} else {
	//
	// Prompting an error when jQuery is not present.
	console.log("'toobasic_rest_asset.js' requires jQuery to be included before it.");
}
