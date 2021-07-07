module.exports = ping;

function ping (req, resp) {
	resp.json({
		time: Math.floor(Date.now() / 1000),
	});	
}
