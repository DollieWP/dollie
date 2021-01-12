/* eslint-env es6 */
'use strict';

// External dependencies
const gulp = require('gulp');
const del = require('del');
const zip = require('gulp-zip');
const rename = require('gulp-rename');

export function cleanFiles(cb) {
    return del('./dist/**/*', {force: true});
    cb();
}

export function copyFiles() {
    return gulp.src([
        './**/*',
        './[^.]*',
        '!./**/composer.json',
        '!./**/composer.lock',
        '!./**/package.json',
        '!./**/package-lock.json',
        '!./**/gulpfile.js',
        '!./**/gulpfile.babel.js',
        '!./**/prepros-6.config',
        '!./**/prepros.config',
        '!./**/Gruntfile.js',
        '!./**/README.md',
        '!./**/build.sh',
        '!node_modules',
        '!node_modules/**',
        '!**/node_modules{,/**}',
        '!./dist',
        '!./dist/**/*',
        '!./gulp',
        '!./gulp/**/*',
        '!./gulpfile.esm.js',
        '!./gulpfile.esm.js/**/*',
        '!**/*.scss',
        '!**/*.css.map',
        '!./tailwind.config.js'

    ], {base: '.'})
        .pipe(rename(function(file) {
            file.dirname = 'dollie/' + file.dirname;
        }))
        .pipe(zip('dollie.zip'))
        .pipe(gulp.dest('dist/'));
}

const build = gulp.series(
    cleanFiles, copyFiles
);

export default build;
