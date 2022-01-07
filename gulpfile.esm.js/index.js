/* eslint-env es6 */
"use strict";

// External dependencies
import { series } from "gulp";

// Internal dependencies
import translate from "./translate";
import { tailwindBuild, tailwindWatch } from "./tailwind";
import build from "./build";

const release = series(tailwindBuild, tailwindWatch, translate, build);

export default release;

export { tailwindBuild, tailwindWatch, translate };
