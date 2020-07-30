<?php

namespace Dollie\Core\Jobs;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Log;
use Dollie\Core\Modules\ContainerManagement;

/**
 * Class SyncContainerJob
 * @package Dollie\Core\Jobs
 */
class SyncContainersJob extends \WP_Background_Process {

	/**
	 * @var string
	 */
	protected $action = 'sync_containers_job';

	/**
	 * Define task
	 *
	 * @param mixed $item
	 *
	 * @return bool|mixed
	 */
	protected function task( $item ) {
		ContainerManagement::instance()->sync_containers();

		return false;
	}

	/**
	 * On complete
	 */
	protected function complete() {
		parent::complete();

		Log::add( 'Containers synced successfully.' );
	}

}
