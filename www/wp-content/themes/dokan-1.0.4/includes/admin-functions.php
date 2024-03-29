<?php

/**
 * Filter all the shop orders to remove child orders
 *
 * @param WP_Query $query
 */
function dokan_admin_shop_order_remove_parents( $query ) {
    if ( $query->is_main_query() && $query->query['post_type'] == 'shop_order' ) {
        $query->set( 'orderby', 'ID' );
        $query->set( 'order', 'DESC' );
    }
}

add_action( 'pre_get_posts', 'dokan_admin_shop_order_remove_parents' );

/**
 * Remove child orders from WC reports
 *
 * @param array $query
 * @return array
 */
function dokan_admin_order_reports_remove_parents( $query ) {

    $query['where'] .= ' AND posts.post_parent = 0';

    return $query;
}

add_filter( 'woocommerce_reports_get_order_report_query', 'dokan_admin_order_reports_remove_parents' );

/**
 * Change the columns shown in admin.
 * 
 * @param array $existing_columns
 * @return array
 */
function dokan_admin_shop_order_edit_columns( $existing_columns ) {
    $columns = array();

    $columns['cb']               = '<input type="checkbox" />';
    $columns['order_status']     = '<span class="status_head tips" data-tip="' . esc_attr__( 'Status', 'dokan' ) . '">' . esc_attr__( 'Status', 'dokan' ) . '</span>';
    $columns['order_title']      = __( 'Order', 'dokan' );
    $columns['order_items']      = __( 'Purchased', 'dokan' );
    $columns['shipping_address'] = __( 'Ship to', 'dokan' );

    $columns['customer_message'] = '<span class="notes_head tips" data-tip="' . esc_attr__( 'Customer Message', 'dokan' ) . '">' . esc_attr__( 'Customer Message', 'dokan' ) . '</span>';
    $columns['order_notes']      = '<span class="order-notes_head tips" data-tip="' . esc_attr__( 'Order Notes', 'dokan' ) . '">' . esc_attr__( 'Order Notes', 'dokan' ) . '</span>';
    $columns['order_date']       = __( 'Date', 'dokan' );
    $columns['order_total']      = __( 'Total', 'dokan' );
    $columns['order_actions']    = __( 'Actions', 'dokan' );
    $columns['seller']        = __( 'Seller', 'dokan' );
    $columns['suborder']        = __( 'Sub Order', 'dokan' );

    return $columns;
}

add_filter( 'manage_edit-shop_order_columns', 'dokan_admin_shop_order_edit_columns', 11 );

/**
 * Adds custom column on dokan admin shop order table
 *
 * @global type $post
 * @global type $woocommerce
 * @global WC_Order $the_order
 * @param type $col
 */
function dokan_shop_order_custom_columns( $col ) {
    global $post, $woocommerce, $the_order;

    if ( empty( $the_order ) || $the_order->id != $post->ID ) {
        $the_order = new WC_Order( $post->ID );
    }

    switch ($col) {
        case 'order_title':
            if ($post->post_parent !== 0) {
                echo '<strong>';
                echo __( 'Sub Order of', 'dokan' );
                printf( ' <a href="%s">#%s</a>', admin_url( 'post.php?action=edit&post=' . $post->post_parent ), $post->post_parent );
                echo '</strong>';
            }
            break;

        case 'suborder':
            $has_sub = get_post_meta( $post->ID, 'has_sub_order', true );

            if ( $has_sub == '1' ) {
                printf( '<a href="#" class="show-sub-orders" data-class="parent-%1$d" data-show="%2$s" data-hide="%3$s">%2$s</a>', $post->ID, __( 'Show Sub-Orders', 'dokan' ), __( 'Hide Sub-Orders', 'dokan' ));
            }
            break;

        case 'seller':
            $has_sub = get_post_meta( $post->ID, 'has_sub_order', true );

            if ( $has_sub != '1' ) {
                $seller = get_user_by( 'id', $post->post_author );
                printf( '<a href="%s">%s</a>', admin_url( 'edit.php?post_type=shop_order&author=' . $seller->ID ), $seller->display_name );
            }

            break;
    }
}

add_action( 'manage_shop_order_posts_custom_column', 'dokan_shop_order_custom_columns', 11 );

/**
 * Adds css classes on admin shop order table
 *
 * @global WP_Post $post
 * @param array $classes
 * @param int $post_id
 * @return array
 */
