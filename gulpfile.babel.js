/* eslint-env es6 */
'use strict';

// External dependencies
import {parallel, series} from 'gulp';

// Internal dependencies
import translate from './gulp/translate';
import build from './gulp/build';

const release = series( translate, build );

export default release;

export {
    translate
}
