<script>
const CardFormContainer = {
	props: {
		'canSubmit': {
			type: Boolean,
			default: true,
		},
		'validationErrors': {
			type: Array,
			default: () => [],
		},
	},
	data: function(){
		return {
		}
	},
	created: function(){
	},
	methods: {
		doSubmit: function(){
			this.$emit('submit');
		},
		doCancel: function(){
			this.$emit('cancel');
		}
	}
};

module.exports = CardFormContainer;
</script>

<template>
<div class="card card-default mb-3">
	<div class="card-header">
		<slot name="header"></slot>
	</div>
	<div class="card-body">
		<ul v-if="validationErrors.length > 0" class="list-group mb-3">
			<li v-for="error in validationErrors" class="list-group-item list-group-item-danger">
				{{ error }}
			</li>
		</ul>
		<div>
			<slot></slot>
		</div>
		<div>
			<button @click="doSubmit" 
				class="btn btn-primary"
				:disabled="!canSubmit">
				<i class="bi bi-cloud-upload-fill"></i>
				Save
			</button>
			<button @click="doCancel"
				class="btn btn-warning">
				<i class="bi bi-arrow-return-left"></i>
				Cancel
			</button>
		</div>
	</div>
</div>
</template>