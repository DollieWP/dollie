/* eslint-env es6 */
'use strict';

// External dependencies
import {parallel, series} from 'gulp';

// Internal dependencies
import translate from './translate';
import build from './build';
import sftp from './sftp';

const release = series( translate, build, sftp );

export default release;

export {
    translate
}
