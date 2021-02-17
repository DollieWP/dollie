/* eslint-env es6 */
"use strict";

// External dependencies
import { series } from "gulp";

// Internal dependencies
import translate from "./translate";
import tailwind from "./tailwind";
import build from "./build";

const release = series(tailwind, translate, build);

export default release;

export { tailwind, translate };
