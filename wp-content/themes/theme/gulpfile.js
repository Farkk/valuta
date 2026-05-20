const { src, dest, series, parallel, watch } = require('gulp');
const browserSync = require('browser-sync').create();
const del = require('del');
const esbuild = require('gulp-esbuild');
const gulpIf = require('gulp-if');
const plumber = require('gulp-plumber');
const postcss = require('gulp-postcss');
const sassCompiler = require('sass');
const gulpSass = require('gulp-sass')(sassCompiler);
const sourcemaps = require('gulp-sourcemaps');
const autoprefixer = require('autoprefixer');
const cssnano = require('cssnano');

const isProduction = process.env.NODE_ENV === 'production';
const projectUrl = process.env.WP_URL || null;

const paths = {
  styles: {
    src: 'assets/scss/style.scss',
    watch: 'assets/scss/**/*.scss',
    dest: 'dist/css',
  },
  fonts: {
    src: 'assets/fonts/**/*.{ttf,woff2,woff,otf}',
    dest: 'dist/fonts',
  },
  scripts: {
    src: 'assets/js/app.js',
    watch: 'assets/js/**/*.js',
    dest: 'dist/js',
  },
  php: {
    watch: ['*.php', 'inc/**/*.php', 'template-parts/**/*.php', 'page-templates/**/*.php', 'virtual-pages/**/*.php'],
  },
};

function clean() {
  return del(['dist/css/**/*', 'dist/js/**/*', 'dist/fonts/**/*', 'dist/assets/**/*'], { force: true });
}

function styles() {
  const plugins = [autoprefixer()];

  if (isProduction) {
    plugins.push(cssnano());
  }

  return src(paths.styles.src, { sourcemaps: !isProduction })
    .pipe(plumber())
    .pipe(gulpIf(!isProduction, sourcemaps.init()))
    .pipe(
      gulpSass.sync({
        includePaths: ['assets/scss', 'node_modules'],
        outputStyle: isProduction ? 'compressed' : 'expanded',
      }).on('error', gulpSass.logError)
    )
    .pipe(postcss(plugins))
    .pipe(gulpIf(!isProduction, sourcemaps.write('.')))
    .pipe(dest(paths.styles.dest));
}

function fonts() {
  return src(paths.fonts.src, { allowEmpty: true }).pipe(dest(paths.fonts.dest));
}

function scripts() {
  return src(paths.scripts.src)
    .pipe(plumber())
    .pipe(
      esbuild({
        bundle: true,
        sourcemap: !isProduction,
        minify: isProduction,
        outfile: 'app.js',
        target: ['es2018'],
        format: 'iife',
        platform: 'browser',
        loader: {
          '.js': 'js',
        },
      })
    )
    .pipe(dest(paths.scripts.dest, { sourcemaps: '.' }));
}

function reload(done) {
  browserSync.reload();
  done();
}

function serve(done) {
  if (!projectUrl) {
    done();
    return;
  }

  browserSync.init({
    proxy: projectUrl,
    open: false,
    notify: false,
  });

  done();
}

function watcher() {
  watch(paths.styles.watch, styles);
  watch(paths.fonts.src, fonts);
  watch(paths.scripts.watch, scripts);

  if (projectUrl) {
    watch(paths.styles.watch, reload);
    watch(paths.scripts.watch, reload);
    watch(paths.php.watch, reload);
  }
}

const build = series((done) => {
  process.env.NODE_ENV = 'production';
  done();
}, clean, parallel(fonts, styles, scripts));

const dev = series((done) => {
  process.env.NODE_ENV = 'development';
  done();
}, clean, parallel(fonts, styles, scripts), serve, watcher);

exports.clean = clean;
exports.fonts = fonts;
exports.styles = styles;
exports.scripts = scripts;
exports.build = build;
exports.watch = dev;
exports.default = build;
