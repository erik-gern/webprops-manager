<script>
const SiteThemeEditor = {
	props: {
		'mode': {
			type: String,
			default: 'edit',
		},
		'themes': {
			type: Array,
			default: function(){ return []; },
		},
		'data': {
			type: Object,
			default: function(){ return {}; },
		},
	},
	data: function(){
		return {
			myData: {},
		}
	},
	created: function(){
		this.myData = {
			theme_id: 0,
			is_primary: 1,
			can_update: 1,
			...this.data
		};
	},
	methods: {
		doSubmit: function(){
			this.$emit('submit', this.myData);
		},
		doClose: function(){
			this.$emit('close');
		}
	}
};

module.exports = SiteThemeEditor;
</script>

<template>
<div class="card card-default mb-3">
	<div class="card-header">
		{{ mode == 'add' ? 'Add' : 'Edit' }} Site/Theme
	</div>
	<div class="card-body">
		<div class="row mb-3">
			<div class="col-md-4">
				<select v-if="mode == 'add'"
					class="form-control" 
					v-model="myData.theme_id" 
					placeholder="Theme">
					<option v-for="theme in themes" v-bind:value="theme.id">
						{{ theme.title }}
					</option>
				</select>
				<span v-if="mode == 'edit'">{{ myData.theme_title }}</span>
			</div>
			<div class="col-md-3">
				<select class="form-control" v-model="myData.is_primary">
					<option :value="1">Primary</option>
					<option :value="0">Not Primary</option>
				</select>
			</div>
			<div class="col-md-3">
				<select class="form-control" v-model="myData.can_update">
					<option :value="1">Can Upgrade</option>
					<option :value="0">Do Not Upgrade</option>
				</select>
			</div>
			<div class="col-md-1">
				<div class="btn-group">
					<button @click="doSubmit" class="btn btn-primary">Save</button>
					<button @click="doClose" class="btn btn-warning">Cancel</button>
				</div>
			</div>
		</div>
	</div>
</div>
</template>