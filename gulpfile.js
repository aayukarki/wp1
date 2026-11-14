/**
 * MWS theme — asset pipeline.
 *
 *   gulp build          one-off production build (minified, no sourcemaps)
 *   gulp                build once, then watch src/ and the PHP templates
 *
 * Nothing is fetched from a CDN at runtime. Swiper, AOS and both font families
 * are pulled out of node_modules at build time and written into dist/, which is
 * what functions.php enqueues.
 *
 * Four outputs, vendor kept separate from application code so a change to our
 * own CSS or JS does not invalidate the cached vendor bundle:
 *
 *   dist/css/vendor.min.css   Swiper + AOS stylesheets
 *   dist/css/main.min.css     Tailwind build + theme styles
 *   dist/js/vendor.min.js     Swiper + AOS (provide the globals main.js uses)
 *   dist/js/main.min.js       theme behaviour
 *   dist/fonts/*              self-hosted woff2/woff
 */

const { src, dest, series, parallel, watch } = require('gulp');
const postcss    = require('gulp-postcss');
const concat     = require('gulp-concat');
const terser     = require('gulp-terser');
const tailwindcss = require('tailwindcss');
const autoprefixer = require('autoprefixer');
const cssnano    = require('cssnano');
const atImport   = require('postcss-import');

/* `gulp build` sets this, so the watch build keeps sourcemaps and the
   production build does not ship them. */
const production = process.argv.includes('build');

const paths = {
    css:   'src/css/main.css',
    js:    'src/js/main.js',
    watchCss: 'src/css/**/*.css',
    watchJs:  'src/js/**/*.js',
    /* Tailwind scans these for class names, so a template edit has to trigger
       a CSS rebuild too — otherwise a newly used utility is missing from the
       compiled sheet. */
    templates: ['./*.php', './inc/**/*.php', './template-parts/**/*.php'],

    vendorCss: [
        'node_modules/swiper/swiper-bundle.min.css',
        'node_modules/aos/dist/aos.css',
    ],
    /* Order matters: Swiper first so `var Swiper` is defined before main.js
       runs, AOS second. Both expose browser globals. */
    vendorJs: [
        'node_modules/swiper/swiper-bundle.min.js',
        'node_modules/aos/dist/aos.js',
    ],
    /* Icons are real .svg files masked by CSS (no inline SVG anywhere), so the
       compiled sheet in dist/css needs them one level up at dist/icons. */
    icons: 'assets/icons/*.svg',

    fonts: [
        'node_modules/@fontsource/anton/files/anton-latin-400-normal.woff2',
        'node_modules/@fontsource/corben/files/corben-latin-400-normal.woff2',
        'node_modules/@fontsource/corben/files/corben-latin-700-normal.woff2',
    ],
};

/* Gulp's built-in sourcemap support, rather than gulp-sourcemaps — both
   gulp-postcss and gulp-terser hand maps back through it. */
const maps = production ? {} : { sourcemaps: true };
const mapsOut = production ? {} : { sourcemaps: '.' };

/** Tailwind + autoprefixer + cssnano → dist/css/main.min.css */
function css() {
    return src(paths.css, maps)
        .pipe(postcss([
            atImport(),
            tailwindcss('./tailwind.config.js'),
            autoprefixer(),
            cssnano({ preset: ['default', { discardComments: { removeAll: true } }] }),
        ]))
        .pipe(concat('main.min.css'))
        .pipe(dest('dist/css', mapsOut));
}

/** Swiper + AOS stylesheets → dist/css/vendor.min.css */
function vendorCss() {
    return src(paths.vendorCss, { allowEmpty: false })
        .pipe(postcss([
            autoprefixer(),
            cssnano({ preset: ['default', { discardComments: { removeAll: true } }] }),
        ]))
        .pipe(concat('vendor.min.css'))
        .pipe(dest('dist/css'));
}

/** Theme behaviour → dist/js/main.min.js */
function js() {
    return src(paths.js, maps)
        .pipe(terser({ format: { comments: false } }))
        .pipe(concat('main.min.js'))
        .pipe(dest('dist/js', mapsOut));
}

/**
 * Swiper + AOS → dist/js/vendor.min.js
 *
 * Both sources are already minified, so terser only runs to normalise them
 * into one file. `compress: false` keeps it quick and avoids re-mangling
 * code that is not ours.
 */
function vendorJs() {
    return src(paths.vendorJs, { allowEmpty: false })
        .pipe(terser({ compress: false, mangle: false, format: { comments: false } }))
        .pipe(concat('vendor.min.js'))
        .pipe(dest('dist/js'));
}

/** Self-hosted font files → dist/fonts */
function fonts() {
    return src(paths.fonts, { encoding: false, allowEmpty: false })
        .pipe(dest('dist/fonts'));
}

/** Icon .svg files → dist/icons (referenced by the mask rules in icons.css) */
function icons() {
    return src(paths.icons, { encoding: false, allowEmpty: false })
        .pipe(dest('dist/icons'));
}

const build = parallel(series(vendorCss, css), series(vendorJs, js), fonts, icons);

function watchFiles() {
    watch(paths.watchCss, css);
    watch(paths.templates, css);   // new utility classes in markup → recompile
    watch(paths.watchJs, js);
    watch(paths.icons, icons);
}

exports.css = css;
exports.js = js;
exports.fonts = fonts;
exports.icons = icons;
exports.build = build;
exports.watch = series(build, watchFiles);
exports.default = series(build, watchFiles);
