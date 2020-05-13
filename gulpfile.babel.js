/* eslint-env es6 */
'use strict';

// External dependencies
import {parallel, series} from 'gulp';

// Internal dependencies
import translate from './gulp/translate';
import build from './gulp/build';
import sftp from './gulp/sftp';

const release = series( translate, build, sftp );

export default release;

export {
    translate
}
