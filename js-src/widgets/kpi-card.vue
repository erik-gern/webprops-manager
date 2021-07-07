<script>
const bgColorToClass = {
	black: 'dark',
	blue: 'primary',
	grey: 'secondary',
	'light-grey': 'light',
	green: 'success',
	red: 'danger',
	yellow: 'warning',
};
const textColorToClass = {
	black: 'dark',
	dark: 'dark',
	light: 'light',
	white: 'light',	
};
	
module.exports = {
	props: {
		backgroundColor: {
			type: String,
			default: 'white',
		},
		textColor: {
			type: String,
			default: 'dark',
		},
	},
	methods: {
		getCardClasses: function() {
			let cls = ['card', 'mb-3'];
			if (bgColorToClass.hasOwnProperty(this.backgroundColor)) {
				cls.push('bg-' + bgColorToClass[this.backgroundColor]);
			}
			if (textColorToClass.hasOwnProperty(this.textColor)) {
				cls.push('text-' + textColorToClass[this.textColor]);
			}
			return cls;
		},
	},
};
</script>

<template>
<div :class="getCardClasses()">
	<div class="card-header">
		<slot name="title"></slot>
	</div>
	<div class="card-body">
		<p class="figure text-center"><strong><slot name="figure"></slot></strong></p>
	</div>
</div>
</template>

<style type="text/css">
.figure {
	font-size: 4em;
}
</style>