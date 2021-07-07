<script>
const TableSort = require('../../widgets/table-sort.vue');
const api = require('../../api');

const NodeModuleList = {
	data: function(){
		return {
			nodemodules: [],
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
		  	return api.get('/nodemodules', params).then((data) => {
		  		this.nodemodules = data.records;
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
		doDelete: function(nodemodule) {
			if (window.confirm('Are you sure you want to delete the node module "'+nodemodule.title+'"?')) {
				api.del('/nodemodules/'+nodemodule.id).then((data) => {
					this.fetch();
				});
			}
		}
	},
	components: {
		'table-sort': TableSort,
	},
};

module.exports = NodeModuleList;
</script>

<template>
<div>
	<router-link class="btn btn-success float-right"
		to="/nodemodules/add">
		Add Node Module
	</router-link>
	<h2>Node Modules</h2>
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
						col="homepage_url"
						v-bind:current-col="sortCol"
						v-bind:current-dir="sortDir"
						v-on:sort-change="setSort($event[0], $event[1])"
					></table-sort>
				</th>
				<th class="text-nowrap">
					Author
					<table-sort
						col="author_name"
						v-bind:current-col="sortCol"
						v-bind:current-dir="sortDir"
						v-on:sort-change="setSort($event[0], $event[1])"
					></table-sort>					
				</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
			<tr v-for="nodemodule in nodemodules">
				<td>{{ nodemodule.id }}</td>
				<td>{{ nodemodule.title }}</td>
				<td>
					<a v-bind:href="nodemodule.homepage_url" 
						target="_blank">
						{{ nodemodule.homepage_url }}
					</a>
				</td>
				<td>
					{{ nodemodule.author_name }}<br>
					<div class="btn-group btn-group-sm">
						<a v-if="nodemodule.author_emailaddress"
							class="btn btn-primary"
							:href="'mailto:' + nodemodule.author_emailaddress">
							<i class="bi bi-envelope-fill"></i>
						</a>
						<a v-if="nodemodule.author_url"
							class="btn btn-secondary"
							:href="nodemodule.author_url"
							target="_blank">
							<i class="bi bi-house-door-fill"></i>
						</a>
					</div>
				</td>
				<td>
					<div class="btn-group btn-group-sm">
						<router-link v-bind:to="'/nodemodules/'+nodemodule.id" class="btn btn-sm btn-primary" title="Edit">
							<i class="bi bi-pencil-square"></i>
						</router-link>
						<button class="btn btn-small btn-danger" title="Delete" @click="doDelete(nodemodule)">
							<i class="bi bi-trash-fill"></i>
						</button>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
</div>
</template>