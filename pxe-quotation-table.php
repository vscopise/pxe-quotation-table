<?php

/**
 * Plugin Name:       PXE Quotation Table
 * Plugin URI:        
 * Description:       A quotation table
 * Version:           1.0.0
 * Author:            Pixie
 * Author URI:        http://www.pixie.com.uy/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       pxe-quotation-table
 * Domain Path:       /languages/
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class PXE_Quotation_Table
{
    protected static $instance = null;

    protected $slug;

    protected $version;

    public function __construct()
    {
        add_shortcode('pxe-quotation-table', __CLASS__ . '::quotation_table_callback');
        add_action('wp_enqueue_scripts', __CLASS__ . '::enqueue_scripts');

        add_action('wp_ajax_pxe_quotation_action', __CLASS__ . '::quotation_action');
        add_action('wp_ajax_nopriv_pxe_quotation_action', __CLASS__ . '::quotation_action');
    }

    public function quotation_action() {
        $response = get_option('pxe_quotation_table_data');;
        wp_send_json_success( $response );
    }

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public static function enqueue_scripts()
    {
        if (shortcode_exists('pxe-quotation-table')) {

            wp_enqueue_style('pxe_quotation_table', plugins_url('/assets/pxe-quotation-table.css', __FILE__));
            wp_enqueue_script('pxe_quotation_table', plugins_url('/assets/pxe-quotation-table.js', __FILE__), array('jquery'));
            wp_localize_script('pxe_quotation_table', 'pxe_quotation_table_ajax_object', array(
                'admin_url'   => admin_url('admin-ajax.php'),
            ));
        }
    }


    public static function quotation_table_callback()
    {
        ob_start();

        include_once plugin_dir_path(__FILE__) . '/assets/pxe-quotation-table-template.php';

        $output = ob_get_clean();
        return $output;
    }

    public static function activated()
    {
        if (!wp_next_scheduled('pxe_quotation_api_cron_hook')) {
            wp_schedule_event(time(), 'twicedaily', 'pxe_quotation_api_cron_hook');
        }
    }

    public static function deactivated()
    {
        wp_clear_scheduled_hook('pxe_quotation_api_cron_hook');
    }

    public static function plugin_setup()
    {
        add_action('pxe_quotation_api_cron_hook', __CLASS__ . '::get_server_quotation_data');
    }

    public function get_server_quotation_data() {
        $url_base = 'https://cotizaciones-brou.herokuapp.com/api';
        $request = wp_remote_get($url_base . '/currency/latest');
        if (!is_wp_error($request)) {
            $body = wp_remote_retrieve_body($request);
            $quotation_data = json_decode($body);
            update_option('pxe_quotation_table_data', $quotation_data);
        }
    }
}

add_action('plugins_loaded', array(PXE_Quotation_Table::get_instance(), 'plugin_setup'));
register_activation_hook(__FILE__, array('PXE_Quotation_Table', 'activated'));
register_deactivation_hook(__FILE__, array('PXE_Quotation_Table', 'deactivated'));