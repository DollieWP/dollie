<?php

namespace Dollie\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

interface ConstInterface {
	const
		TYPE_SITE      = '0',
		TYPE_BLUEPRINT = '1',
		TYPE_STAGING   = '2';

	const
		ACTION_START   = 'start',
		ACTION_STOP    = 'stop',
		ACTION_RESTART = 'restart';

	const PANEL_SLUG = 'wpd_platform_setup';



	const
		EXECUTION_STAGING_SYNC            = 'staging.sync.to.live',
		EXECUTION_BACKUP_CREATE           = 'backup.create',
		EXECUTION_BACKUP_APPLY            = 'backup.apply',
		EXECUTION_BACKUP_RESTORE          = 'backup.restore',
		EXECUTION_BACKUP_CREDENTIALS      = 'backup.credentials.change',
		EXECUTION_BLUEPRINT_CREATE        = 'blueprint.create',
		EXECUTION_BLUEPRINT_DEPLOY        = 'blueprint.deploy',
		EXECUTION_BLUEPRINT_AFTER_DEPLOY  = 'blueprint.after.deploy',
		EXECUTION_CHANGE_USER_ROLE        = 'change.user.role',
		EXECUTION_DYNAMIC_FIELDS_CHECK    = 'dynamic.fields.check',
		EXECUTION_DYNAMIC_FIELDS_REPLACE  = 'dynamic.fields.replace',
		EXECUTION_DOMAIN_UPDATE           = 'domain.update',
		EXECUTION_DOMAIN_APPLY_CLOUDFLARE = 'domain.apply.cloudflare',
		EXECUTION_PLUGIN_GET_UPDATES      = 'plugins.get.updates',
		EXECUTION_PLUGIN_APPLY_UPDATES    = 'plugins.apply.updates',
		EXECUTION_WIZARD_SETUP            = 'wizard.setup';
}
