const elixir = require('laravel-elixir');

require('laravel-elixir-vue');

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
    mix.styles([
    	
    	],'public/css/vendor.css','resources/assets/vendor/')
    	.scripts([
            // Order is important
            //'underscore/underscore-min.js',
            'jquery/dist/jquery.min.js',
            'jquery-ui/ui/minified/jquery-ui.min.js',
            'jquery-ui/ui/minified/jquery.ui.sortable.min.js',
            //'backbone/backbone.js',
            //'backbone-deep-model/distribution/deep-model.js',            
            'select2/dist/js/select2.min.js',
            'iCheck/icheck.min.js',
            'bootstrap/dist/js/bootstrap.min.js',
            'formBuilder/dist/form-builder.min.js',
            'formBuilder/dist/form-render.min.js',
            'adminlte/dist/js/app.min.js'
    		],'public/js/vendor.js','resources/assets/vendor/')
    	.sass('app.scss')
       	.webpack('app.js');
});
