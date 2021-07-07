<script>
const formValidator = require('../mixins/formValidator');
const isValidUrl = require('../validators/isValidUrl');
const CardFormContainer = require('../widgets/card-form-container.vue');

const SiteEditor = {
	mixins: [formValidator],
	components: {
		'card-form-container': CardFormContainer,
	},
	props: {
		'mode': {
			type: String,
			default: 'edit',
		},
		'site': {
			type: Object,
			default: () => {},
		},
		'isSubmitting': {
			type: Boolean,
			default: false,
		},
		'isSaved': {
			type: Boolean,
			default: false,	
		},
		'isError': {
			type: Boolean,
			default: false,	
		},
		'errors': {
			type: Array,
			default: () => [],	
		},
	},
	data: function(){
		return {
			myData: {},
		};
	},
	created: function(){
		this.myData = {...this.site};
		this.validate();
	},
	methods: {
		validate: function(){
			this.clearValidationErrors();
			if (!this.myData.title || this.myData.title.length == 0) {
				this.addValidationError('title', 'Title is required.');
			}
			if (!this.myData.url || this.myData.url.length == 0) {
				this.addValidationError('url', 'URL is required.');
			}
			else if (!isValidUrl(this.myData.url)) {
				this.addValidationError('url', 'URL must be a valid URL.');
			}
			if (!this.myData.admin_url || this.myData.admin_url.length == 0) {
				this.addValidationError('admin_url', 'Admin URL is required.');
			}
			else if (!isValidUrl(this.myData.admin_url)) {
				this.addValidationError('admin_url', 'Admin URL must be a valid URL.');
			}
			if (!this.myData.description || this.myData.description.length == 0) {
				this.addValidationError('description', 'Description is required.');
			}
			if (!this.myData.host || this.myData.host.length == 0) {
				this.addValidationError('host', 'Host is required.');
			}
			['php', 'wordpress'].forEach((software) => {
				let isFilled = true;
				const vKey = software + '_version';
				['major', 'minor', 'patch'].forEach((lvl) => {
					const key = vKey + '_' + lvl;
					if (!this.myData[key]) {
						isFilled = false;
					}
				});
				if (!isFilled) {
					this.addValidationError(vKey, vKey + ' is required.');
				}
			});
		},
		doSubmit: function(){
			this.$emit('submit', this.myData);	
		},
		doClose: function(){
			this.$emit('close');
		},
	},
};

module.exports = SiteEditor;
</script>

