const sqlite = require('sqlite');
const sqlite3 = require('sqlite3');

const lowercaseObjectKeys = require('../server/lowercaseObjectKeys');

let db;

main();

async function getThemesSites(id) {
	return await db.all(
		'SELECT * FROM vSites_Themes WHERE theme_id = ? ORDER BY theme_title ASC',
		[id]
	);
}

async function main() {
	console.log('List themes for knowledge transfer');
	console.log('-------------------------------');
	
	db = await sqlite.open({
		filename: './data.db',
		driver: sqlite3.Database
	});	
	
	const rows = await db.all(
		'SELECT vThemes.*, pThemes.title AS parent_theme FROM vThemes '
		+ 'LEFT OUTER JOIN vThemes pThemes ON vThemes.parent_id = pThemes.id '
		//+ 'WHERE sites_count > 0 '
		+ 'ORDER BY vThemes.title ASC'
	);
	
	for (let i = 0; i < rows.length; i++) {
		let theme = lowercaseObjectKeys(rows[i]);
		let theme_sites = await getThemesSites(theme.id);
		if (theme_sites.length == 0) continue;
		console.log(theme.title);
		console.log('Folder: ' + (theme.folder_name ? '/'+theme.folder_name : 'N/A'));
		console.log('Parent: ' + (theme.parent_theme ? theme.parent_theme : 'N/A'));
		console.log('Internal To EVO: ' + (theme.is_internal == 1 ? 'Yes' : 'No'));
		console.log('');

		console.log('Sites Installed: ' + theme_sites.length);
		for (let j = 0; j < theme_sites.length; j++) {
			let theme_site = lowercaseObjectKeys(theme_sites[j]);
			console.log('* ' + theme_site.site_title);
		}
		console.log('');

		console.log(theme.description ? theme.description : '[description]');
		console.log('');
		
		console.log('Ongoing Issues:');
		console.log('');
		
		//console.log(JSON.stringify(theme));
		//console.log('');
	}
}