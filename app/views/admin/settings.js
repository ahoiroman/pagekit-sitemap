window.settings = {

    el: '#settings',

    data: {
        config: $data.config
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
            this.$notify('Sitemap-generation is in progress. Please stand by until the "Sitemap generated"-message shows up.', {status:'warning', timeout: 0});
            this.$http.post('/admin/sitemap/generate').then(function (data) {
                    this.$notify('Sitemap generated.', {status:'success', timeout: 0});
                }, function (data) {
                    this.$notify(data, 'danger');
                }
            );
        }

    },
    components: {}
};

Vue.ready(window.settings);
