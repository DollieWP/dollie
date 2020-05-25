/* eslint-env es6 */
'use strict';

// External dependencies
const gulp = require('gulp');
var rsync = require('gulp-rsync');

export default function sendFiles(cb) {
    gulp.src('dist/dollie.zip')
        .pipe(rsync({
            root: 'dist',
            hostname: '95.179.250.250', //api.getdollie.com
            port: '2086',
            username: 'root',
            destination: '/var/www/api.getdollie.com/htdocs/shared/storage/app/packages'
        }));
    return cb();
}
