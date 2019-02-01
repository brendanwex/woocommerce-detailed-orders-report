<?php
/**
 * Created by PhpStorm.
 * User: BrendanDoyle
 * Date: 01/02/2019
 * Time: 09:50
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


class WC_Report_Orders_Detailed extends WC_Admin_Report
{

    /**
     * Output the report.
     */
    public function output_report()
    {


        $ranges = array(
            'year' => __('Year', 'woocommerce'),
            'last_month' => __('Last month', 'woocommerce'),
            'month' => __('This month', 'woocommerce'),
        );
        $current_range = !empty($_GET['range']) ? sanitize_text_field($_GET['range']) : 'year';
        if (!in_array($current_range, array('custom', 'year', 'last_month', '7day'))) {
            $current_range = 'month';
        }
        $this->check_current_range_nonce($current_range);
        $this->calculate_current_range($current_range);
        $hide_sidebar = true;
        include_once(REPORT_WOO_INCLUDES .'views/html-report-by-date.php');
    }


    /**
     * Output an export link.
     */
    public function get_export_button() {

        $current_range = !empty($_GET['range']) ? sanitize_text_field($_GET['range']) : 'year';
        $start_date = !empty($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
        $end_date = !empty($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '';
        $export_url = admin_url('admin-ajax.php?action=custom_report_export&range=' . $current_range . '&start_date=' . $start_date . '&end_date=' . $end_date);

        ?>
        <a href="<?php echo $export_url;?>" class="export_csv_custom">
            <?php _e( 'Export CSV', 'woocommerce' ); ?>
        </a>
        <?php
    }

    /**
     * Get the main chart.
     */
    public function get_main_chart()
    {


        global $reportHelper;

        $current_range = !empty($_GET['range']) ? sanitize_text_field($_GET['range']) : 'year';
        $start_date = !empty($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
        $end_date = !empty($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '';


        $results = $reportHelper->helper_report_main_query();


        ?>

        <table class="widefat">
            <thead>
            <tr>
                <th><strong>Order No.</strong></th>
                <th><strong>Order Date</strong></th>
                <th><strong>Customer</strong></th>
                <th><strong>Address</strong></th>

                <th><strong>Payment Method</strong></th>
                <th><strong>Payment Status</strong></th>

                <th><strong>Total</strong></th>
            </tr>
            </thead>
            <tbody>
            <?php

            foreach ($results as $order) {

                ?>
                <tr>
                    <td><?php echo $order['order_id']; ?></td>
                    <td><?php echo $order['order_date']; ?></td>
                    <td><?php echo $order['customer']; ?></td>
                    <td><?php echo $order['address']; ?></td>

                    <td><?php echo $order['payment_method']; ?></td>
                    <td><?php echo $order['status']; ?></td>

                    <td><?php echo $order['total']; ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <?php

    }
}
