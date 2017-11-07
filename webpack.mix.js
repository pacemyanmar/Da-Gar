const {mix} = require('laravel-mix');

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

mix.js('resources/assets/js/app.js', 'public/js').autoload({
    jquery: ['$', 'window.jQuery', 'jQuery'],
}).extract(['jquery','vue', 'bootstrap-sass', 'moment', 'hyperform',
    'datatables.net','datatables.net-bs',
    'datatables.net-buttons','datatables.net-buttons-bs',
    'datatables.net-buttons/js/buttons.colVis','gasparesganga-jquery-loading-overlay',
    'select2','select2/dist/js/i18n/en','admin-lte','jquery-ui','jquery-ui-sortable'])
    .sass('resources/assets/sass/app.scss', 'public/css')
    .version();

if (mix.inProduction()) {

    mix.version();

    mix.webpackConfig({
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
