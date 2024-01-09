const mix = require('laravel-mix')
const path = require('path')

const directory = path.basename(path.resolve(__dirname))
const source = `platform/plugins/${directory}`
const dist = `public/vendor/core/plugins/${directory}`

mix
    .sass(`${source}/resources/sass/elfinder-integration.scss`, `${dist}/css`)
    .js(`${source}/resources/js/elfinder-integration.js`, `${dist}/js`)

if (mix.inProduction()) {
    mix
        .copy(`${dist}/css/elfinder-integration.css`, `${source}/public/css`)
        .copy(`${dist}/js/elfinder-integration.js`, `${source}/public/js`)
}
