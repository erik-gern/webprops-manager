const api = require('../api');

module.exports = recordsModule;

const LIST_REQUEST = 'LIST_REQUEST';
const LIST_SUCCESS = 'LIST_SUCCESS';
const LIST_ERROR = 'LIST_ERROR';

const CREATE_REQUEST = 'CREATE_REQUEST';
const CREATE_SUCCESS = 'CREATE_SUCCESS';
const CREATE_INVALID = 'CREATE_INVALID';
const CREATE_ERROR = 'CREATE_ERROR';

const DELETE_REQUEST = 'DELETE_REQUEST';
const DELETE_SUCCESS = 'DELETE_SUCCESS';
const DELETE_ERROR = 'DELETE_ERROR';

function recordsModule(name) {
	const urlStub = '/'+name;
	
	const module = {
		namespaced: true,
		state: function(){
			return {
				records: [],
				error: null,
				validationErrors: [],
				currentRecord: {},
			};
		},
		mutations: {
			[LIST_REQUEST]: function(state){
				state.records = [];
				state.error = null;
			},
			[LIST_SUCCESS]: function(state, records){
				state.records = records;
			},
			[LIST_ERROR]: function(state, error) {
				state.error = error;
			},
			[CREATE_REQUEST]: function(state, record) {
				state.error = null;
				state.validationErrors = null;
			},
			[CREATE_SUCCESS]: function(state, id) {
				state.currentRecord.id = id;
			},
			[CREATE_ERROR]: function(state, error){
				state.error = error;
			},
			[CREATE_INVALID]: function(state, validationErrors){
				state.validationErrors = validationErrors;
			},
			[DELETE_REQUEST]: function(state, id){
				//
			},
			[DELETE_SUCCESS]: function(state, id){
				// ??
			},
			[DELETE_ERROR]: function(){
				state.error = error;	
			},
		},
		actions: {
			list: async function(context, params){
				context.commit(LIST_REQUEST);
				try {
					const data = await api.get(urlStub, params);
					context.commit(LIST_SUCCESS, data.records);
				}
				catch (e) {
					context.commit(LIST_ERROR, e);
				}
			},
			create: async function(context, params) {
				context.commit(CREATE_REQUEST);
				try {
					const resp = await api.put(urlStub, params);
					if (resp.status == 'success') {
						context.commit(CREATE_SUCCESS, resp.id);					
					}
					else {
						context.commit(CREATE_INVALID, resp.errors);
					}
				}
				catch (e) {
					context.commit(CREATE_ERROR, e);
				}
			},
			update: async function(context, params) {},
			delete: async function(context, id) {
				context.commit(DELETE_REQUEST, id);
				try {
					const resp = await api.del(urlStub, params);
					context.commit(DELETE_SUCCESS, id);					
				}
				catch (e) {
					context.commit(DELETE_ERROR, e);
				}				
			},
		},
	};
	return module;
};
