const express = require('express');
const normalizeSortCol = require('./normalizeSortCol');
const ResourceRouter = require('./ResourceRouter');

module.exports = ChildResourceRouter;

function ChildResourceRouter(persistence, options={}) {
	options = {
		parentId: 'parent_id',
		childResourceName: 'children',
		childId: 'id',
		...options		
	};
	let router = new ResourceRouter(persistence, options);
	
	router.route = this.route;
	
	
	return router;
}

ChildResourceRouter.prototype.route = function(){
	
	const router = express.Router();
	const listStub = '/:parent_id/'+this.options.childResourceName;
	const singleStub = '/:parent_id/'+this.options.childResourceName+'/:child_id';
	
	router.get('/', this.list.bind(this));
	router.post('/', this.validateData.bind(this));
	router.post('/', this.create.bind(this));
	
	router.use('/:id', this.checkExists.bind(this));
	router.put('/:id', this.validateData.bind(this));
	router.get('/:id', this.get.bind(this));
	router.put('/:id', this.update.bind(this));
	router.delete('/:id', this.del.bind(this));
	
	
	
	router.get(listStub, this.list.bind(this));
	router.get(singleStub, this.get.bind(this));
	router.post(listStub, this.create.bind(this));
	router.put(singleStub, this.update.bind(this));
	router.delete(singleStub, this.del.bind(this));	
	
	return router;
};
