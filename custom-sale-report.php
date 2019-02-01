<?php
/*
Plugin Name: Custom Sale Report
Plugin URI:
Description: Individual sale report for WooCommerce
Version: 1.0
Author: Brendan Doyle
Author URI:
License: A "Slug" license name e.g. GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
define("REPORT_WOO_INCLUDES", plugin_dir_path(__DIR__) . 'woocommerce/includes/admin/');


if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {


    include_once(REPORT_WOO_INCLUDES . 'reports/class-wc-admin-report.php');

    include('ReportHelper.php');

    include("WC_Report_Orders_Detailed.php");

} else {

    add_action('admin_notices', 'custom_report_woocommerce_not_active');

}


function custom_report_woocommerce_not_active()
{
    ?>
    <div class="notice notice-error is-dismissible">
        <p><?php _e('Woocommerce needs to be installed and active for this report plugin to function.'); ?></p>
    </div>
    <?php
}