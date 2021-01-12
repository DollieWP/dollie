/* eslint-env es6 */
'use strict';

// Root path is where npm run commands happen
export const rootPath = process.env.INIT_CWD;

// PHPCS options
export const PHPCSOptions = {
	bin: `${rootPath}/vendor/bin/phpcs`,
	standard: `${rootPath}/phpcs.xml.dist`,
	warningSeverity: 0,
};

export const names = {
	PHPNamespace: 'Dollie',
	slug: 'dollie',
	name: 'Dollie',
	underscoreCase: 'dollie',
	constant: 'DOLLIE',
	camelCase: 'Dollie',
	camelCaseVar: 'dollie',
	author: 'Dollie',
};

// Project paths
let paths = {

	languages: {
		src: [
			`${rootPath}/**/*.php`,
			`!${rootPath}/optional/**/*.*`,
			`!${rootPath}/tests/**/*.*`,
			`!${rootPath}/vendor/**/*.*`,
			`!${rootPath}/dist/**/*.*`,
			`!${rootPath}/core/Extras/kirki/**/*.*`,
		],
		dest: `${rootPath}/languages/${names.slug}.pot`,
	}
};

export {paths};
