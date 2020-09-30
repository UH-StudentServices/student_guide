'use strict';

import gulp from 'gulp';
import autoPrefixer from 'gulp-autoprefixer';
import bower from 'gulp-bower';
import del from 'del';
import sass from 'gulp-sass';
import globbing from 'node-sass-globbing';
import browserSync from 'browser-sync';
import eslint from 'gulp-eslint';

const rootDir = process.cwd();
const paths = {
  theme: `${rootDir}/themes/uhsg_theme`,
  modules: `${rootDir}/modules`
};

// const browserSyncProxyTarget = 'https://local.guide.student.helsinki.fi';
const browserSyncProxyTarget = 'https://local.studies-qa.it.helsinki.fi';

const sass_config = {
  importer: globbing,
  outputStyle: 'expanded',
  includePaths: [
    `${rootDir}/node_modules/normalize.css/`,
    `${rootDir}/node_modules/breakpoint-sass/stylesheets/`
  ]
};

// Compile sass.
gulp.task('sass', (done) => {
  process.chdir(paths.theme);
  gulp.src('sass/**/*.scss')
    .pipe(sass(sass_config).on('error', sass.logError))
    .pipe(autoPrefixer())
    .pipe(gulp.dest('css'));
    browserSync.reload();

  done();
});

gulp.task('watch', gulp.series('sass', () => {
  process.chdir(paths.theme);
  gulp.watch('sass/**/*.scss', gulp.series('sass'));
}));

gulp.task('bower', () => {
  process.chdir(paths.theme);
  return bower({ cmd: 'update'});
});

// Clean styleguide assets
gulp.task('styleguide-clean', () => {
  process.chdir(paths.theme);
  return del([
    'fonts/**/*',
    'sass/styleguide'
  ]);
});

// Updates styleguide with bower and moves relevant assets to correct path
gulp.task('styleguide-update', gulp.series('bower', 'styleguide-clean', (done) => {
  process.chdir(paths.theme);
  gulp.src('./bower_components/Styleguide/fonts/**/*')
    .pipe(gulp.dest('./fonts'));

  gulp.src(['./bower_components/Styleguide/sass/**/*',
    '!./bower_components/Styleguide/sass/styles.scss'],
    { base: './bower_components/Styleguide/sass' })
    .pipe(gulp.dest('./sass/styleguide'));

  done();
}));

// Live reload css changes
gulp.task('browsersync', gulp.series('watch', (done) => {
  process.chdir(paths.theme);
  browserSync.init({
    proxy: browserSyncProxyTarget,
    reloadDelay: 1000
  });

  done();
}));

// Linting
gulp.task('lint', () => {
  return gulp.src([`${paths.theme}/**/*.js`, `${paths.modules}/**/*.js`, '!**/node_modules/**', '!**/bower_components/**'])
    .pipe(eslint())
    .pipe(eslint.format())
    .pipe(eslint.failAfterError());
});
