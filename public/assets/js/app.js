const routes = [
	{path: '/', component: Home},
	{path: '/sites', component: Sites},
	{path: '/sites/add', component: SiteAdd},
	{path: '/sites/:id', component: SiteViewer},
	{path: '/users', component: Users},
	{path: '/plugins', component: Plugins},
	{path: '/themes', component: Themes},
];

const router = new VueRouter({
	routes: routes,
});

var app = new Vue({
  el: '#app',
  data: {
    title: 'Sites',
  },
  router: router,
});