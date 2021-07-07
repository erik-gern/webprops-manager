const sqlite = require('sqlite');
const sqlite3 = require('sqlite3');

const lowercaseObjectKeys = require('../server/lowercaseObjectKeys');

let db;

main();

async function main() {
	console.log('List web properties for knowledge transfer');
	console.log('-------------------------------');
	
	db = await sqlite.open({
		filename: './data.db',
		driver: sqlite3.Database
	});	
	
	const rows = await db.all(
		'SELECT * FROM vSites '
		+ 'ORDER BY title ASC'
	);
	
	for (let i = 0; i < rows.length; i++) {
		let site = lowercaseObjectKeys(rows[i]);
		console.log(site.title);
		console.log('URL: ' + site.url);
		console.log('Admin URL: ' + site.admin_url);
		console.log('Framework: WordPress');
		console.log('Server: ' + site.host);
		console.log('PoC: ???');
		console.log('');
		console.log(site.description);
		console.log('');
		console.log('Ongoing issues: ');
		console.log('');
	}
}