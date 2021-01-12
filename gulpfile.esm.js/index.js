/* eslint-env es6 */
'use strict';

// External dependencies
import {series} from 'gulp';

// Internal dependencies
import translate from './translate';
import tailwind from './tailwind';
import build from './build';
import sftp from './sftp';

const release = series(tailwind, translate, build, sftp);

export default release;

export {
    tailwind, translate, build
}
