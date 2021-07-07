module.exports = lowercaseObjectKeys;

function lowercaseObjectKeys(obj) {
	let out = {};
	Object.keys(obj).forEach((k) => {
		out[k.toLowerCase()] = obj[k];
	});
	return out;
}
