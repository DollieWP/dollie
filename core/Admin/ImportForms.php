<?php

namespace Dollie\Core\Admin;

if (!defined('ABSPATH')) {
    exit(); // Exit if accessed directly.
}

use Dollie\Core\Log;
use Dollie\Core\Singleton;

/**
 * Class ImportGravityForms
 *
 * @package Dollie\Core\Admin
 */
class ImportForms extends Singleton
{
    /**
     * Current plugin forms version
     *
     * @var string
     */
    private $forms_version = '5.0.0';

    /**
     * Option name that gets saved in the options database table.
     *
     * @var string
     */
    private $option_name = 'dollie_forms_version';

    /**
     * Current plugin forms
     *
     * @var array
     */
    private $forms = [
        'form_dollie_create_backup',
        'form_dollie_delete_site',
        'form_dollie_domain_connect',
        'form_dollie_launch_site',
        'form_dollie_quick_launch',
        'form_dollie_list_backups',
        'form_dollie_plugin_updates',
        'form_dollie_performance',

        // Onboarding.
        'form_dollie_agency_onboarding',
    ];

    /**
     * Check if an update is needed
     *
     * @return bool
     */
    public function needs_update()
    {
        // Check for database version
        $db_version = get_option($this->option_name) ?: '1.0.0';

        // If we need an update
        if (version_compare($this->forms_version, $db_version, '>')) {
            return true;
        }

        return false;
    }

    /**
     * Create or update all forms with the new data
     *
     * @return bool
     */
    /**
     * Create or update all forms with the new data
     *
     * @param bool $force Set to true to force the update even if not needed.
     * @return bool
     */
    public function import_forms($force = false)
    {
        if (!class_exists('AF')) {
            return false;
        }

        if (!current_user_can('manage_options')) {
            return false;
        }

        // If we don't need to update and $force is not true.
        if (!$force && !$this->needs_update()) {
            return true;
        }

        $success = true;

        if ( ! function_exists('af_import_form') ) {
            $import_class = DOLLIE_CORE_PATH . 'Extras/advanced-forms/api/api-import-export.php';
            require_once $import_class;
        }

        foreach ($this->forms as $form_slug) {
            $path = DOLLIE_CORE_PATH . 'Extras/forms/' . $form_slug . '.json';

            if (file_exists($path)) {
                $form = file_get_contents($path);

                if ($form) {
                    $form = json_decode($form, true);
                    if (!empty($form)) {
                        // Import the form
                        $result = af_import_form($form);

                        if (false === $result || is_wp_error($result)) {
                            $success = false;
                            Log::add('Forms import error');
                        }
                    }
                }
            }
        }

        if (true === $success) {
            Log::add('Forms successfully imported');
            update_option($this->option_name, $this->forms_version);
        }

        return $success;
    }
}
