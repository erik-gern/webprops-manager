module.exports = validateSitePlugin;

function validateSitePlugin (data) {
	console.log(data);
	let errors = [];
	if (!data.is_active) {
		errors.push('is_active is required.');
	}
	else if ([0, 1].indexOf(parseInt(data.is_active)) == -1) {
		errors.push('is_active must be either 0 or 1');
	}
	if (!data.can_update) {
		errors.push('can_update is required.');
	}
	else if ([0, 1].indexOf(parseInt(data.can_update)) == -1) {
		errors.push('can_update must be either 0 or 1');
	}
	return errors;
}