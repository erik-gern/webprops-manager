const util = require('util');
const sqlite = require('sqlite');
const sqlite3 = require('sqlite3');
const exec = util.promisify(require('child_process').exec);
const fetch = require('node-fetch');

const npmPackageUrl = 'https://core.trac.wordpress.org/browser/trunk/package.json?format=txt';

async function getWordpressCoreDeps () {
	let packageResp = await fetch(npmPackageUrl, {
		headers: {
			'User-Agent': 'Mozilla 5.0 (Compatible; Web Properties Manager)',
		}
	});
	console.log(packageResp);
	let packageObj = await packageResp.json();
	console.log(packageObj);
	return Object.keys(packageObj.dependencies);
}

async function getPackageInfo (pkg) {
	let out = await exec('npm view "' + pkg.replace('"', '\\"') + '" --json');
	let pkgInfo = JSON.parse(out.stdout);
	if (!pkgInfo.author) {
		pkgInfo.author = {
			name: 'N/A',
			email: 'N/A',
			url: 'N/A',
		};
	}
	if (typeof pkgInfo.author == 'string') {
		let authorName = pkgInfo.author;
		let authorEmail = null;
		let authorUrl = null;
		const author_email_url = /^(.+) <([^>]+)> \(([^\)]+)\)$/i;
		const author_url_email = /^(.+) \(([^\)]+)\) <([^>]+)>$/i;
		const author_email = /^(.+) <([^>]+)>$/i;
		const author_url = /^(.+) \(([^\)]+)\)$/i;
		if (author_email_url.test(authorName)) {
			let matches = author_email_url.exec(authorName);
			authorName = matches[1];
			authorEmail = matches[2];
			authorUrl = matches[3];
		}
		else if (author_url_email.test(authorName)) {
			let matches = author_url_email.exec(authorName);
			authorName = matches[1];
			authorUrl = matches[2];			
			authorEmail = matches[3];
		}
		else if (author_email.test(authorName)) {
			let matches = author_email.exec(authorName);
			authorName = matches[1];
			authorEmail = matches[2];
		}
		else if (author_url.test(authorName)) {
			let matches = author_url.exec(authorName);
			authorName = matches[1];
			authorUrl = matches[2];			
		}
		pkgInfo.author = {
			name: authorName,
			email: authorEmail,
			url: authorUrl,
		};
	}
	return pkgInfo;
}

async function run () {
	console.log('WordPress Core npm Dependencies');
	console.log('-------------------------------');
	
	let deps = await getWordpressCoreDeps();
	console.log('Dependencies: ' + deps.length);
	deps.forEach(async (pkg) => {
		let pkgInfo;
		try {
			pkgInfo = await getPackageInfo(pkg);
		}
		catch (e) {
			console.log(e);
			return;
		}
		console.log(pkgInfo.name);
		console.log('  Description: ' + pkgInfo.description);
		console.log('  Author: '+ pkgInfo.author.name + 
			' <' + pkgInfo.author.email + '>' + 
			' (' + pkgInfo.author.url + ')');
		console.log('  License: '+pkgInfo.license);		
	});
}

run();