<template>
<card-form-container
	:can-submit="isValid()"
	:validation-errors="errors"
	@submit="doSubmit"
	@cancel="doClose">
	
	<template v-slot:header>
		{{ mode == 'add' ? 'Add' : 'Edit' }} Site
	</template>
	
	<div class="row mb-3">
		<label class="col-md-3 col-form-label">Title</label>
		<div class="col-md-6">
			<input :class="{
					'form-control': true,
					'is-invalid': isDirty('title') && !isValid('title')
				}" 
				type="text" 
				v-model="myData.title"
				@input="onControlInput('title')"
				@blur="onControlBlur('title')">
		</div>
		<div v-if="isDirty('title') && !isValid('title')" class="col-md-3">
			<div v-for="message in getValidationErrors('title')">{{ message }}</div>
		</div>
	</div>
	<div class="row mb-3">
		<label class="col-md-3 col-form-label">URL</label>
		<div class="col-md-6">
			<input :class="{
					'form-control': true,
					'is-invalid': isDirty('url') && !isValid('url')
				}" 
				type="text" 
				v-model="myData.url"
				@input="onControlInput('url')"
				@blur="onControlBlur('url')">
		</div>
		<div v-if="isDirty('url') && !isValid('url')" class="col-md-3">
			<div v-for="message in getValidationErrors('url')">{{ message }}</div>
		</div>
	</div>
	<div class="row mb-3">
		<label class="col-md-3 col-form-label">Admin URL</label>
		<div class="col-md-6">
			<input :class="{
					'form-control': true,
					'is-invalid': isDirty('admin_url') && !isValid('admin_url')
				}" 
				type="text" 
				v-model="myData.admin_url"
				@input="onControlInput('admin_url')"
				@blur="onControlBlur('admin_url')">
		</div>
		<div v-if="isDirty('admin_url') && !isValid('admin_url')" class="col-md-3">
			<div v-for="message in getValidationErrors('admin_url')">{{ message }}</div>
		</div>
	</div>
	<div class="row mb-3">
		<label class="col-md-3 col-form-label">Description</label>
		<div class="col-md-6">
			<textarea :class="{
					'form-control': true,
					'is-invalid': isDirty('description') && !isValid('description')
				}" 
				v-model="myData.description"
				@input="onControlInput('description')"
				@blur="onControlBlur('description')"
			></textarea>
		</div>
		<div v-if="isDirty('description') && !isValid('description')" class="col-md-3">
			<div v-for="message in getValidationErrors('description')">{{ message }}</div>
		</div>
	</div>
	<div class="row mb-3">
		<label class="col-md-3 col-form-label">Last Reviewed</label>
		<div class="col-md-3">
			<input type="date" class="form-control" v-model="myData.last_reviewed">
		</div>
	</div>		
	<div class="row mb-3">
		<label class="col-md-3 col-form-label">Host</label>
		<div class="col-md-6">
			<input :class="{
					'form-control': true,
					'is-invalid': isDirty('host') && !isValid('host')
				}" 
				type="text" 
				v-model="myData.host"
				@input="onControlInput('host')"
				@blur="onControlBlur('host')">
		</div>
		<div v-if="isDirty('host') && !isValid('host')" class="col-md-3">
			<div v-for="message in getValidationErrors('host')">{{ message }}</div>
		</div>
	</div>

	<div v-for="softwarePair in [['php', 'PHP'], ['wordpress', 'WordPress']]"
		class="row mb-3">
		<label class="col-md-3 col-form-label">{{ softwarePair[1] }} Version</label>
		<div class="col-md-4">
			<div class="input-group">
				<template v-for="lvl in ['major', 'minor', 'patch']">
					<input :class="{
							'form-control': true,
							'is-invalid': isDirty(softwarePair[0]+'_version') 
								&& !isValid(softwarePair[0]+'_version')
						}"
						type="number"
						min="0"
						v-model="myData[softwarePair[0]+'_version_'+lvl]"
						:placeholder="lvl"
						@input="onControlInput(softwarePair[0]+'_version')"
						@blur="onControlBlur(softwarePair[0]+'_version')">
					<span v-if="lvl != 'patch'" class="input-group-text">.</span>
				</template>
			</div>
		</div>
		<div v-if="isDirty(softwarePair[0]+'_version') && !isValid(softwarePair[0]+'_version')"
			:class="['col-md-3', 'off'+'set-md-2']">
			<div v-for="message in getValidationErrors(softwarePair[0]+'_version')">{{ message }}</div>
		</div>
	</div>

	<div class="row mb-3">
		<label class="col-md-3 col-form-label">2-Factor Enabled</label>
		<div class="col-md-6">
			<div class="form-check form-check-inline">
				<input type="radio" 
					class="form-check-input" 
					id="has_2factor_yes" 
					v-bind:value="1"
					v-model="myData.has_2factor">
				<label class="form-check-label" for="has_2factor_yes">Yes</label>
			</div>
			<div class="form-check form-check-inline">
				<input type="radio" 
					class="form-check-input" 
					id="has_2factor_no" 
					v-bind:value="0"
					v-model="myData.has_2factor">
				<label class="form-check-label" for="has_2factor_no">No</label>
			</div>
		</div>
	</div>
	<div class="row mb-3">
		<label class="col-md-3 col-form-label">SSL</label>
		<div class="col-md-3">
			<input type="date" class="form-control" v-model="myData.ssl_expiry" placeholder="Expiration">
		</div>
		<div class="col-md-3">
			<input type="text" class="form-control" v-model="myData.ssl_provider" placeholder="Provider">
		</div>
	</div>
	<div class="row mb-3">
		<label class="col-md-3 col-form-label">Email Name</label>
		<div class="col-md-6">
			<input type="text" class="form-control" v-model="myData.email_from_name" placeholder="Name">
		</div>
	</div>
	<div class="row mb-3">
		<label class="col-md-3 col-form-label">Email Address</label>
		<div class="col-md-6">
			<input type="text" class="form-control" v-model="myData.email_from_address" placeholder="Address">
		</div>
	</div>
	<div class="row mb-3">
		<label class="col-md-3 col-form-label">Admin Email</label>
		<div class="col-md-6">
			<input class="form-control" type="text" v-model="myData.admin_address">
		</div>
	</div>	
	
	<pre>{{validation | json}}</pre>	

</card-form-container>
</template>