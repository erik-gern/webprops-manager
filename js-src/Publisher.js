module.exports = Publisher;

function Publisher () {
	this.events = {};
}

Publisher.prototype.on = function(event, callback){
	if (!this.events[event]) this.events[event] = [];
	this.events[event].push(callback);
	return this;
};

Publisher.prototype.remove = function(event, callback) {
	if (!this.events[event]) return this;
	let pos = this.events[event].indexOf(callback);
	if (pos >= 0) {
		this.events[event].splice(this.events[event].indexOf(callback), 1);
	}
	return this;	
};

Publisher.prototype.removeAll = function(event) {
	if (this.events[event]) {
		this.events[event].splice(0, this.events[event].length);
	}
	return this;
};

Publisher.prototype.publish = function(event, data) {
	if (!this.events[event]) return this;
	this.events[event].forEach((callback) => {
		callback(data);
	});
	return this;
}
