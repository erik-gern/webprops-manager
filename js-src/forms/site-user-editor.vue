<script>
const CardFormContainer = require('../widgets/card-form-container.vue');
const formValidator = require('../mixins/formValidator');
	
const SiteUserEditor = {
	mixins: [formValidator],
	components: {
		'card-form-container': CardFormContainer,
	},	
	props: {
		'mode': {
			type: String,
			default: 'edit',
		},
		'users': {
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
		};
	},
	created: function(){
		this.myData = {
			user_id: 0,
			role: 'Administrator',
			...this.data
		};
		this.validate();
	},
	methods: {
		validate: function(){
			this.clearValidationErrors();
			if (!this.myData.user_id || this.myData.user_id == 0) {
				this.addValidationError('user_id', 'User is required.');
			}
			if (!this.myData.username || this.myData.username.length == 0) {
				this.addValidationError('username', 'Username is required.');
			}
			if (!this.myData.role || this.myData.role.length == 0) {
				this.addValidationError('role', 'Roles is required.');
			}
		},
		onSubmit: function(){
			this.$emit('submit', this.myData);
		},
		onCancel: function(){
			this.$emit('close');
		},
	},
};

module.exports = SiteUserEditor;
</script>

<template>
<card-form-container 
	:can-submit="isValid()" 
	:validation-errors="[]"
	@submit="onSubmit" 
	@cancel="onCancel">
	<template v-slot:header>
		{{ mode == 'add' ? 'Add' : 'Edit' }} Site/User
	</template>
	
	<div class="row mb-3">
		<label for="user-editor__user" class="col-md-3 col-form-label">User</label>
		<div class="col-md-6">
			<select v-if="mode=='add'"
				:class="{
					'form-control': true, 
					'is-invalid': isDirty('user_id') && !isValid('user_id')
				}" 
				id="user-editor__user"
				v-model="myData.user_id"
				@change="onControlChange('user_id')"
				@blur="onControlBlur('user_id')">
				<option v-for="user in users" v-bind:value="user.id">
					{{ user.name_first }} {{ user.name_last }} ({{ user.emailaddress }})
				</option>
			</select>
			<input v-if="mode=='edit'"
				type="text" 
				class="form-control"
				disabled 
				:value="myData.name_first + ' ' + myData.name_last + ' (' + myData.emailaddress + ')'">
		</div>
		<div class="col-md-3" v-if="isDirty('user_id') && !isValid('user_id')">
			<div v-for="message in getValidationErrors('user_id')">{{ message }}</div>
		</div>		
	</div>
	<div class="row mb-3">
		<label for="user-editor__username" class="col-md-3 col-form-label">Username</label>
		<div class="col-md-6">
			<input type="text" 
				id="user-editor__username"
				:class="{
					'form-control': true, 
					'is-invalid': isDirty('username') && !isValid('username')
				}"
				v-model="myData.username"
				@input="onControlInput('username')"
				@blur="onControlBlur('username')">
		</div>
		<div class="col-md-3" v-if="isDirty('username') && !isValid('username')">
			<div v-for="message in getValidationErrors('username')">{{ message }}</div>
		</div>		
	</div>
	<div class="row mb-3">
		<label for="user-editor__roles" class="col-md-3 col-form-label">Roles</label>
		<div class="col-md-6">
			<input type="text" 
				id="user-editor__roles"
				:class="{
					'form-control': true, 
					'is-invalid': isDirty('role') && !isValid('role')
				}"
				v-model="myData.role"
				@input="onControlInput('role')"
				@blur="onControlBlur('role')">
		</div>
		<div class="col-md-3" v-if="isDirty('role') && !isValid('role')">
			<div v-for="message in getValidationErrors('role')">{{ message }}</div>
		</div>		
	</div>
	
</card-form-container>
</template>