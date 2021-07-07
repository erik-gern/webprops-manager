const lowercaseObjectKeys = require('./lowercaseObjectKeys');

module.exports = Persistence;

function Persistence (db, table, options={}) {
	options = {
		ids: ['id'],
		dataCols: [],
		selectCols: [],
		softDelete: false,
		writeTable: null,
		...options
	};
	this.db = db;
	this.table = table;
	this.idColumns = [...options.ids];
	this.dataColumns = [...options.dataCols];
	this.selectColumns = options.selectCols.length > 0 ? 
		[...options.selectCols] : 
		[...options.dataCols];
	this.softDelete = options.softDelete;
	this.softDeleteCol = options.softDeleteCol;
	this.softDeleteVal = options.softDeleteVal;
	this.writeTable = options.writeTable || this.table;
}

Persistence.prototype.normalizeIds = function(idIn) {
	let idOut = {};
	if (typeof idIn === 'string' || typeof idIn === 'number') {
		idOut[this.idColumns[0]] = idIn;
	}
	else {
		idOut = idIn;
	}
	return idOut;
};

Persistence.prototype.getSelectColumns = function() {
	if (this.selectColumns.length == 0) {
		return '*';
	}
	else {
		return this.selectColumns.join(',');
	}	
};

Persistence.prototype.createWhereClause = function (where) {
	where = {...where};
	let whereKeys = Object.keys(where);
	if (whereKeys.length == 0) {
		return ['', {}];
	}
	
	/*
	whereKeys.forEach((k) => {
		if (typeof where[k] != 'Array') {
			where[k] = ['=', k];
		}
		if (where[k].length < 2) {
			where[k].unshift('=');
		}
		if (['=', '!=', '<', '>', '>=', '<=', 'LIKE'].indexOf(where[k][0]) == -1) {
			where[k][0] = '=';
		}
	});
	
	let sql = 'WHERE ' + whereKeys.map((k) => {
		return k + ' ' + where[k][0] + ' :' + k;
	}).join(' AND ');
	
	let params = whereKeys.reduce((p, col) => {
		p[':'+col] = where[col];
		return p;
	}, {});
	*/
	
	let sqlParts = [];
	let params = {};
	whereKeys.forEach((k) => {
		const parts = k.split(' ');
		const col = parts[0];
		let oper;
		if (parts.length < 2) {
			oper = '=';
		}
		else {
			oper = parts[1];
		}
		if (['=', '!=', '<>', '<', '>', '>=', '<=', 'LIKE'].indexOf(oper) == -1) {
			oper = '=';
		}
		
		sqlParts.push(col + ' ' + oper + ' :' + col);
		params[':'+col] = where[k];
	})
	const sql = 'WHERE ' + sqlParts.join(' AND ');
	
	return [sql, params];
};

Persistence.prototype.exists = async function(ids) {
	console.log('original ids', ids)
	ids = this.normalizeIds(ids);
	console.log('Persistence::exists', ids);
	let count = await this.count(ids);
	return count > 0;
}

Persistence.prototype.count = async function(where={}) {
	let sql = 'SELECT COUNT(*) AS count FROM '+this.table;
	let params = {};
	if (Object.keys(where).length > 0) {
		let whereClause = this.createWhereClause(where);
		sql += ' ' + whereClause[0];
		params = {
			...params,
			...whereClause[1],
		};
	}
	let countRow = await this.db.get(sql, params);
	return parseInt(countRow.count);
};

Persistence.prototype.list = async function(where={}, options={}) {
	options = {
		start: 0,
		count: 0,
		sortCol: null,
		sortDir: 'ASC',
		...options
	};
	
	let sql = 'SELECT ';
	let params = {};
	sql += this.getSelectColumns();
	sql += ' FROM '+this.table;
	
	let whereClause = this.createWhereClause(where);
	sql += ' ' + whereClause[0];
	params = {...params, ...whereClause[1]};
	
	if (options.sortCol != null) {
		sql += ' ORDER BY ' + options.sortCol + ' COLLATE NOCASE ' + options.sortDir.toUpperCase();
	}
	
	if (options.count > 0) {
		sql += ' LIMIT ' + options.count.toString();
	}
	if (options.start > 0) {
		sql += ' OFFSET ' + options.start.toString();
	}
	
	console.log(sql, params);
	let records = await this.db.all(sql, params);
	records = records.map((record) => {
		return lowercaseObjectKeys(record);
	});
	return records;	
};

Persistence.prototype.get = async function(ids) {
	ids = this.normalizeIds(ids);
	let sql = 'SELECT ';
	sql += this.getSelectColumns();
	sql += ' FROM '+this.table+' ';
	let whereClause = this.createWhereClause(ids);
	sql += whereClause[0];
	let params = {...whereClause[1]};
	sql += ' LIMIT 1';
		
	let record = await this.db.get(sql, params);
	record = lowercaseObjectKeys(record);
	return record;
};

Persistence.prototype.create = async function(data) {
	console.log('Persistence::create');
	let params = this.dataColumns.reduce((p, k) => {
		p[':'+k] = data[k];
		return p;
	}, {});
	let sql = 'INSERT INTO '+this.writeTable+' (';
	sql += this.dataColumns.join(',');
	sql += ') VALUES (';
	sql += ':'+this.dataColumns.join(',:');
	sql += ')';

	let res = await this.db.run(sql, params);
	return res.lastID;
}

Persistence.prototype.update = async function(ids, data) {
	ids = this.normalizeIds(ids);
	let params = this.dataColumns.reduce((p, k) => {
		p[':'+k] = data[k];
		return p;
	}, {});
	let sql = 'UPDATE '+this.writeTable+' SET ';
	sql += this.dataColumns.map((k) => k+'=:'+k).join(',');
	let whereClause = this.createWhereClause(ids);
	sql += ' '+whereClause[0];
	params = {
		...params,
		...whereClause[1]
	};
	let res = await this.db.run(sql, params);
	return res.changes;
}

Persistence.prototype.del = async function(ids) {
	ids = this.normalizeIds(ids);
	let sql;
	let params = {};
	if (this.softDelete) {
		sql = 'UPDATE '+this.writeTable;
		sql += ' SET '+this.softDeleteCol+'=:'+this.softDeleteCol;
		params[':'+this.softDeleteCol] = this.softDeleteVal;
	}	
	else {
		sql = 'DELETE FROM '+this.writeTable;
	}
	let whereClause = this.createWhereClause(ids);
	sql += ' '+whereClause[0];
	params = {...params, ... whereClause[1]};

	res = await this.db.run(sql, params);
	return res.changes;
};
