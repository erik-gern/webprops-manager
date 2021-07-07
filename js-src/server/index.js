const fs = require('fs');
const express = require('express');
const sqlite3 = require('sqlite3');
const sqlite = require('sqlite');
const yaml = require('js-yaml');

const PersistenceRegistry = require('./PersistenceRegistry');
const ResourceRouter = require('./ResourceRouter');

const pingRoute = require('./routes/ping');
const statsRoute = require('./routes/stats');

const validateSite = require('./validators/validateSite');
const validateSitePlugin = require('./validators/validateSitePlugin');

const app = express();

const port = 8000;

let db;

async function start() {
	
	app.use(express.urlencoded({ extended: true }));

	db = await sqlite.open({
		filename: 'data.db',
		driver: sqlite3.Database
	});	
	
	PersistenceRegistry.init(db, yaml.load(fs.readFileSync('persistence_config.yaml')));

	app.use(express.static('public'));
	
	app.route('/api/ping').post(pingRoute);

	app.route('/api/stats').get(statsRoute);
	
	app.use('/api/nodemodules', new ResourceRouter(PersistenceRegistry.get('nodemodules')).route());
	
	app.use('/api/sites', new ResourceRouter(PersistenceRegistry.get('sites'), {
		validateData: validateSite,
	}).route());
	app.use('/api/sites', new ResourceRouter(PersistenceRegistry.get('sites_plugins'), {
		collectionStub: '/:site_id/plugins',
		recordStub: '/:site_id/plugins/:plugin_id',
		recordPostStub: '/:site_id/plugins/:plugin_id',
		validateData: validateSitePlugin,
	}).route());
	app.use('/api/sites', new ResourceRouter(PersistenceRegistry.get('sites_users'), {
		collectionStub: '/:site_id/users',
		recordStub: '/:site_id/users/:user_id',
		recordPostStub: '/:site_id/users/:user_id',
	}).route());
	app.use('/api/sites', new ResourceRouter(PersistenceRegistry.get('sites_themes'), {
		collectionStub: '/:site_id/themes',
		recordStub: '/:site_id/themes/:theme_id',
		recordPostStub: '/:site_id/themes/:theme_id',
	}).route());
	
	app.use('/api/plugins', new ResourceRouter(PersistenceRegistry.get('plugins')).route());

	app.use('/api/themes', new ResourceRouter(PersistenceRegistry.get('themes')).route());

	app.use('/api/users', new ResourceRouter(PersistenceRegistry.get('users')).route());

	app.listen(port, () => {
		console.log('Listening on port ' + port.toString());
	});

}

start();