function dokan_admin_shop_order_row_classes( $classes, $post_id ) {
    global $post;

    if ( $post->post_type == 'shop_order' && $post->post_parent != 0 ) {
        $classes[] = 'sub-order parent-' . $post->post_parent;
    }

    return $classes;
}

add_filter( 'post_class', 'dokan_admin_shop_order_row_classes', 10, 2);

/**
 * Show/hide sub order css/js
 *
 * @return void
 */
function dokan_admin_shop_order_scripts() {
    ?>
    <script type="text/javascript">
    jQuery(function($) {
        $('tr.sub-order').hide();

        $('a.show-sub-orders').on('click', function(e) {
            e.preventDefault();

            var $self = $(this),
                el = $('tr.' + $self.data('class') );

            if ( el.is(':hidden') ) {
                el.show();
                $self.text( $self.data('hide') );
            } else {
                el.hide();
                $self.text( $self.data('show') );
            }
        });

        $('button.toggle-sub-orders').on('click', function(e) {
            e.preventDefault();

            $('tr.sub-order').toggle();
        });
    });
    </script>

    <style type="text/css">
        tr.sub-order {
            background: #ECFFF2;
        }
    </style>
    <?php
}

add_action( 'admin_footer-edit.php', 'dokan_admin_shop_order_scripts' );

/**
 * Delete sub orders when parent order is trashed
 *
 * @param int $post_id
 */
function dokan_admin_on_trash_order( $post_id ) {
    $post = get_post( $post_id );

    if ( $post->post_type == 'shop_order' && $post->post_parent == 0 ) {
        $sub_orders = get_children( array( 'post_parent' => $post_id, 'post_type' => 'shop_order' ) );

        if ( $sub_orders ) {
            foreach ($sub_orders as $order_post) {
                wp_trash_post( $order_post->ID );
            }
        }
    }
}

add_action( 'wp_trash_post', 'dokan_admin_on_trash_order' );

/**
 * Untrash sub orders when parent orders are untrashed
 *
 * @param int $post_id
 */
function dokan_admin_on_untrash_order( $post_id ) {
    $post = get_post( $post_id );

    if ( $post->post_type == 'shop_order' && $post->post_parent == 0 ) {
        $sub_orders = get_children( array( 'post_parent' => $post_id, 'post_type' => 'shop_order' ) );

        if ( $sub_orders ) {
            foreach ($sub_orders as $order_post) {
                wp_untrash_post( $order_post->ID );
            }
        }
    }
}

add_action( 'wp_untrash_post', 'dokan_admin_on_untrash_order' );


/**
 * Delete sub orders and from dokan sync table when a order is deleted
 *
 * @param int $post_id
 */
function dokan_admin_on_delete_order( $post_id ) {
    $post = get_post( $post_id );

    if ( $post->post_type == 'shop_order' ) {
        dokan_delete_sync_order( $post_id );

        $sub_orders = get_children( array( 'post_parent' => $post_id, 'post_type' => 'shop_order' ) );

        if ( $sub_orders ) {
            foreach ($sub_orders as $order_post) {
                wp_delete_post( $order_post->ID );
            }
        }
    }
}

add_action( 'delete_post', 'dokan_admin_on_delete_order' );

/**
 * Show a toggle button to toggle all the sub orders
 *
 * @global WP_Query $wp_query
 */
function dokan_admin_shop_order_toggle_sub_orders() {
    global $wp_query;

    if ( isset( $wp_query->query['post_type'] ) && $wp_query->query['post_type'] == 'shop_order' ) {
        echo '<button class="toggle-sub-orders button">' . __( 'Toggle Sub-orders', 'dokan' ) . '</button>';
    }
}

add_action( 'restrict_manage_posts', 'dokan_admin_shop_order_toggle_sub_orders');

/**
 * Get total commision earning of the site
 * 
 * @global WPDB $wpdb
 * @return int
 */
function dokan_site_total_earning() {
    global $wpdb;

    $sql = "SELECT  SUM((do.order_total - do.net_amount)) as earning
            FROM {$wpdb->prefix}dokan_orders do
            LEFT JOIN $wpdb->posts p ON do.order_id = p.ID
            WHERE seller_id != 0 AND p.post_status = 'publish' AND do.order_status IN ('on-hold', 'completed', 'processing')
            ORDER BY do.order_id DESC";

    return $wpdb->get_var( $sql );
}

/**
 * Generate report in admin area
 * 
 * @global WPDB $wpdb
 * @global type $wp_locale
 * @param string $group_by
 * @param string $year
 * @return obj
 */
