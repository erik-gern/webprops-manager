const api = require('../api');

module.exports = {
	namespaced: true,
	state: {
		sites: 0,
		nodemodules: 0,
		plugins: 0,
		themes: 0,
		users: 0,
	},
	mutations: {
		setAll: function(state, stats){
			//state.stats = stats;
			Object.keys(stats).forEach((k) => {
				state[k] = stats[k];
			});
		},
	},
	actions: {
		refresh: async function(context) {
			const data = await api.get('/stats');
			context.commit('setAll', data.stats);
		},
	},
};