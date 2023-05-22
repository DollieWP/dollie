<?php
add_filter('woocommerce_enable_setup_wizard', 'mywoo_disable_wizard');
function mywoo_disable_wizard()
{
    return false;
}
// Set Elementor Tracker Notice
update_option('elementor_tracker_notice', 1);

function ocdi_import_files()
{
    return [
        [
            'import_file_name' => 'Elementor Hub Starter',
            'categories' => ['Agencies', 'WaaS'],
            'import_file_url' => 'http://www.your_domain.com/ocdi/demo-content.xml',
            'import_widget_file_url' => 'http://www.your_domain.com/ocdi/widgets.json',
            'import_customizer_file_url' => 'http://www.your_domain.com/ocdi/customizer.dat',
            'import_redux' => [
                [
                    'file_url' => 'http://www.your_domain.com/ocdi/redux.json',
                    'option_name' => 'redux_option_name',
                ],
            ],
            'import_preview_image_url' => 'https://control-hq.s3.bhs.io.cloud.ovh.net/media/22506/conversions/media-libraryB2AHhl-full.jpg',
            'import_notice' => __('Please make sure you have an Elementor Pro license key to ensure updates and support.', 'your-textdomain'),
            'preview_url' => 'https://hub-elementor.wp-site.xyz/',
        ],
        [
            'import_file_name' => 'Bricks Builder Hub Starter',
            'categories' => ['Agencies', 'WaaS'],
            'import_file_url' => 'http://www.your_domain.com/ocdi/demo-content2.xml',
            'import_widget_file_url' => 'http://www.your_domain.com/ocdi/widgets2.json',
            'import_customizer_file_url' => 'http://www.your_domain.com/ocdi/customizer2.dat',
            'import_redux' => [
                [
                    'file_url' => 'http://www.your_domain.com/ocdi/redux.json',
                    'option_name' => 'redux_option_name',
                ],
                [
                    'file_url' => 'http://www.your_domain.com/ocdi/redux2.json',
                    'option_name' => 'redux_option_name_2',
                ],
            ],
            'import_preview_image_url' => 'https://control-hq.s3.bhs.io.cloud.ovh.net/media/22506/conversions/media-libraryB2AHhl-full.jpg',
            'import_notice' => __('Please make sure you have an Bricks Builder license key to ensure updates and support.', 'your-textdomain'),
            'preview_url' => 'https://hub-bricks.wp-site.xyz/',
        ],
    ];
}
add_filter('ocdi/import_files', 'ocdi_import_files');

function dollie_hub_plugin_setup($default_settings)
{
    $default_settings['parent_slug'] = 'dollie_setup';
    $default_settings['page_title'] = esc_html__('Setup Wizard', 'one-click-demo-import');
    $default_settings['menu_title'] = esc_html__('Import Hub Design', 'one-click-demo-import');
    $default_settings['capability'] = 'import';
    $default_settings['menu_slug'] = 'dollie-setup-wizard';

    return $default_settings;
}
add_filter('ocdi/plugin_page_setup', 'dollie_hub_plugin_setup');

function ocdi_plugin_intro_text($default_text)
{
    $default_text = '<div class="ocdi__intro-text"><p>Here you can choose from one our pre-made Hub design carefully crafted by our team based on popular WordPress page builders. <br>Please note that we do strongly recommend to have an active license for these solutions before you start building your Hub.</p></div>';

    return $default_text;
}
add_filter('ocdi/plugin_intro_text', 'ocdi_plugin_intro_text');

function my_text_strings($translated_text, $text, $domain)
{
    switch ($translated_text) {
        case 'One Click Demo Import':
            $translated_text = __('Dollie Hub Starter Designs', 'woocommerce');
            break;
        case 'Before We Import Your Demo':
            $translated_text = __('Before we import your starter Hub Design', 'woocommerce');
            break;
    }
    return $translated_text;
}
add_filter('gettext', 'my_text_strings', 20, 3);
add_filter('the_content', 'replace_text');

function ocdi_register_plugins($plugins)
{
    $theme_plugins = [];

    if (isset($_GET['step']) && $_GET['step'] === 'import' && isset($_GET['import'])) {
        //Elementor Blueprint
        if ($_GET['import'] === '0') {
            $theme_plugins = [
                [
                    'name' => 'Elementor',
                    'slug' => 'elementor',
                    'required' => true,
                ],
                [
                    'name' => 'Elementor Pro',
                    'slug' => 'elementor-pro',
                    'required' => true,
                    'source' => 'https://control.getdollie.com/releases/?action=get_metadata&slug=elementor-pro',
                ],
            ];
        }

        //Bricks Builder Blueprint
        if ($_GET['import'] === '1') {
            $theme_plugins = [
                [
                    'name' => 'Bricks Builder',
                    'slug' => 'bricks-builder',
                    'required' => true,
                    'source' => 'https://control.getdollie.com/releases/?action=get_metadata&slug=bricks-builder',
                ],
            ];
        }
    }

    return array_merge($plugins, $theme_plugins);
}
add_filter('ocdi/register_plugins', 'ocdi_register_plugins');
