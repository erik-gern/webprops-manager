const api = {
	baseUrl: '/api',
	stringifyParams: function(params){
		let str = Object.keys(params).map((k) => {
			return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]);
		}).join('&');
		return str;
	},
	call: function(url, method='GET', queryParams={}, postParams={}) {
		console.log('api.call', url, method, queryParams, postParams);
		let options = {
			method: method,
		};
		if (['GET', 'HEAD'].indexOf(method) == -1 && Object.keys(postParams).length > 0) {
			options.body = this.stringifyParams(postParams);
			options.headers = {
				'Content-Type': 'application/x-www-form-urlencoded'
			};
		}
		let fullUrl = this.baseUrl + url;
		if (Object.keys(queryParams).length > 0) {
			fullUrl += '?' + this.stringifyParams(queryParams);
		}
		return fetch(fullUrl, options).then((response) => { 
			if (response.status >= 500) {
				throw new Error('API call failed with status '+response.status);
			}
			return response.json();
		});		
	},
	get: function(url, queryParams){
		return this.call(url, 'GET', queryParams);
	},
	post: function(url, queryParams, postParams){
		return this.call(url, 'POST', queryParams, postParams);
	},
	put: function(url, queryParams, postParams){
		return this.call(url, 'PUT', queryParams, postParams);
	},
	del: function(url, queryParams, postParams){
		return this.call(url, 'DELETE', queryParams, postParams);	
	},
};

module.exports = api;
