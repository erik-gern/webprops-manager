const api = require('../api');

module.exports = {
	namespaced: true,
	actions: {
		'ping': async function () {
			await api.post('/ping');
		},
	},
};
