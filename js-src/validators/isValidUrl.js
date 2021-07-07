module.exports = isValidUrl;

function isValidUrl (url) {
	return /^http(s)?\:\/\/[^ ]{2,}\.[^\. ]{2,}(.+)?$/i.test(url);
}
