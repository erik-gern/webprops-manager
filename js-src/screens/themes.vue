<script>
const api = require('../api');

const Themes = {
	data: function(){
		return {
			themes: [],
		};
	},
	created: function(){
	  	api.get('/themes').then((data) => {
	  		this.themes = data.records;
	  	});		
	},
};

module.exports = Themes;
</script>

<template>
<div>
	<h2>Themes</h2>
	<table class="table table-bordered table-hover table-sm">
		<thead>
			<tr>
				<th>ID</th>
				<th>Title</th>
				<th>Folder</th>
				<th>Description</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
			<tr v-for="theme in themes">
				<td>{{ theme.id }}</td>
				<td>{{ theme.title }}</td>
				<td>{{ theme.folder_name }}</td>
				<td>{{ theme.description }}</td>
				<td>[More]</td>
			</tr>
		</tbody>
	</table>
</div>
</template>