/* eslint-env es6 */
'use strict';

// External dependencies
const gulp = require('gulp');
const del = require('del');

function cleanDist() {
    return del('./dist/**/*', {force:true});
}
function copyFiles(cb) {
    gulp.src([
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
        '!**/*.scss',
        '!**/*.css.map'

    ], {base: '.'})
        .pipe(gulp.dest('dist'));
    cb();
}


const build = gulp.series(
    cleanDist, copyFiles
);

export default build;