const PersistenceRegistry = require('../PersistenceRegistry');

module.exports = stats;

async function stats (req, resp) {
	let sitesCount = await PersistenceRegistry.get('sites').count();
	let nodeModulesCount = await PersistenceRegistry.get('nodemodules').count();
	let pluginsCount = await PersistenceRegistry.get('plugins').count();
	let themesCount = await PersistenceRegistry.get('themes').count();
	let usersCount = await PersistenceRegistry.get('users').count();
	
	resp.json({
		'stats': {
			'sites': sitesCount,
			'nodemodules': nodeModulesCount,
			'plugins': pluginsCount,
			'themes': themesCount,
			'users': usersCount,
		},
	});	
}