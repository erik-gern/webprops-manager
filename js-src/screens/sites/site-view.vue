<script>
const api = require('../../api');
const Publisher = require('../../Publisher');
// children
const SiteViewDetails = require('./site-view-details.vue');
const SiteViewThemes = require('./site-view-themes.vue');
const SiteViewUsers = require('./site-view-users.vue');
const SiteViewPlugins = require('./site-view-plugins.vue');
// widgets
const TabContainer = require('../../widgets/tab-container.vue');
const TabPane = require('../../widgets/tab-pane.vue');	

const SiteViewer = {
	data: function(){
		return {
			loading: true,
			id: null,
			site: {},
			plugins: [],
			themes: [],
			users: [],
			allPlugins: [],
			allThemes: [],
			allUsers: [],
			pluginsSortCol: 'plugin_title',
			pluginsSortDir: 'ASC',
			usersSortCol: 'username',
			usersSortDir: 'ASC',
			themesSortCol: 'theme_title',
			themesSortDir: 'ASC',
			detailsPublisher: new Publisher(),
		};
	},
	created: function(){
		this.id = parseInt(this.$route.params.id);
		this.fetch();	
	},
	methods: {
		// load all data
		fetch: function(){
			this.loading = true;
			Promise.all([
				api.get('/sites/'+this.id).then((data) => {
					this.site = data.record;
				}),
				this.fetchSitesPlugins(),
				this.fetchSitesUsers(),		
				this.fetchSitesThemes(),
				api.get('/plugins').then((data) => {
					this.allPlugins = data.records;
				}),		
				api.get('/themes').then((data) => {
					this.allThemes = data.records;
				}),
				api.get('/users').then((data) => {
					this.allUsers = data.records;
				}),
			]).then((all) => {
				this.loading = false;
			})
		},
		fetchSitesPlugins: function(){
			return api.get('/sites/'+this.id+'/plugins', {
				sort: this.pluginsSortCol+':'+this.pluginsSortDir,
			}).then((data) => {
				this.plugins = data.records;
			});
		},
		fetchSitesThemes: function(){
			return api.get('/sites/'+this.id+'/themes', {
				sort: this.themesSortCol+':'+this.themesSortDir,
			}).then((data) => {
				this.themes = data.records;
			});
		},
		fetchSitesUsers: function(){
			return api.get('/sites/'+this.id+'/users', {
				sort: this.usersSortCol+':'+this.usersSortDir,
			}).then((data) => {
				this.users = data.records;
			});
		},
		// site details
		editSite: function(data){
			this.detailsPublisher.publish('submit', {});
			api.put('/sites/'+this.id, {}, data).then((resp) => {
				if (resp.status == 'success') {
					this.detailsPublisher.publish('save', resp);
					api.get('/sites/'+this.id, {}).then((resp) => {
						this.site = resp.record;
					});
				}
				else {
					this.detailsPublisher.publish('error', resp);
				}
			}, (resp) => {
				this.detailsPublisher.publish('error', resp);
			});
		},
		// sites/plugins
		addPlugin: function(data) {
			api.post(
				'/sites/' + data.site_id + '/plugins/' + data.plugin_id,
				{},
				data
			).then(() => {
				this.fetchSitesPlugins();		
			});
		},
		editPlugin: function(data){
			api.put(
				'/sites/' + data.site_id + '/plugins/' + data.plugin_id,
				{},
				data
			).then(() => {
				this.fetchSitesPlugins();		
			});
		},
		deletePlugin: function(data){
			api.del(
				'/sites/' + data.site_id + '/plugins/' + data.plugin_id,
				{},
				{}
			).then(() => {
				this.fetchSitesPlugins();		
			});			
		},
		doSortPlugins: function(sortCol, sortDir){
			this.pluginsSortCol = sortCol;
			this.pluginsSortDir = sortDir;
			this.fetchSitesPlugins();
		},
		// sites/themes
		addTheme: function(data) {
			api.post(
				'/sites/' + data.site_id + '/themes/' + data.theme_id,
				{},
				data
			).then(() => {
				this.fetchSitesThemes();		
			});
		},
		editTheme: function(data){
			api.put(
				'/sites/' + data.site_id + '/themes/' + data.theme_id,
				{},
				data
			).then(() => {
				this.fetchSitesThemes();		
			});
		},
		deleteTheme: function(data){
			api.del(
				'/sites/' + data.site_id + '/themes/' + data.theme_id,
				{},
				{}
			).then(() => {
				this.fetchSitesThemes();		
			});			
		},		
		doSortThemes: function(sortCol, sortDir){
			this.themesSortCol = sortCol;
			this.themesSortDir = sortDir;
			this.fetchSitesThemes();
		},
		// sites/users
		addUser: function(data) {
			api.post(
				'/sites/' + data.site_id + '/users/' + data.user_id,
				{},
				data
			).then(() => {
				this.fetchSitesUsers();		
			});
		},
		editUser: function(data){
			api.put(
				'/sites/' + data.site_id + '/users/' + data.user_id,
				{},
				data
			).then(() => {
				this.fetchSitesUsers();		
			});
		},
		deleteUser: function(data){
			api.del(
				'/sites/' + data.site_id + '/users/' + data.user_id,
				{},
				{}
			).then(() => {
				this.fetchSitesUsers();		
			});			
		},		
		doSortUsers: function(sortCol, sortDir){
			this.usersSortCol = sortCol;
			this.usersSortDir = sortDir;
			this.fetchSitesUsers();
		},
	},
	components: {
		// children
		'site-view-details': SiteViewDetails,
		'site-view-themes': SiteViewThemes,
		'site-view-users': SiteViewUsers,
		'site-view-plugins': SiteViewPlugins,
		// widgets
		'tab-container': TabContainer,
		'tab-pane': TabPane,		
	},
};

module.exports = SiteViewer;
</script>

<template>
<div>
	<div v-if="loading">
		<p class="text-center">Loading...</p>
	</div>
	<div v-if="!loading">
		
		<tab-container>
			<tab-pane title="Details" :default="true">
				<site-view-details
					:site-id="id"
					:site-data="site"
					:publisher="detailsPublisher"
					@edit="editSite($event)"
				></site-view-details>
			</tab-pane>
			
			<tab-pane title="Users">	
				<site-view-users 
					:site-id="id"
					:users="users"
					:all-users="allUsers"
					:sort-col="usersSortCol"
					:sort-dir="usersSortDir"
					@add="addUser($event)"
					@delete="deleteUser($event)"
					@edit="editUser($event)"
					@sort="doSortUsers($event[0], $event[1])"
				></site-view-users>
			</tab-pane>
					
			<tab-pane title="Plugins">
				<site-view-plugins 
					:site-id="id"
					:plugins="plugins" 
					:all-plugins="allPlugins"
					:sort-col="pluginsSortCol"
					:sort-dir="pluginsSortDir"
					@add="addPlugin($event)"
					@delete="deletePlugin($event)"
					@edit="editPlugin($event)"
					@sort="doSortPlugins($event[0], $event[1])"
				></site-view-plugins>
			</tab-pane>
			
			<tab-pane title="Themes">
				<site-view-themes 
					:site-id="id"
					:themes="themes"
					:all-themes="allThemes"
					:sort-col="themesSortCol"
					:sort-dir="themesSortDir"
					@add="addTheme($event)"
					@delete="deleteTheme($event)"
					@edit="editTheme($event)"
					@sort="doSortThemes($event[0], $event[1])"
				></site-view-themes>
			</tab-pane>
				
		</tab-container>
		
		
	</div>
</div>
</template>