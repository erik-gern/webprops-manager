const formValidator = {
	data: function(){
		return {
			validation: {
				errors: [],
				dirty: {},
			},
		};
	},
	methods: {
		isValid: function(key) {
			return this.getValidationErrors(key).length == 0;
		},
		isDirty: function(key){
			return !!this.validation.dirty[key];
		},
		clearValidationErrors: function() {
			this.validation.errors = [];
		},
		addValidationError: function(key, message){
			this.validation.errors.push({ key, message });
		},
		getValidationErrors: function(key) {
			let errors = this.validation.errors.slice(0);
			if (!!key) {
				errors = errors.filter((e) => e.key == key)
			}
			return errors.map((e) => e.message);
		},
		markDirty: function(key){
			this.validation.dirty[key] = true;
		},
		onControlChange: function(key) {
			this.markDirty(key);
			this.validate();
		},
		onControlInput: function(key) {
			this.markDirty(key);
			this.validate();
		},
		onControlBlur: function(key) {
			this.markDirty(key);
			this.validate();
		},
	},
};

module.exports = formValidator;