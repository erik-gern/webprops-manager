<script>
const SitePluginEditor = require('../../forms/site-plugin-editor.vue');
const TableSort = require('../../widgets/table-sort.vue');

const SiteViewPlugins = {
	props: {
		'siteId': {
			'type': Number,
			'default': 0,
		},
		'plugins': {
			'type': Array,
			'default': function(){ return [] },
		},
		'allPlugins': {
			'type': Array,
			'default': function(){ return [] },			
		},
		'sortCol': {
			'type': String,
			'default': 'username',
		},
		'sortDir': {
			'type': String,
			'default': 'ASC',	
		},		
	},
	data: function(){
		return {
			mode: 'list',
			pluginToEdit: null,
		};
	},
	created: function(){
	},
	methods: {
		showEdit: function(plugin) {
			this.pluginToEdit = {...plugin};
			this.mode = 'edit';
		},
		addPlugin: function(data) {
			this.mode = 'list';
			this.$emit('add', data);
		},
		editPlugin: function(data) {
			this.mode = 'list';
			this.$emit('edit', data);
		},
		deletePlugin: function(data){
			// confirm deletion
			let pluginTitle = this.allPlugins.filter((p) => { 
				return parseInt(p.id) == parseInt(data.plugin_id);
			})[0].title;
			if (!window.confirm("Are you sure you want to remove the association with the plugin "+pluginTitle+"?")) {
				return;
			}
			
			// emit event to parent
			this.$emit('delete', data);
		},
		activePluginsCount: function(){
			return this.plugins.filter((p) => { return p.is_active == 1 }).length;			
		},
		availablePlugins: function(includeIds){
			if (!includeIds) includeIds = [];
			return this.allPlugins.filter((plugin) => {
				for (let i = 0; i < this.plugins.length; i++) {
					let pid = this.plugins[i].plugin_id;
					if (includeIds.indexOf(pid) == -1 && pid == plugin.id) {
						return false;
					}
				}
				return true;
			});
		},
		setSort: function(sortCol, sortDir) {
			this.$emit('sort', [sortCol, sortDir]);
		},			
	},
	components: {
		'site-plugin-editor': SitePluginEditor,	
		'table-sort': TableSort,
	},
};

module.exports = SiteViewPlugins;	
</script>

<template>
	<div>		
		
		<div v-if="mode=='edit'">
			<site-plugin-editor
				mode="edit"
				:plugins="availablePlugins([pluginToEdit.plugin_id])"
				:data="pluginToEdit"
				@submit="editPlugin($event)"
				@close="mode='list'"
			></site-plugin-editor>			
		</div>	
		
		<div v-if="mode=='add'">
			<site-plugin-editor
				mode="add"
				:plugins="availablePlugins()"
				:data="{site_id: siteId}"
				@submit="addPlugin($event)"
				@close="mode='list'"
			></site-plugin-editor>
		</div>		
		
		<div v-if="mode=='list'">
			<span class="float-right">
				<button @click="mode='add'" class="btn btn-success">
					<i class="bi bi-pencil-square"></i>
					Add
				</button>
			</span>				
			
			<h3>Plugins (total: {{ plugins.length }}, active: {{ activePluginsCount() }})</h3>
		
			<table class="table table-bordered table-hover table-sm">
				<thead>
					<tr>
						<th>
							Title
							<table-sort
								col="plugin_title"
								v-bind:current-col="sortCol"
								v-bind:current-dir="sortDir"
								v-on:sort-change="setSort($event[0], $event[1])"
							></table-sort>						
						</th>
						<th>
							Active
							<table-sort
								col="is_active"
								v-bind:current-col="sortCol"
								v-bind:current-dir="sortDir"
								v-on:sort-change="setSort($event[0], $event[1])"
							></table-sort>						
						</th>
						<th>
							Can Update
							<table-sort
								col="can_update"
								v-bind:current-col="sortCol"
								v-bind:current-dir="sortDir"
								v-on:sort-change="setSort($event[0], $event[1])"
							></table-sort>						
						</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="plugin in plugins" v-bind:class="{ 'text-muted': plugin.is_active==0 }">
						<td>{{ plugin.plugin_title }}</td>
						<td>{{ plugin.is_active }}</td>
						<td>{{ plugin.can_update }}</td>
						<td>
							<div class="btn-group btn-group-sm">
								<button class="btn btn-sm btn-primary" 
									title="Edit"
									@click="showEdit(plugin)">
									<i class="bi bi-pencil-square"></i>
								</button>
								<button class="btn btn-small btn-danger" 
									title="Delete" 
									@click="deletePlugin(plugin)">
									<i class="bi bi-trash-fill"></i>
								</button>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</template>