function dokan_admin_report( $group_by = 'day', $year = '' ) {
    global $wpdb, $wp_locale;

    $start_date = isset( $_POST['start_date'] ) ? $_POST['start_date'] : '';
    $end_date   = isset( $_POST['end_date'] ) ? $_POST['end_date'] : '';
    $current_year = date( 'Y' );

    if ( ! $start_date ) {
        $start_date = date( 'Y-m-d', strtotime( date( 'Ym', current_time( 'timestamp' ) ) . '01' ) );

        if ( $group_by == 'month' ) {
            $start_date = $year . '-01-01';
        }
    }

    if ( ! $end_date ) {
        $end_date = date( 'Y-m-d', current_time( 'timestamp' ) );

        if ( $group_by == 'month' && ( $year < $current_year ) ) {
            $end_date = $year . '-12-31';
        }
    }

    $start_date_to_time = strtotime( $start_date );
    $end_date_to_time = strtotime( $end_date );

    $date_where = '';

    if ( $group_by == 'day' ) {
        $group_by_query       = 'YEAR(p.post_date), MONTH(p.post_date), DAY(p.post_date)';
        $date_where           = " AND DATE(p.post_date) >= '$start_date' AND DATE(p.post_date) <= '$end_date'";
        $chart_interval       = ceil( max( 0, ( $end_date_to_time - $start_date_to_time ) / ( 60 * 60 * 24 ) ) );
        $barwidth             = 60 * 60 * 24 * 1000;
    } else {
        $group_by_query = 'YEAR(p.post_date), MONTH(p.post_date)';
        $chart_interval = 0;
        $min_date             = $start_date_to_time;
        while ( ( $min_date   = strtotime( "+1 MONTH", $min_date ) ) <= $end_date_to_time ) {
            $chart_interval ++;
        }
        $barwidth             = 60 * 60 * 24 * 7 * 4 * 1000;
    }

    $sql = "SELECT
                SUM((do.order_total - do.net_amount)) as earning,
                SUM(do.order_total) as order_total,
                COUNT(DISTINCT p.ID) as total_orders,
                p.post_date as order_date
            FROM {$wpdb->prefix}dokan_orders do
            LEFT JOIN $wpdb->posts p ON do.order_id = p.ID
            WHERE
                seller_id != 0 AND
                p.post_status = 'publish' AND
                do.order_status IN ('on-hold', 'completed', 'processing')
                $date_where
            GROUP BY $group_by_query";

    $data = $wpdb->get_results( $sql );

    // echo $sql;
    // var_dump($data);
    // var_dump($data, $barwidth, $start_date, $end_date);
    // Prepare data for report
    $order_counts      = dokan_prepare_chart_data( $data, 'order_date', 'total_orders', $chart_interval, $start_date_to_time, $group_by );
    $order_amounts     = dokan_prepare_chart_data( $data, 'order_date', 'order_total', $chart_interval, $start_date_to_time, $group_by );
    $order_commision     = dokan_prepare_chart_data( $data, 'order_date', 'earning', $chart_interval, $start_date_to_time, $group_by );

    // Encode in json format
    $chart_data = json_encode( array(
        'order_counts'      => array_values( $order_counts ),
        'order_amounts'     => array_values( $order_amounts ),
        'order_commision'     => array_values( $order_commision )
    ) );

    $chart_colours = array(
        'order_counts'  => '#3498db',
        'order_amounts'   => '#1abc9c',
        'order_commision'   => '#73a724'
    );

    ?>

    <script type="text/javascript">
        jQuery(function($) {

            var order_data = jQuery.parseJSON( '<?php echo $chart_data; ?>' );
            var series = [
                {
                    label: "<?php echo esc_js( __( 'Total Sales', 'dokan' ) ) ?>",
                    data: order_data.order_amounts,
                    shadowSize: 0,
                    hoverable: true,
                    points: { show: true, radius: 5, lineWidth: 3, fillColor: '#fff', fill: true },
                    lines: { show: true, lineWidth: 4, fill: false },
                    shadowSize: 0,
                    prepend_tooltip: "<?php echo __('Total: ', 'dokan') . get_woocommerce_currency_symbol(); ?>"
                },
                {
                    label: "<?php echo esc_js( __( 'Number of orders', 'dokan' ) ) ?>",
                    data: order_data.order_counts,
                    shadowSize: 0,
                    hoverable: true,
                    points: { show: true, radius: 5, lineWidth: 3, fillColor: '#fff', fill: true },
                    lines: { show: true, lineWidth: 4, fill: false },
                    shadowSize: 0,
                    append_tooltip: " <?php echo __( 'sales', 'dokan' ); ?>"
                },
                {
                    label: "<?php echo esc_js( __( 'Commision', 'dokan' ) ) ?>",
                    data: order_data.order_commision,
                    shadowSize: 0,
                    hoverable: true,
                    points: { show: true, radius: 5, lineWidth: 3, fillColor: '#fff', fill: true },
                    lines: { show: true, lineWidth: 4, fill: false },
                    shadowSize: 0,
                    prepend_tooltip: "<?php echo __('Commision: ', 'dokan') . get_woocommerce_currency_symbol(); ?>"
                },
            ];

            var main_chart = jQuery.plot(
                jQuery('.chart-placeholder.main'),
                series,
                {
                    legend: {
                        show: true,
                        position: 'nw'
                    },
                    series: {
                        lines: { show: true, lineWidth: 4, fill: false },
                        points: { show: true }
                    },
                    grid: {
                        borderColor: '#eee',
                        color: '#aaa',
                        backgroundColor: '#fff',
                        borderWidth: 1,
                        hoverable: true,
                        show: true,
                        aboveData: false,
                    },
                    xaxis: {
                        color: '#aaa',
                        position: "bottom",
                        tickColor: 'transparent',
                        mode: "time",
                        timeformat: "<?php if ( $group_by == 'day' ) echo '%d %b'; else echo '%b'; ?>",
                        monthNames: <?php echo json_encode( array_values( $wp_locale->month_abbrev ) ) ?>,
                        tickLength: 1,
                        minTickSize: [1, "<?php echo $group_by; ?>"],
                        font: {
                            color: "#aaa"
                        }
                    },
                    yaxes: [
                        {
                            min: 0,
                            minTickSize: 1,
                            tickDecimals: 0,
                            color: '#d4d9dc',
                            font: { color: "#aaa" }
                        },
                        {
                            position: "right",
                            min: 0,
                            tickDecimals: 2,
                            alignTicksWithAxis: 1,
                            color: 'transparent',
                            font: { color: "#aaa" }
                        }
                    ],
                    colors: ["<?php echo $chart_colours['order_counts']; ?>", "<?php echo $chart_colours['order_amounts']; ?>", "<?php echo $chart_colours['order_commision']; ?>"]
                }
            );

            jQuery('.chart-placeholder').resize();


            function showTooltip(x, y, contents) {
                jQuery('<div class="chart-tooltip">' + contents + '</div>').css({
                    top: y - 16,
                    left: x + 20
                }).appendTo("body").fadeIn(200);
            }

            var prev_data_index = null;
            var prev_series_index = null;

            jQuery(".chart-placeholder").bind("plothover", function(event, pos, item) {
                if (item) {
                    if (prev_data_index != item.dataIndex || prev_series_index != item.seriesIndex) {
                        prev_data_index = item.dataIndex;
                        prev_series_index = item.seriesIndex;

                        jQuery(".chart-tooltip").remove();

                        if (item.series.points.show || item.series.enable_tooltip) {

                            var y = item.series.data[item.dataIndex][1];

                            tooltip_content = '';

                            if (item.series.prepend_label)
                                tooltip_content = tooltip_content + item.series.label + ": ";

                            if (item.series.prepend_tooltip)
                                tooltip_content = tooltip_content + item.series.prepend_tooltip;

                            tooltip_content = tooltip_content + y;

                            if (item.series.append_tooltip)
                                tooltip_content = tooltip_content + item.series.append_tooltip;

                            if (item.series.pie.show) {

                                showTooltip(pos.pageX, pos.pageY, tooltip_content);

                            } else {

                                showTooltip(item.pageX, item.pageY, tooltip_content);

                            }

                        }
                    }
                } else {
                    jQuery(".chart-tooltip").remove();
                    prev_data_index = null;
                }
            });
        });

    </script>
    <?php

    return $data;
}

/**
 * Send notification to the seller once a product is published from pending
 * 
 * @param WP_Post $post
 * @return void
 */
function dokan_send_notification_on_product_publish( $post ) {
    if ( $post->post_type != 'product' ) {
        return;
    }

    $seller = get_user_by( 'id', $post->post_author );
    Dokan_Email::init()->product_published( $post, $seller );
}

add_action( 'pending_to_publish', 'dokan_send_notification_on_product_publish' );