<script>
const api = require('../api');

const Users = {
	data: function(){
		return {
			users: [],
		};
	},
	created: function(){
	  	api.get('/users').then((data) => {
	  		this.users = data.records;
	  	});		
	},
};

module.exports = Users;
</script>

<template>
<div>
	<h2>Users</h2>
	<table class="table table-bordered table-hover table-sm">
		<thead>
			<tr>
				<th>ID</th>
				<th>Email</th>
				<th>Last Name</th>
				<th>First Name</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
			<tr v-for="user in users">
				<td>{{ user.id }}</td>
				<td>{{ user.emailaddress }}</td>
				<td>{{ user.name_last }}</td>
				<td>{{ user.name_first}}</td>
				<td>[More]</td>
			</tr>
		</tbody>
	</table>
</div>
</template>