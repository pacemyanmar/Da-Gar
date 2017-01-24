const elixir = require('laravel-elixir');

require('laravel-elixir-vue-2');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(mix => {
    // by using versioning on js and css files, all dependencies related files and directory should be in "build" dir.
    mix.copy('resources/assets/vendor/bootstrap/dist/fonts', 'public/build/fonts')
        .copy('resources/assets/fonts/NotoSansMyanmar', 'public/build/fonts')
        .copy('resources/assets/vendor/font-awesome/fonts', 'public/build/fonts')
        .copy('resources/assets/vendor/ionicons/fonts','public/build/fonts')
        .copy('resources/assets/vendor/adminlte/img', 'public/build/img')
        .copy('resources/assets/vendor/datatables/media/images', 'public/build/images')
        .copy('resources/assets/vendor/ion-checkradio/img', 'public/build/img')
        .copy('resources/assets/js/buttons.server-side.js', 'public/vendor/datatables/buttons.server-side.js')
        .copy('resources/assets/images', 'public/images')
        .styles([
            //'bootstrap/dist/css/bootstrap.min.css',
            //'bootstrap/dist/css/bootstrap-theme.min.css',
            'font-awesome/css/font-awesome.min.css',
            'select2/dist/css/select2.css',
            'adminlte/dist/css/AdminLTE.min.css',
            'adminlte/dist/css/skins/_all-skins.min.css',
            'ionicons/css/ionicons.min.css',
            '../css/form-builder.css',
            'formBuilder/dist/form-render.min.css',
            'datatables/media/css/dataTables.bootstrap.min.css',
            'datatables.net-buttons-bs/css/buttons.bootstrap.min.css',
            'magic-check/css/magic-check.min.css',
    	],'public/css/vendor.css','resources/assets/vendor/')
    	.scripts([
            // Order is important
            //'underscore/underscore-min.js',
            'jquery/dist/jquery.min.js',
            'jquery-migrate-official/dist/jquery-migrate.min.js',
            'jquery-ui/jquery-ui.min.js',
            'jquery-ui-sortable/jquery-ui-sortable.min.js',
            '../js/tooltip-conflict.js', // resolve conflict between jquery-ui and bootstrap
            'bootstrap/dist/js/bootstrap.min.js',
            //'backbone/backbone.js',
            //'backbone-deep-model/distribution/deep-model.js',
            'select2/dist/js/select2.min.js',
            '../js/form-builder.js',
            //'../js/form-render.js',
            //'formBuilder/dist/form-builder.min.js',
            'formBuilder/dist/form-render.min.js',
            'datatables/media/js/jquery.dataTables.min.js',
            'datatables/media/js/dataTables.bootstrap.min.js',
            'datatables.net-buttons/js/dataTables.buttons.min.js',
            'datatables.net-buttons/js/buttons.colVis.min.js',
            'datatables.net-buttons/js/buttons.flash.min.js',
            'datatables.net-buttons/js/buttons.html5.min.js',
            'datatables.net-buttons/js/buttons.print.min.js',
            'datatables.net-buttons-bs/js/buttons.bootstrap.min.js',
            'moment/min/moment-with-locales.min.js',
            'adminlte/dist/js/app.min.js',
            'hyperform/dist/hyperform.min.js'
    		],'public/js/vendor.js','resources/assets/vendor/')
    	.sass('app.scss')
       	.webpack('app.js')
        .version(["public/css/app.css","public/css/vendor.css","public/js/app.js","public/js/vendor.js"]);
        Elixir.webpack.mergeConfig({
            module: {
                loaders: [{
                    test: /\.jsx?$/,
                    loader: 'babel',
                    exclude: /node_modules(?!\/(vue-tables-2|vue-pagination-2))/
                }]
            }
        });
});
