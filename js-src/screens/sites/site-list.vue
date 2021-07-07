<script>
const TableSort = require('../../widgets/table-sort.vue');
const api = require('../../api');

const SiteList = {
	data: function(){
		return {
			sites: [],
			sortCol: 'title',
			sortDir: 'ASC',
			q: '',
			fetchTimeout: null,
		};
	},
	created: function(){
		this.fetch();	
	},
	destroyed: function(){
		if (this.fetchTimeout) {
			window.clearTimeout(this.fetchTimeout);
		}
	},
	methods: {
		fetch: function(){
			let params = {
		  		'sort': this.sortCol+':'+this.sortDir				
			};
			if (this.q.length > 0) {
				params.q = this.q;
			}
		  	return api.get('/sites', params).then((data) => {
		  		this.sites = data.records;
		  	});				
		},
		scheduleFetch: function(){
			if (this.fetchTimeout) {
				return;
			}
			this.fetchTimeout = window.setTimeout(() => {
				this.fetch().then(() => {
					this.fetchTimeout = null;
				});
			}, 500);
		},
		setSort: function(col, dir){
			this.sortCol = col;
			this.sortDir = dir;
			this.fetch();
		},
		doDelete: function(site) {
			if (window.confirm('Are you sure you want to delete the site "'+site.title+'"?')) {
				api.del('/sites/'+site.id).then((data) => {
					this.fetch();
				});
			}
		}
	},
	components: {
		'table-sort': TableSort,
	},
};

module.exports = SiteList;
</script>

<template>
<div>
	<router-link class="btn btn-success float-right"
		to="/sites/add">
		Add Site
	</router-link>
	<h2>Sites</h2>
	<div class="row mb-3">
		<div class="col-md-4">
			<input type="text" 
				class="form-control" 
				id="sites_q" 
				v-model="q"
				@input="scheduleFetch()"
				placeholder="Search">
		</div>
	</div>
	<table class="table table-bordered table-hover table-sm">
		<thead>
			<tr>
				<th class="text-nowrap">
					ID
					<table-sort
						col="id"
						v-bind:current-col="sortCol"
						v-bind:current-dir="sortDir"
						v-on:sort-change="setSort($event[0], $event[1])"
					></table-sort>
				</th>
				<th class="text-nowrap">
					Title
					<table-sort
						col="title"
						v-bind:current-col="sortCol"
						v-bind:current-dir="sortDir"
						v-on:sort-change="setSort($event[0], $event[1])"
					></table-sort>					
				</th>
				<th class="text-nowrap">
					URL
					<table-sort
						col="url"
						v-bind:current-col="sortCol"
						v-bind:current-dir="sortDir"
						v-on:sort-change="setSort($event[0], $event[1])"
					></table-sort>
				</th>
				<th>PHP</th>
				<th>WordPress</th>
				<th class="text-nowrap">
					Plugins
					<table-sort
						col="plugins_installed"
						v-bind:current-col="sortCol"
						v-bind:current-dir="sortDir"
						v-on:sort-change="setSort($event[0], $event[1])"
					></table-sort>
				</th>
				<th class="text-nowrap">
					Themes
					<table-sort
						col="themes_installed"
						v-bind:current-col="sortCol"
						v-bind:current-dir="sortDir"
						v-on:sort-change="setSort($event[0], $event[1])"
					></table-sort>
				</th>
				<th class="text-nowrap">
					Users
					<table-sort
						col="users_count"
						v-bind:current-col="sortCol"
						v-bind:current-dir="sortDir"
						v-on:sort-change="setSort($event[0], $event[1])"
					></table-sort>
				</th>
				<th class="text-nowrap">
					Last Reviewed
					<table-sort
						col="last_reviewed"
						v-bind:current-col="sortCol"
						v-bind:current-dir="sortDir"
						v-on:sort-change="setSort($event[0], $event[1])"
					></table-sort>
				</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
			<tr v-for="site in sites">
				<td>{{ site.id }}</td>
				<td>{{ site.title }}</td>
				<td>
					<a v-bind:href="site.url" target="_blank">{{ site.url }}</a>
				</td>
				<td>{{ site.php_version }}</td>
				<td>{{ site.wordpress_version }}</td>
				<td>{{ site.plugins_installed }}</td>
				<td>{{ site.themes_installed }}</td>
				<td>{{ site.users_count }}</td>
				<td>{{ site.last_reviewed }} ({{ site.lastreview_dayssince }} days)</td>
				<td>
					<div class="btn-group btn-group-sm">
						<a v-bind:href="site.admin_url" 
							target="_blank"
							class="btn btn-sm btn-secondary"
							title="Admin">
							<i class="bi bi-gear"></i>
						</a>
						<router-link v-bind:to="'/sites/'+site.id" class="btn btn-sm btn-primary" title="Edit">
							<i class="bi bi-pencil-square"></i>
						</router-link>
						<button class="btn btn-small btn-danger" title="Delete" @click="doDelete(site)">
							<i class="bi bi-trash-fill"></i>
						</button>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
</div>
</template>