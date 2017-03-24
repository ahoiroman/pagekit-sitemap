/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};

/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {

/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId])
/******/ 			return installedModules[moduleId].exports;

/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			exports: {},
/******/ 			id: moduleId,
/******/ 			loaded: false
/******/ 		};

/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);

/******/ 		// Flag the module as loaded
/******/ 		module.loaded = true;

/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}


/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;

/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;

/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";

/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ function(module, exports) {

	window.settings = {

	    el: '#settings',

	    data: {
	        config: $data.config,
	        progress: false
	    },

	    methods: {

	        save: function () {

	            this.$http.post('admin/sitemap/save', {config: this.config}, function () {
	                this.$notify('Settings saved.');
	            }).error(function (data) {
	                this.$notify(data, 'danger');
	            });
	        },
	        add: function add(e) {

	            e.preventDefault();
	            if (!this.newExclusion || this.urlMatch(this.newExclusion)) return;

	            this.config.excluded.push(this.newExclusion);
	            this.newExclusion = ''
	        },
	        urlMatch: function (url) {
	            return this.config.excluded.filter(function (result) {
	                    return result == url;
	                }).length > 0;
	        },
	        remove: function (exclusion) {
	            this.config.excluded.$remove(exclusion);
	        },
	        generate: function () {
	            this.progress = true;
	            this.$notify('Sitemap-generation is in progress. Please stand by until the "Sitemap generated"-message shows up.', {status:'warning', timeout: 0});
	            this.$http.post('/admin/sitemap/generate').then(function (data) {
	                    this.$notify('Sitemap generated.', {status:'success', timeout: 0});
	                    this.progress = false;
	                }, function (data) {
	                    this.$notify(data, 'danger');
	                    this.progress = false;
	                }
	            );
	        }

	    },
	    components: {}
	};

	Vue.ready(window.settings);


/***/ }
/******/ ]);