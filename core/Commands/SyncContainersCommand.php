<?php
/**
 * WP-CLI command for syncing containers.
 */

namespace Dollie\Core\Commands;

use WP_CLI_Command;

class SyncContainersCommand extends WP_CLI_Command
{
    /**
     * Sync containers using WP-CLI.
     *
     * ## EXAMPLES
     *
     * wp dollie sync-containers run
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function run()
    {
        // Instantiate SyncContainersJob and run the sync.
        $sync_job = \Dollie\Core\Jobs\SyncContainersJob::instance();
        $sync_job->run();

        // Output a success message.
        \WP_CLI::success('Containers synchronization completed successfully.');

    }
}
