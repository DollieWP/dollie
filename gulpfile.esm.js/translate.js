/* eslint-env es6 */
'use strict';

// External dependencies
import {src, dest} from 'gulp';
import pump from 'pump';
import sort from 'gulp-sort';
import wpPot from 'gulp-wp-pot';

// Internal dependencies
import {paths, names} from './constants';

/**
 * Generate translation files.
 */
export default function translate(done) {

	return pump([
        src(paths.languages.src),
        sort(),
        wpPot({
            domain: names.slug,
            package: names.name,
            bugReport: names.name,
            lastTranslator: names.author
        }),
        dest(paths.languages.dest),
    ], done);
}
