<script>
const SiteThemeEditor = require('../../forms/site-theme-editor.vue');
const TableSort = require('../../widgets/table-sort.vue');

const SiteViewThemes = {
	props: {
		'siteId': {
			'type': Number,
			'default': 0,
		},
		'themes': {
			'type': Array,
			'default': function(){ return [] },
		},
		'allThemes': {
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
			themeToEdit: null,
		};
	},
	created: function(){
	},
	methods: {
		showEdit: function(theme) {
			this.themeToEdit = {...theme};
			this.mode = 'edit';
		},
		addTheme: function(data) {
			this.mode = 'list';
			this.$emit('add', data);
		},
		editTheme: function(data) {
			this.mode = 'list';
			this.$emit('edit', data);
		},
		deleteTheme: function(data){
			// confirm deletion
			let themeTitle = this.allThemes.filter((p) => { 
				return parseInt(p.id) == parseInt(data.theme_id);
			})[0].title;
			if (!window.confirm("Are you sure you want to remove the association with the theme "+themeTitle+"?")) {
				return;
			}
			
			// emit event to parent
			this.$emit('delete', data);
		},
		availableThemes: function(includeIds){
			if (!includeIds) includeIds = [];
			return this.allThemes.filter((theme) => {
				for (let i = 0; i < this.themes.length; i++) {
					let pid = this.themes[i].theme_id;
					if (includeIds.indexOf(pid) == -1 && pid == theme.id) {
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
		'site-theme-editor': SiteThemeEditor,	
		'table-sort': TableSort,
	},
};

module.exports = SiteViewThemes;	
</script>

<template>
	<div>		
		
		<div v-if="mode=='edit'">
			<site-theme-editor
				mode="edit"
				:themes="availableThemes([themeToEdit.theme_id])"
				:data="themeToEdit"
				@submit="editTheme($event)"
				@close="mode='list'"
			></site-theme-editor>			
		</div>	
		
		<div v-if="mode=='add'">
			<site-theme-editor
				mode="add"
				:themes="availableThemes()"
				:data="{site_id: siteId}"
				@submit="addTheme($event)"
				@close="mode='list'"
			></site-theme-editor>
		</div>		
		
		<div v-if="mode=='list'">
			<span class="float-right">
				<button @click="mode='add'" class="btn btn-success">
					<i class="bi bi-pencil-square"></i>
					Add
				</button>
			</span>				
			
			<h3>Themes (total: {{ themes.length }})</h3>
		
			<table class="table table-bordered table-hover table-sm">
				<thead>
					<tr>
						<th>
							Title
							<table-sort
								col="theme_title"
								v-bind:current-col="sortCol"
								v-bind:current-dir="sortDir"
								v-on:sort-change="setSort($event[0], $event[1])"
							></table-sort>
						</th>
						<th>
							Parent
							<table-sort
								col="parent_title"
								v-bind:current-col="sortCol"
								v-bind:current-dir="sortDir"
								v-on:sort-change="setSort($event[0], $event[1])"
							></table-sort>
						</th>
						<th>
							Primary
							<table-sort
								col="is_primary"
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
					<tr v-for="theme in themes" v-bind:class="{ 'text-muted': theme.is_primary==0 }">
						<td>{{ theme.theme_title }}</td>
						<td>{{ theme.parent_title }}</td>
						<td>{{ theme.is_primary }}</td>
						<td>{{ theme.can_update }}</td>
						<td>
							<div class="btn-group btn-group-sm">
								<button class="btn btn-sm btn-primary" 
									title="Edit"
									@click="showEdit(theme)">
									<i class="bi bi-pencil-square"></i>
								</button>
								<button class="btn btn-small btn-danger" 
									title="Delete" 
									@click="deleteTheme(theme)">
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