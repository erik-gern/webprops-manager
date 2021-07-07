<script>
const AppNav = {
};

module.exports = AppNav;
</script>

<template>
<div class="navbar navbar-expand-lg navbar-light">
	<div class="container-fluid">
		<router-link class="navbar-brand" to="#">Web Properties Manager</router-link>
		<div class="collapse navbar-collapse" id="navbarNav">
			<ul class="navbar-nav">
				<li class="nav-item">
					<router-link class="nav-link" active-class="active" to="/">Dashboard</router-link>
				</li>
				<li class="nav-item">
					<router-link class="nav-link" active-class="active" to="/sites">Sites</router-link>
				</li>
				<li>
					<router-link class="nav-link" active-class="active" to="/nodemodules">Node Modules</router-link>
				</li>
				<li class="nav-item">
					<router-link class="nav-link" active-class="active" to="/users">Users</router-link>
				</li>
				<li class="nav-item">
					<router-link class="nav-link" active-class="active" to="/plugins">Plugins</router-link>
				</li>
				<li class="nav-item">
					<router-link class="nav-link" active-class="active" to="/themes">Themes</router-link>
				</li>
			</ul>
		</div>
	</div>
</div>
</template>