const Vue = require('vue');
const VueRouter = require('vue-router');
const Vuex = require('vuex');
const Vuelidate = require('vuelidate');
Vue.use(VueRouter);
Vue.use(Vuex);
Vue.use(Vuelidate);

const api = require('./api');
const AppNav = require('./widgets/app-nav.vue');
const Home = require('./screens/home.vue');
const NodeModuleList = require('./screens/nodemodules/nodemodule-list.vue');
const Plugins = require('./screens/plugins.vue');
const SiteAdd = require('./screens/sites/site-add.vue');
const SiteView = require('./screens/sites/site-view.vue');
const SiteList = require('./screens/sites/site-list.vue');
const Themes = require('./screens/themes.vue');
const Users = require('./screens/users.vue');

const storeModules = require('./store');

const routes = [
	{path: '/', component: Home},
	{path: '/nodemodules', component: NodeModuleList},
	{path: '/sites', component: SiteList},
	{path: '/sites/add', component: SiteAdd},
	{path: '/sites/:id', component: SiteView},
	{path: '/users', component: Users},
	{path: '/plugins', component: Plugins},
	{path: '/themes', component: Themes},
];

const router = new VueRouter({
	routes: routes,
});

const store = new Vuex.Store({
	modules: {...storeModules},
});

var app = new Vue({
	el: '#app',
	store: store,
	data: {
		title: 'Sites',
	},
	router: router,
	mounted: function(){
		window.setInterval(() => { this.$store.dispatch('util/ping'); }, 60000);
	},
	components: {
		'app-nav': AppNav,
	},
});