module.exports = validateSite;

function validateSite (data) {
	let errors = [];
	if (!data.hasOwnProperty('title') || data.title.length == 0) {
		errors.push('Title is required.')
	}
	
	if (!data.hasOwnProperty('url') || data.url.length == 0) {
		errors.push('Home URL is required.')
	}
	else if (/^http[s]?\:\/\/.+$/i.test(data.url) == false) {
		errors.push('Home URL must be a correctly formatted URL.');
	}
	
	if (!data.hasOwnProperty('admin_url') || data.admin_url.length == 0) {
		errors.push('Admin URL is required.')				
	}
	else if (/^http[s]?\:\/\/.+$/i.test(data.admin_url) == false) {
		errors.push('Admin URL must be a correctly formatted URL.');
	}
	
	if (!data.hasOwnProperty('host') || data.host.length == 0) {
		errors.push('Host is required.')
	}
	
	if (!data.hasOwnProperty('description') || data.description.length == 0) {
		errors.push('Description is required.')
	}
	
	['php', 'wordpress'].forEach((software) => {
		['major', 'minor', 'patch'].forEach((level) => {
			const key = software + '_version_' + level;
			if (data.hasOwnProperty(key) && data[key] !== null) {
				let numVal = parseInt(data[key]);
				if (isNaN(numVal) || numVal < 0) {
					errors.push(key + ' must be an integer greater than or equal to 0.');
				}
			}
		});
	});
	
	return errors;	
}
