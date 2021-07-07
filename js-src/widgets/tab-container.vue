<script>
const TabContainer = {
	data: function(){
		return {
			tabs: [],
			activeTab: null,
		};
	},
	created: function(){
		this.activeTab = this.default;
	},
	methods: {
		registerTab: function(tab){
			this.tabs.push(tab);
			if (tab.default) {
				this.activateTab(tab);
			}
		},
		activateTab: function(tab){
			this.tabs.forEach((t) => { t.active = false; });
			tab.active = true;
		},
	},
	components: {
	},
};
module.exports = TabContainer;
</script>

<template>
<div>
	<ul class="nav nav-tabs mb-3" role="tablist">
		<li v-for="tab in tabs" class="nav-item" role="presentation">
			<button 
				:class="{
					'nav-link': true,
					'active': tab.active
				}" 
				@click="activateTab(tab)"
				type="button" 
				role="tab">
				{{ tab.title }}
			</button>
		</li>
	</ul>
	<div class="tab-content">
		<slot></slot>			
	</div>		
</div>
</template>