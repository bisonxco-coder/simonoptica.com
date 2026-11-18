const mix = require('laravel-mix');
const path = require('path');
const service = require('./webpack.mix.service.js')

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

// Compile main scss with merged app.js file
mix.js('resources/js/app.js', 'public/js')
    .vue({ version: 3 })
    .webpackConfig({
        resolve: {
            alias: {
                '@': path.resolve(__dirname, 'resources/js/'),
                styles: path.join(__dirname, 'resources/sass/') 
            },
        }
    })
    .sass('resources/sass/app.scss', 'public/css', [])
    

// Plugin scss files
const scssFiles = service.getFiles('vendor/lensware', /\app.scss$/)
for (scssFile of scssFiles){
    mix.sass(scssFile, 'public/css', [])
}

// Plugin js files
const jsFiles = service.getFiles('vendor/lensware', /\app.js$/)
for (jsFile of jsFiles){
    mix.js(jsFile, 'public/js')
        .vue({ version: 3 })
        .webpackConfig({
            resolve: {
                modules: [path.resolve(__dirname, 'node_modules'), 'node_modules']
            }
        })
}

mix.version();