const sqlite = require('sqlite');
const sqlite3 = require('sqlite3');
const fetch = require('node-fetch');


const lowercaseObjectKeys = require('../server/lowercaseObjectKeys');

let db;

main();

async function main() {
	console.log('Gather plugin URLs');
	console.log('-------------------------------');
	
	db = await sqlite.open({
		filename: './data.db',
		driver: sqlite3.Database
	});	
	
	const rows = await db.all(
		'SELECT * FROM Plugins '
		+ 'WHERE folder_name IS NOT NULL AND folder_name != "" AND url IS NULL '
		+ 'ORDER BY title ASC'
	);
	
	for (let i = 0; i < rows.length; i++) {
		let plugin = lowercaseObjectKeys(rows[i]);
		console.log(plugin.title + ': ' + plugin.folder_name);
		
		let testUrl = 'https://wordpress.org/plugins/'+ plugin.folder_name +'/';
		let isValid = false;
		console.log('  Testing '+testUrl+'...');
		const resp = await fetch(testUrl, { redirect: 'manual' });
		
		console.log('  ' + resp.status + ': ' + resp.statusText);
		
		// check for redirect
		if (resp.status == 301) {
			const newUrl = resp.headers.get('Location');
			console.log('  Plugin page returned redirect ('+ newUrl +')...');
			if (/^https\:\/\/wordpress\.org\/plugins\/search\/.+\/$/i.test(newUrl)) {
				console.log('  Redirect URL is a search page. Skipping.');
				// todo: try first result in search page
			}
			else if (/^https\:\/\/wordpress\.org\/plugins\/[a-z0-9\-_]+\/$/i.test(newUrl)) {
				console.log('  Redirect URL is plugin page, using this instead.');
				testUrl = newUrl;
				isValid = true;
			}
			else {
				console.log('  Redirect is not a valid URL.');
			}
		}
		// check for OK
		else if (resp.status == 200) {
			console.log('  Valid URL.');
			isValid = true;
		}
		else {
			console.log('  Not a valid page.');
		}
		
		// if valid page, update plugin record
		if (isValid) {
			console.log('  Updating plugin record...');
			const dbRes = await db.run("UPDATE Plugins SET url=:url WHERE id=:id", {
				':url': testUrl,
				':id': plugin.id,
			});
			console.log('  Updated ' + dbRes.changes + ' records.');
			console.log('  Done.');
		}
	}
}