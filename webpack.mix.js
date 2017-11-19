const {mix} = require('laravel-mix');
const webpack = require('webpack');
let CommonsChunkPlugin = require('webpack/lib/optimize/CommonsChunkPlugin');
require('webpack/lib/ProvidePlugin')



if (mix.inProduction()) {

    mix.version();

    mix.webpackConfig({
        resolve: {
            alias: {
                d3: 'd3/build/d3.js'
            }
        },
        module: {
            rules: [{
                test: /\.js?$/,
                exclude: /node_modules(?!\/hyperform)|bower_components/,
                use: [{
                    loader: 'babel-loader',
                    options: mix.config.babel()
                }]
            }]
        }
    });
}

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/assets/js/app.js', 'public/js');
mix.autoload({
    jquery: ['$', 'window.jQuery', 'jQuery'],
    hyperform: ['hyperform'],
    d3: ['d3', 'window.d3', 'global.d3']
});
mix.extract(['jquery','vue', 'bootstrap-sass', 'moment', 'd3', 'hyperform',
    'datatables.net','datatables.net-bs',
    'datatables.net-buttons','datatables.net-buttons-bs',
    'datatables.net-buttons/js/buttons.colVis','datatables.net-buttons/js/buttons.print.js',
    'gasparesganga-jquery-loading-overlay',
    'select2','select2/dist/js/i18n/en','admin-lte','jquery-ui','jquery-ui-sortable','jquery-datetimepicker'])
    .sass('resources/assets/sass/app.scss', 'public/css')
    .version();

mix.scripts([
    'node_modules/formBuilder/dist/form-builder.min.js',
    'node_modules/formBuilder/dist/form-render.min.js'
],'public/js/formbuilder.js');

