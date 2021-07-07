<script>
const KPICard = require('../widgets/kpi-card.vue');

const Home = {
	data: function(){
		return {
		};
	},
	created: function(){
	  	this.$store.dispatch('stats/refresh');
	},
	computed: {
		stats: function () { return this.$store.state.stats; },
	},
	components: {
		'kpi-card': KPICard,
	},
};

module.exports = Home;
</script>

<template>
<div>
	<div class="row">
		<div class="col-md-4">
			<kpi-card background-color="blue" text-color="white">
				<template v-slot:title>Sites</template>
				<template v-slot:figure>{{ stats.sites }}</template>
			</kpi-card>
		</div>
		<div class="col-md-4">
			<kpi-card background-color="red" text-color="white">
				<template v-slot:title>Node Modules</template>
				<template v-slot:figure>{{ stats.nodemodules }}</template>
			</kpi-card>
		</div>
		<div class="col-md-4">
			<kpi-card background-color="green" text-color="white">
				<template v-slot:title>Plugins</template>
				<template v-slot:figure>{{ stats.plugins }}</template>
			</kpi-card>
		</div>
	</div>
	<div class="row">
		<div :class="['col-md-4','off'+'set-md-2']">
			<kpi-card background-color="yellow" text-color="dark">
				<template v-slot:title>Themes</template>
				<template v-slot:figure>{{ stats.themes }}</template>			
			</kpi-card>
		</div>
		<div class="col-md-4">
			<kpi-card background-color="grey" text-color="light">
				<template v-slot:title>Users</template>
				<template v-slot:figure>{{ stats.users }}</template>			
			</kpi-card>
		</div>
	</div>
</div>
</template>