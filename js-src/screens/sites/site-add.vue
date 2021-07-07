<script>
const api = require('../../api');
const SiteEditor = require('../../forms/site-editor.vue');

const SiteAdd = {
	components: {
		'site-editor': SiteEditor,
	},	
	data: function(){
		return {
			site: {
				'has_2factor': 0,
			},
			isSubmitting: false,
			isSaved: false,
			isError: false,
			errors: [],
		};
	},
	created: function(){
	},
	methods: {
		addSite: function(siteData){
			this.isSubmitting = true;
			this.isError = false;
			this.isSaved = false;
			this.errors.splice(0, this.errors.slice.length);
			api.post('/sites', {}, siteData).then((resp) => {
				this.isSubmitting = false;
				if (resp.status == 'success') {
					let id;
					this.isSaved = true;
					id = resp.id;
					this.$router.push('/sites/'+id);
				}
				else {
					this.isError = true;
					if (resp.errors) {
						this.errors.push.apply(this.errors, resp.errors);
					}
				}
			}, (resp) => {
				this.isError = true;
			});
		},
		doClose: function() {
			this.$router.push('/sites');
		},
	},
};

module.exports = SiteAdd;
</script>

<template>
<div>
	<site-editor
		:mode="'add'"
		:site="site"
		:is-submitting="isSubmitting"
		:is-saved="isSaved"
		:is-error="isError"
		:errors="errors"
		@submit="addSite($event)"
		@close="doClose"
	></site-editor>
</div>
</template>