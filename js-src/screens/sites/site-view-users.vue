<script>
const SiteUserEditor = require('../../forms/site-user-editor.vue');
const TableSort = require('../../widgets/table-sort.vue');

const SiteViewUsers = {
	props: {
		'siteId': {
			'type': Number,
			'default': 0,
		},
		'users': {
			'type': Array,
			'default': function(){ return [] },
		},
		'allUsers': {
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
			userToEdit: null,
		};
	},
	created: function(){
	},
	methods: {
		showEdit: function(user) {
			this.userToEdit = {...user};
			this.mode = 'edit';
		},
		add: function(data) {
			this.mode = 'list';
			this.$emit('add', data);
		},
		edit: function(data) {
			this.mode = 'list';
			this.$emit('edit', data);
		},
		del: function(data){
			// confirm deletion
			let userToDelete = this.allUsers.filter((p) => { 
				return parseInt(p.id) == parseInt(data.user_id);
			})[0];
			let userTitle = [
				userToDelete.name_first,
				userToDelete.name_last,
				'('+userToDelete.emailaddress+')',
			].join(' ');
			if (!window.confirm("Are you sure you want to remove the association with the user "+userTitle+"?")) {
				return;
			}
			
			// emit event to parent
			this.$emit('delete', data);
		},
		availableUsers: function(includeIds){
			if (!includeIds) includeIds = [];
			return this.allUsers.filter((user) => {
				for (let i = 0; i < this.users.length; i++) {
					let pid = this.users[i].user_id;
					if (includeIds.indexOf(pid) == -1 && pid == user.id) {
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
		'site-user-editor': SiteUserEditor,	
		'table-sort': TableSort,
	},
};

module.exports = SiteViewUsers;	
</script>

<template>
	<div>		
		
		<div v-if="mode=='edit'">
			<site-user-editor
				mode="edit"
				:users="availableUsers([userToEdit.user_id])"
				:data="userToEdit"
				@submit="edit($event)"
				@close="mode='list'"
			></site-user-editor>			
		</div>	
		
		<div v-if="mode=='add'">
			<site-user-editor
				mode="add"
				:users="availableUsers()"
				:data="{site_id: siteId}"
				@submit="add($event)"
				@close="mode='list'"
			></site-user-editor>
		</div>		
		
		<div v-if="mode=='list'">
			<span class="float-right">
				<button @click="mode='add'" class="btn btn-success">
					<i class="bi bi-pencil-square"></i>
					Add
				</button>
			</span>				
			
			<h3>Users (total: {{ users.length }})</h3>
		
			<table class="table table-bordered table-hover table-sm">
				<thead>
					<tr>
						<th>
							Username
							<table-sort
								col="username"
								v-bind:current-col="sortCol"
								v-bind:current-dir="sortDir"
								v-on:sort-change="setSort($event[0], $event[1])"
							></table-sort>
						</th>
						<th>
							Email
							<table-sort
								col="emailaddress"
								v-bind:current-col="sortCol"
								v-bind:current-dir="sortDir"
								v-on:sort-change="setSort($event[0], $event[1])"
							></table-sort>
						</th>
						<th>
							Last Name
							<table-sort
								col="name_last"
								v-bind:current-col="sortCol"
								v-bind:current-dir="sortDir"
								v-on:sort-change="setSort($event[0], $event[1])"
							></table-sort>
						</th>
						<th>
							First Name
							<table-sort
								col="name_first"
								v-bind:current-col="sortCol"
								v-bind:current-dir="sortDir"
								v-on:sort-change="setSort($event[0], $event[1])"
							></table-sort>
						</th>
						<th>
							Roles
							<table-sort
								col="role"
								v-bind:current-col="sortCol"
								v-bind:current-dir="sortDir"
								v-on:sort-change="setSort($event[0], $event[1])"
							></table-sort>
						</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="user in users" v-bind:class="{ 'text-muted': user.is_active==0 }">
					<td>{{ user.username }}</td>
					<td>{{ user.emailaddress }}</td>
					<td>{{ user.name_last }}</td>
					<td>{{ user.name_first }}</td>
					<td>{{ user.role }}</td>
						<td>
							<div class="btn-group btn-group-sm">
								<button class="btn btn-sm btn-primary" 
									title="Edit"
									@click="showEdit(user)">
									<i class="bi bi-pencil-square"></i>
								</button>
								<button class="btn btn-small btn-danger" 
									title="Delete" 
									@click="del(user)">
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