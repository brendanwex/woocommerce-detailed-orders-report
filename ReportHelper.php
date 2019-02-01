<?php
/**
 * Created by PhpStorm.
 * User: BrendanDoyle
 * Date: 01/02/2019
 * Time: 09:44
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


class ReportHelper
{

    public function __construct()
    {
        add_filter('woocommerce_admin_reports', array($this, 'custom_woocommerce_admin_reports'), 10, 1);

        add_action("wp_ajax_custom_report_export", array($this, "custom_report_export"));

        add_action("admin_head", array($this, "helper_custom_admin_css"));

    }


    function custom_woocommerce_admin_reports($reports)
    {
        $order_detailed = array(
            'order_detailed' => array(
                'title' => 'Order Detailed',
                'description' => '',
                'hide_title' => true,
                'callback' => array($this, 'orders_detailed'),
            ),
        );
        $reports['orders']['reports'] = array_merge($reports['orders']['reports'], $order_detailed);
        return $reports;
    }

    function orders_detailed()
    {
        $report = new WC_Report_Orders_Detailed();
        $report->output_report();
    }


    function custom_report_export()
    {


        $results = $this->helper_report_main_query();

        $this->helper_csv_headers("data_export_" . date("Y-m-d") . ".csv");
        echo $this->helper_array_csv($results);

        wp_die();

    }


    function helper_report_main_query()
    {
        $current_range = !empty($_GET['range']) ? sanitize_text_field($_GET['range']) : '';
        $start_date = !empty($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
        $end_date = !empty($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '';

        $results = array();

        $args = array(
            'post_type' => 'shop_order',
            'posts_per_page' => -1,
            'post_status' => array('wc-completed', 'wc-processing'),
        );

        if ($current_range == "month") {

            $args['date_query'] = array(

                array(

                    'month' => date('m'),
                    'year' => date('Y')


                ),
            );
        }

        if ($current_range == "last_month") {

            $current_month = date("m");
            $current_year = date("Y");

            if ($current_month == "01") {
                $current_year = date("Y", strtotime('-1 years'));
            }


            $args['date_query'] = array(

                array(

                    'month' => date('m', strtotime('-1 months')),
                    'year' => $current_year

                ),
            );
        }
        if ($current_range == "year") {

            $args['date_query'] = array(

                array(

                    'year' => date('Y'),

                ),
            );
        }

        if ($current_range == "custom") {

            $args['date_query'] = array(

                array(
                    'after' => $_GET['start_date'],
                    'before' => $_GET['end_date'],
                    'inclusive' => true,
                ),
            );
        }

        $query = new WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();

                $order = new WC_Order(get_the_ID());

                $address = $order->get_billing_address_1() . " ";
                $address .= $order->get_billing_address_2() . " ";
                $address .= $order->get_billing_city();
                if (empty($order->get_billing_address_1())) {
                    $address = "None supplied or smart savers deposit";
                }

                $customer_name = $order->get_billing_first_name() . " " . $order->get_billing_last_name();

                if (empty($order->get_billing_first_name())) {
                    $customer_name = $order->get_billing_email();
                }


                $result = array('order_id' => $order->get_order_number(), 'order_date' => get_the_date(), 'customer' => $customer_name, 'total' => $order->get_total(), 'payment_method' => $order->get_payment_method(), 'address' => $address, 'status' => $order->get_status());

                array_push($results, $result);

            }
        }
        wp_reset_postdata();


        return $results;

    }

    function helper_array_csv(array &$array)
    {
        if (count($array) == 0) {
            return null;
        }
        ob_start();
        $df = fopen("php://output", 'w');
        fputcsv($df, array_keys(reset($array)));
        foreach ($array as $row) {
            fputcsv($df, $row);
        }
        fclose($df);
        return ob_get_clean();
    }

    function helper_csv_headers($filename)
    {
        // disable caching
        $now = gmdate("D, d M Y H:i:s");
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");

        // force download
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");

        // disposition / encoding on response body
        header("Content-Disposition: attachment;filename={$filename}");
        header("Content-Transfer-Encoding: binary");
    }


    function helper_custom_admin_css()
    {
        ?>

        <style>

            .woocommerce-reports-wide .postbox div.stats_range .export_csv_custom::before{
                font-family: Dashicons;
                speak: none;
                font-weight: 400;
                font-variant: normal;
                text-transform: none;
                line-height: 1;
                -webkit-font-smoothing: antialiased;
                content: "";
                text-decoration: none;
                margin-right: 4px;
            }


            .woocommerce-reports-wide .postbox div.stats_range .export_csv_custom, .woocommerce-reports-wide .postbox h3.stats_range .export_csv_custom, .woocommerce-reports-wrap .postbox div.stats_range .export_csv_custom, .woocommerce-reports-wrap .postbox h3.stats_range .export_csv_custom {
                float: right;
                line-height: 26px;
                border-left: 1px solid #dfdfdf;
                padding: 10px;
                display: block;
                text-decoration: none;
            }
        </style>

    <?php }

}

$reportHelper = new ReportHelper();