<script>
const TableSort = {
	props: ['col', 'currentCol', 'currentDir'],
	data: function(){
		return {
			myCol: '',
		};
	},
	created: function(){
		this.myCol = this.col;
	},
	methods: {
		doSort: function(){
			let dir;
			if (this.currentCol != this.myCol) {
				dir = 'ASC';
			}
			else {
				if (this.currentDir == 'ASC') {
					dir = 'DESC';
				}
				else {
					dir = 'ASC';
				}
			}
			this.$emit('sort-change', [this.myCol, dir]);
		},
	}
};

module.exports = TableSort;
</script>

<template>
<a v-on:click="doSort" 
	v-bind:class="{
		'text-primary': currentCol==myCol, 
		'text-muted': currentCol!=myCol, 
		'text-small': true
	}">
	<i class="bi bi-arrow-down-right-square" 
		v-if="currentCol==myCol && currentDir =='DESC'"></i>
	<i class="bi bi-arrow-up-right-square" 
		v-if="(currentCol==myCol && currentDir =='ASC') || currentCol != myCol"></i>
</a>
</template>