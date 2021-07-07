<script>
const TableSort = require('../widgets/table-sort.vue');

const Plugins = {
	data: function(){
		return {
			sortCol: 'title',
			sortDir: 'ASC',
			fetchTimeout: null,
			q: '',
		};
	},
	mounted: function(){
		this.fetch();
	},
	computed: {
		records: function(){ return this.$store.state.plugins.records; },
		error: function(){ return this.$store.state.plugins.error; },
		validationErrors: function(){ return this.$store.state.plugins.validationErrors; },
		currentRecord: function(){ return this.$store.state.plugins.currentRecord; },
		newRecord: function(){ return this.$store.state.plugins.newRecord; },
	},
	methods: {
		fetch: function(){
			let params = {
		  		'sort': this.sortCol+':'+this.sortDir				
			};
			if (this.q.length > 0) {
				params.q = this.q;
			}			
	  		this.$store.dispatch('plugins/list', params);					
		},
		scheduleFetch: function(){
			if (this.fetchTimeout) {
				return;
			}
			this.fetchTimeout = window.setTimeout(this.onFetchTimeout.bind(this), 500);
		},		
		onFetchTimeout: function(){
			this.fetch().then(() => {
				this.fetchTimeout = null;
			});
		},
		setSort: function(col, dir) {
			this.sortCol = col;
			this.sortDir = dir;
			this.fetch();
		},
		doDelete: function(plugin) {
			if (window.confirm('Are you sure you want to delete the plugin "'+plugin.title+'"?')) {
				// todo: store delete action
			}
		},		
	},
	components: {
		'table-sort': TableSort,
	}
};

module.exports = Plugins;
</script>

<template>
<div>
	<h2>Plugins</h2>
	<div v-if="error">
		There was an error listing the plugins: {{ error }}
	</div>
	<div class="row mb-3">
		<div class="col-md-4">
			<input type="text" 
				class="form-control" 
				id="plugins_q" 
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
					Folder
					<table-sort
						col="folder_name"
						v-bind:current-col="sortCol"
						v-bind:current-dir="sortDir"
						v-on:sort-change="setSort($event[0], $event[1])"
					></table-sort>					
				</th>		
				<th class="text-nowrap">
					Sites
					<table-sort
						col="sites_count"
						v-bind:current-col="sortCol"
						v-bind:current-dir="sortDir"
						v-on:sort-change="setSort($event[0], $event[1])"
					></table-sort>					
				</th>
				<th class="text-nowrap">
					Internal
					<table-sort
						col="is_internal"
						v-bind:current-col="sortCol"
						v-bind:current-dir="sortDir"
						v-on:sort-change="setSort($event[0], $event[1])"
					></table-sort>					
				</th>										
				<th class="text-nowrap">
					Description
					<table-sort
						col="description"
						v-bind:current-col="sortCol"
						v-bind:current-dir="sortDir"
						v-on:sort-change="setSort($event[0], $event[1])"
					></table-sort>					
				</th>
				<th class="text-nowrap">
					Actions
				</th>
			</tr>
		</thead>
		<tbody>
			<tr v-for="plugin in records">
				<td>{{ plugin.id }}</td>
				<td>{{ plugin.title }}</td>
				<td>{{ plugin.folder_name }}</td>
				<td>{{ plugin.sites_count }}</td>
				<td>{{ plugin.is_internal }}</td>
				<td>{{ plugin.description }}</td>
				<td>
					<div class="btn-group btn-group-sm">
						<a v-if="plugin.url"
							class="btn btn-sm btn-secondary" 
							:href="plugin.url"
							target="_blank"
							title="Open Plugin home page in new tab">
							<i class="bi bi-link-45deg"></i>
						</a>
						<router-link v-bind:to="'/plugins/'+plugin.id" class="btn btn-sm btn-primary" title="Edit">
							<i class="bi bi-pencil-square"></i>
						</router-link>
						<button class="btn btn-sm btn-danger" title="Delete" @click="doDelete(plugin)">
							<i class="bi bi-trash-fill"></i>
						</button>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
</div>
</template>