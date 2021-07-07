<script>
const SiteEditor = require('../../forms/site-editor.vue');

const SiteViewDetails = {
	props: {
		'publisher': {
			type: Object,
			required: true,
		},
		'siteId': {
			'type': Number,
			'default': 0,
		},
		'siteData': {
			'type': Object,
			'default': function(){ return {}; },
		},
	},
	data: function(){
		return {
			showEdit: false,
			isSubmitting: false,
			isSaved: false,
			isError: false,
			errors: [],
		};
	},
	mounted: function(){
		this.publisher.on('submit', this.onSubmit.bind(this));
		this.publisher.on('save', this.onSave.bind(this));
		this.publisher.on('error', this.onError.bind(this));
	},
	methods: {
		onSubmit: function(data){
			this.isSubmitting = true;
			this.isSaved = false;
			this.isError = false;
			this.errors.splice(0, this.errors.length);
		},
		onSave: function(data){
			this.isSubmitting = false;
			this.isSaved = true;
			this.isError = false;
			this.showEdit = false;
		},
		onError: function(data){
			this.isSubmitting = false;
			this.isSaved = false;
			this.isError = true;
			if (data.errors && data.errors.length > 0) {
				this.errors.push.apply(this.errors, data.errors);
			}
			else {
				this.errors.push('An unknown error occurred.');
			}
		},
		doEdit: function(data) {
			this.$emit('edit', data);
		},
	},
	components: {
		'site-editor': SiteEditor,
	},
};
module.exports = SiteViewDetails;
</script>

<template>
	<div>
		
		<div v-if="showEdit">
			<site-editor
				:mode="'edit'"
				:site="siteData"
				:is-submitting="isSubmitting"
				:is-saved="isSaved"
				:is-error="isError"
				:errors="errors"
				@submit="doEdit($event)"
				@close="showEdit=false"
			></site-editor>
		</div>
		
		<div v-if="!showEdit">
			<span class="float-right">
				<button v-on:click="showEdit=true" class="btn btn-primary">
					<i class="bi bi-pencil-square"></i>
					Edit
				</button>
			</span>
			
			<h2>{{ siteData.title }}</h2>
			<p>URL: <a v-bind:href="siteData.url" target="_blank">{{ siteData.url }}</a></p>
			<p>Admin: <a v-bind:href="siteData.admin_url" target="_blank">{{ siteData.admin_url }}</a></p>
			<p><i>{{ siteData.description }}</i></p>
			<table class="table table-bordered">
				<tbody>
					<tr>
						<td><b>ID</b></td>
						<td>{{ siteData.id }}</td>
					</tr>
					<tr>
						<td><b>Host</b></td>
						<td>{{ siteData.host }}</td>
					</tr>
					<tr>
						<td><b>PHP Version</b></td>
						<td>{{ siteData.php_version_major }}.{{ siteData.php_version_minor }}.{{ siteData.php_version_patch }}</td>
					</tr>
					<tr>
						<td><b>WordPress Version</b></td>
						<td>{{ siteData.wordpress_version_major }}.{{ siteData.wordpress_version_minor }}.{{ siteData.wordpress_version_patch }}</td>
					</tr>		
					<tr>	
						<td><b>2 Factor</b></td>
						<td>{{ siteData.has_2factor == 1 ? 'Yes' : 'No' }}</td>
					</tr>
					<tr>
						<td><b>SSL</b></td>
						<td>{{ siteData.ssl_provider }} / expires {{ siteData.ssl_expiry }}</td>
					</tr>
					<tr>
						<td><b>From Email</b></td>
						<td>"{{ siteData.email_from_name }}" &lt;{{ siteData.email_from_address }}&gt;</td>
					<tr>
					<tr>
						<td><b>Admin Email</b></td>
						<td>{{ siteData.admin_address }}</td>
					</tr>
					<tr>
						<td><b>Last Reviewed</b></td>
						<td>{{ siteData.last_reviewed }} ({{ siteData.lastreview_dayssince }} days ago)</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</template>