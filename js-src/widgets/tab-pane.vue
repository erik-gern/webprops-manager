<script>
const TabPane = {
	props: {
		default: {
			'type': Boolean,
			'default': false,
		},
		title: {
			'type': String,
			'default': '',	
		},
	},
	data: function(){
		return {
			active: false,
		};
	},
	created: function(){
		console.log(this.$parent);
		this.$parent.registerTab(this);	
	},
};
module.exports = TabPane;
</script>

<template>
	<div 
		:class="{
			'tab-pane': true,
			'fade': true,
			'show': active,
			'active': active
		}" 
		role="tabpanel">
		<slot></slot>
	</div>
</template>