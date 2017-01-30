var gulp = require('gulp');
var autoPrefixer = require('gulp-autoprefixer');
var bower = require('gulp-bower');
var sass = require('gulp-sass');
var globbing = require('node-sass-globbing');
var browserSync = require('browser-sync').create();
var eslint = require('gulp-eslint');

var browserSyncProxyTarget = 'https://local.guide.student.helsinki.fi';

var sass_config = {
  importer: globbing,
  outputStyle: 'expanded',
  includePaths: [
    'node_modules/normalize.css/',
    'node_modules/breakpoint-sass/stylesheets/'
  ]
};

// Compile sass.
gulp.task('sass', function () {
  gulp.src('sass/**/*.scss')
    .pipe(sass(sass_config).on('error', sass.logError))
    .pipe(autoPrefixer({
      browsers: ['last 4 versions']
    }))
    .pipe(gulp.dest('css'));
    browserSync.reload();
});

gulp.task('watch', ['sass'], function () {
  gulp.watch('sass/**/*.scss', ['sass']);
});

gulp.task('bower', function() {
  return bower();
});

// Updates styleguide with bower and moves relevant assets to correct path
gulp.task('styleguide-update',['bower'], function() {
  gulp.src('./bower_components/Styleguide/fonts/**/*')
    .pipe(gulp.dest('./fonts'));

  gulp.src(['./bower_components/Styleguide/sass/**/*',
    '!./bower_components/Styleguide/sass/styles.scss'],
    { base: './bower_components/Styleguide/sass' })
    .pipe(gulp.dest('./sass/styleguide'));
});

// Live reload css changes
gulp.task('browsersync', ['watch'], function() {
  browserSync.init({
    proxy: browserSyncProxyTarget,
    reloadDelay: 1000
  });
});

// Linting
gulp.task('lint', function() {
  return gulp.src(['*.js', 'js/*.js', '!node_modules/**'])
    .pipe(eslint())
    .pipe(eslint.format())
    .pipe(eslint.failAfterError());
});
