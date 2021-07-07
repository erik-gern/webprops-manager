const Persistence = require('./Persistence');

module.exports = {
	_instances: {},
	init: function(db, config) {
		this.db = db;
		this.config = config;
	},
	get: function (name) {
		if (!this.config[name]) {
			throw new Error('Not config found for persistence '+name);
		}
		if (!this._instances[name]) {
			this._instances[name] = new Persistence(
				this.db, 
				this.config[name].table,
				{...this.config[name]}
			);
		}
		return this._instances[name];
	},
};
