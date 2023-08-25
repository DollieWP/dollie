/* eslint-env es6 */
"use strict";

// External dependencies
const gulp = require("gulp");
const del = require("del");
const zip = require("gulp-zip");
const rename = require("gulp-rename");
const rsync = require("gulp-rsync");
const { exec } = require("child_process");



export function PushToLive() {
    return gulp.src('dst/dollie.zip')
        .pipe(rsync({
            root: 'dst/',
            hostname: '209.250.255.161',
            username: 'root',
            destination: '/var/www/manager/storage/app/packages',
            archive: true,
            silent: false,
            compress: true
        }));
}

export function PushToStaging() {
    return gulp.src('dst/dollie.zip')
        .pipe(rsync({
            root: 'dst/',
            hostname: '209.250.255.161',
            username: 'root',
            destination: '/var/www/manager/storage/app/packages/staging',
            archive: true,
            silent: false,
            compress: true
        }));
}


export function cleanFiles(cb) {
    return del("./dst/**/*", { force: true });
}

export function copyFiles() {
    return gulp
        .src(
            [
                "./**/*",
                "./[^.]*",
                "!./**/composer.json",
                "!./**/composer.lock",
                "!./**/package.json",
                "!./**/package-lock.json",
                "!./**/gulpfile.js",
                "!./**/gulpfile.babel.js",
                "!./**/prepros-6.config",
                "!./**/prepros.config",
                "!./**/Gruntfile.js",
                "!./**/README.md",
                "!./**/build.sh",
                "!node_modules",
                "!node_modules/**",
                "!**/node_modules{,/**}",
                "!./dst",
                "!./dst/**/*",
                "!./gulp",
                "!./gulp/**/*",
                "!./gulpfile.esm.js",
                "!./gulpfile.esm.js/**/*",
                "!**/*.scss",
                "!**/*.css.map",
                "!./tailwind.config.js",
            ],
            { base: "." }
        )
        .pipe(
            rename(function (file) {
                file.dirname = "dollie/" + file.dirname;
            })
        )
        .pipe(zip("dollie.zip"))
        .pipe(gulp.dest("dst/"));
}

export function triggerStagingRundeckJob(cb) {
    const RUNDECK_URL = "https://dollie-rundeck-staging.stratus5.net/";
    const RUNDECK_API_TOKEN = "vRjdEYQanKQ90bYwIxnIQHmt6KiFdF5s";
    const JOB_ID = "51cc196f-abb0-40d7-bb8f-a50087964219";

    const command = `curl -X POST --header "X-Rundeck-Auth-Token: ${RUNDECK_API_TOKEN}" ${RUNDECK_URL}api/41/job/${JOB_ID}/run`;

    exec(command, (err, stdout, stderr) => {
        if (err) {
            console.error(`An error occurred: ${err}`);
            return cb(err);
        }
        console.log(`Rundeck job triggered successfully`);
        console.log(`Response from Rundeck API: ${stdout}`);
        cb();
    });
}

export function triggerHubUpdatesRundeckJob(cb) {
    const RUNDECK_URL = "https://rundeck-dollie.stratus5.com/";
    const RUNDECK_API_TOKEN = "EpZ9k21jTirQ8VFbYBERA9hRA2fjfoN7";
    const JOB_ID = "ce25518e-a5c4-4640-8bb0-fcfbbd5750b5";

    const command = `curl -X POST --header "X-Rundeck-Auth-Token: ${RUNDECK_API_TOKEN}" ${RUNDECK_URL}api/41/job/${JOB_ID}/run`;

    exec(command, (err, stdout, stderr) => {
        if (err) {
            console.error(`An error occurred: ${err}`);
            return cb(err);
        }
        console.log(`Rundeck job triggered successfully`);
        console.log(`Response from Rundeck API: ${stdout}`);
        cb();
    });
}

// Just Build the Plugin
export const build = gulp.series(cleanFiles, copyFiles);

// Build and Push to Live
export const release = gulp.series(build, PushToLive, triggerHubUpdatesRundeckJob);

// Build and Push to Staging + Trigger Rundeck Job
export const staging = gulp.series(build, PushToStaging, triggerStagingRundeckJob);

// Register the tasks with Gulp
gulp.task('build', build);
gulp.task('staging', staging);
gulp.task('release', release);

//Just Rundeck
gulp.task('triggerHubUpdatesRundeckJob', triggerHubUpdatesRundeckJob);

export default build;
