/* eslint-env es6 */
"use strict";

import tailwindcss from "tailwindcss";
import autoprefixer from "autoprefixer";
import gulp from "gulp";
import gulpSourcemaps from "gulp-sourcemaps";
import sass from "gulp-sass";
import rename from "gulp-rename";
import csso from "gulp-csso";
import postCSS from "gulp-postcss";

const tailwindBuild = function (done) {
  return gulp
    .src("./assets/scss/dollie.scss")
    .pipe(gulpSourcemaps.init())
    .pipe(sass())
    .pipe(postCSS([tailwindcss, autoprefixer]))
    .pipe(gulpSourcemaps.write("."))
    .pipe(gulp.dest("./assets/css/"))
    .pipe(csso())
    .pipe(
      rename({
        suffix: ".min",
      })
    )
    .pipe(gulp.dest("./assets/css/"));
};

const tailwindWatch = function (done) {
  return gulp.watch(
    [
      "assets/scss/*.scss",
      "assets/scss/*/*.scss",
      "templates/**/*",
      "templates/**/**/*",
    ],
    tailwindBuild
  );
};

export { tailwindBuild, tailwindWatch };
