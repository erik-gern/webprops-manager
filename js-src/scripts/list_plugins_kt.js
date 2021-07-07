const sqlite = require('sqlite');
const sqlite3 = require('sqlite3');

const lowercaseObjectKeys = require('../server/lowercaseObjectKeys');

let db;

main();

async function getPluginSites(id) {
	return await db.all(
		'SELECT * FROM vSites_Plugins WHERE plugin_id = ? ORDER BY plugin_title ASC',
		[id]
	);
}

async function main() {
	console.log('List plugins for knowledge transfer');
	console.log('-------------------------------');
	
	db = await sqlite.open({
		filename: './data.db',
		driver: sqlite3.Database
	});	
	
	const rows = await db.all(
		'SELECT * FROM vPlugins '
		+ 'WHERE sites_count > 0 '
		+ 'ORDER BY title ASC'
	);
	
	for (let i = 0; i < rows.length; i++) {
		let plugin = lowercaseObjectKeys(rows[i]);
		let plugin_sites = await getPluginSites(plugin.id);
		console.log(plugin.title);
		console.log('URL: ' + (plugin.url ? plugin.url : 'N/A'));
		console.log('Folder: ' + (plugin.folder_name ? '/'+plugin.folder_name : 'N/A'));
		console.log('Internal To EVO: ' + (plugin.is_internal == 1 ? 'Yes' : 'No'));
		console.log('');

		console.log('Sites Installed: ' + plugin.sites_count);
		for (let j = 0; j < plugin_sites.length; j++) {
			let plugin_site = lowercaseObjectKeys(plugin_sites[j]);
			console.log('* ' + plugin_site.site_title);
		}
		console.log('');

		console.log(plugin.description ? plugin.description : '[description]');
		console.log('');
		
		console.log('Ongoing Issues:');
		console.log('');
		
		//console.log(JSON.stringify(plugin));
		//console.log('');
	}
}