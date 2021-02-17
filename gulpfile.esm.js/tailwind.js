/* eslint-env es6 */
"use strict";

const gulp = require("gulp"),
  sourcemaps = require("gulp-sourcemaps"),
  sass = require("gulp-sass"),
  rename = require("gulp-rename"),
  csso = require("gulp-csso"),
  postcss = require("gulp-postcss");

export default function tailwind(done) {
  return gulp
    .src("./assets/scss/dollie.scss")
    .pipe(sourcemaps.init())
    .pipe(sass())
    .pipe(
      postcss([
        require("tailwindcss")("./tailwind.config.js"),
        require("autoprefixer"),
      ])
    )
    .pipe(sourcemaps.write("."))
    .pipe(gulp.dest("./assets/css/"))
    .pipe(csso())
    .pipe(
      rename({
        suffix: ".min",
      })
    )
    .pipe(gulp.dest("./assets/css/"));
}
