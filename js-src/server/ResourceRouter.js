const express = require('express');
const normalizeSortCol = require('./normalizeSortCol');

module.exports = ResourceRouter;

function ResourceRouter (persistence, options={}) {
	this.persistence = persistence;
	this.options = {
		validateData: () => [],
		collectionStub: '/',
		recordStub: '/:id',
		recordPostStub: '/',
		...options	
	};
	
	this._validateData = this.options.validateData;
	this.collectionStub = this.options.collectionStub;
	this.recordStub = this.options.recordStub;
	this.recordPostStub = this.options.recordPostStub;
	
	return this;
}

ResourceRouter.prototype.getIdsFromRequest = function(req){
	console.log('ResourceRouter::getIdsFromRequest');
	console.log(req.params);
	const ids = this.persistence.idColumns.reduce((p, k) => {
		p[k] = req.params[k];
		return p;
	}, {});
	console.log(ids);
	return ids;
};

ResourceRouter.prototype.route = function() {
	let router = express.Router();
	
	router.get(this.collectionStub, this.list.bind(this));
	router.post(this.recordPostStub, this.validateData.bind(this));
	router.post(this.recordPostStub, this.create.bind(this));
	
	router.use(this.recordStub, this.checkExists.bind(this));
	router.put(this.recordStub, this.validateData.bind(this));
	router.get(this.recordStub, this.get.bind(this));
	router.put(this.recordStub, this.update.bind(this));
	router.delete(this.recordStub, this.del.bind(this));
	
	return router;
};

ResourceRouter.prototype.validateData = async function(req, resp, next) {	
	console.log('ResourceRouter::validateData');
	let data = req.body;
	let errors;
	try {
		errors = this._validateData(data);
	}
	catch (e) {
		console.log(e);
		resp.status(500);
		return;
	}
	if (errors.length > 0) {
		resp.status(400).json({
			'errors': errors,
			'status': 'Invalid data',
		});
		return;
	}
	next();
}

ResourceRouter.prototype.checkExists = async function(req, resp, next) {
	console.log('ResourceRouter::checkExists');
	let exists;
	try {
		exists = await this.persistence.exists(this.getIdsFromRequest(req));
	}
	catch (e) {
		console.log(e);
		resp.status(500);
		return;
	}
	if (!exists) {
		resp.status(404);
		return;
	}	
	next();
};

ResourceRouter.prototype.list = async function(req, resp) {
	let where = {
		...req.params,
	};
	if (req.query['q']) {
		where['search_text LIKE'] = '%'+req.query['q']+'%';
	}
	let sorts = normalizeSortCol(req.query['sort']);
	let opts = {};
	if (sorts.length > 0) {
		opts['sortCol'] = sorts[0][0];
		opts['sortDir'] = sorts[0][1];
	}
	let records = await this.persistence.list(where, opts);
	resp.json({
		'records': records,
	});		
};

ResourceRouter.prototype.get = async function(req, resp) {	
	let record = await this.persistence.get(this.getIdsFromRequest(req));
	resp.json({
		'record': record,
	});
};

ResourceRouter.prototype.create = async function(req, resp) {
	let id;
	try {
		id = await this.persistence.create(req.body);
	}
	catch (e) {
		console.log(e);
		resp.status(500);
		return;
	}
	resp.status(201).json({
		'status': 'success',
		'id': id,
	});
};

ResourceRouter.prototype.update = async function(req, resp) {
	let ids = this.getIdsFromRequest(req);
	let affectedRows;
	try {
		affectedRows = await this.persistence.update(ids, req.body);
	}
	catch (e) {
		console.log(e);
		resp.status(500);
		return;
	}
	resp.json({
		'status': 'success',
		'affected_rows': affectedRows,
	});	
}

ResourceRouter.prototype.del = async function(req, resp) {
	let affectedRows;
	try {
		affectedRows = await this.persistence.del(this.getIdsFromRequest(req));
	}
	catch (e) {
		console.log(e);
		resp.status(500);
		return;
	}
	resp.json({
		'status': 'success',
		'affected_rows': affectedRows,
	});
};
