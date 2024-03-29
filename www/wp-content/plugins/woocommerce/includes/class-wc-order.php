<?php
/**
 * Order
 *
 * @class    WC_Order
 * @version  2.2.0
 * @package  WooCommerce/Classes
 * @category Class
 * @author   WooThemes
 */
class WC_Order extends WC_Abstract_Order {

	/**
	 * Initialize the order refund.
	 *
	 * @param int|WC_Order $order
	 */
	public function __construct( $order = '' ) {
		$this->order_type = 'simple';

		parent::__construct( $order );
	}

	/**
	 * Get order refunds
	 *
	 * @since 2.2
	 * @return array
	 */
	public function get_refunds() {
		if ( empty( $this->refunds ) && ! is_array( $this->refunds ) ) {
			$refunds      = array();
			$refund_items = get_posts(
				array(
					'post_type'      => 'shop_order_refund',
					'post_parent'    => $this->id,
					'posts_per_page' => -1,
					'post_status'    => 'any',
					'fields'         => 'ids'
				)
			);

			foreach ( $refund_items as $refund_id ) {
				$refunds[] = new WC_Order_Refund( $refund_id );
			}

			$this->refunds = $refunds;
		}
		return $this->refunds;
	}

	/**
	 * Get amount already refunded
	 *
	 * @since 2.2
	 * @return int|float
	 */
	public function get_total_refunded() {
		global $wpdb;

		$total = $wpdb->get_var( $wpdb->prepare( "
			SELECT SUM( postmeta.meta_value )
			FROM $wpdb->postmeta AS postmeta
			INNER JOIN $wpdb->posts AS posts ON ( posts.post_type = 'shop_order_refund' AND posts.post_parent = %d )
			WHERE postmeta.meta_key = '_refund_amount'
			AND postmeta.post_id = posts.ID
		", $this->id ) );

		return $total;
	}

	/**
	 * Get the refunded amount for a line item
	 *
	 * @param  int $item_id ID of the item we're checking
	 * @param  string $item_type type of the item we're checking, if not a line_item
	 * @return integer
	 */
	public function get_qty_refunded_for_item( $item_id, $item_type = 'line_item' ) {
		$qty = 0;
		foreach ( $this->get_refunds() as $refund ) {
			foreach ( $refund->get_items( $item_type ) as $refunded_item ) {
				if ( isset( $refunded_item['refunded_item_id'] ) && $refunded_item['refunded_item_id'] == $item_id ) {
					$qty += $refunded_item['qty'];
				}
			}
		}
		return $qty;
	}

	/**
	 * Get the refunded amount for a line item
	 *
	 * @param  int $item_id ID of the item we're checking
	 * @param  string $item_type type of the item we're checking, if not a line_item
	 * @return integer
	 */
	public function get_total_refunded_for_item( $item_id, $item_type = 'line_item' ) {
		$total = 0;
		foreach ( $this->get_refunds() as $refund ) {
			foreach ( $refund->get_items( $item_type ) as $refunded_item ) {
				if ( isset( $refunded_item['refunded_item_id'] ) && $refunded_item['refunded_item_id'] == $item_id ) {
					switch ( $item_type ) {
						case 'shipping' :
							$total += $refunded_item['cost'];
						break;
						default :
							$total += $refunded_item['line_total'];
						break;
					}
				}
			}
		}
		return $total * -1;
	}

	/**
	 * Get the refunded amount for a line item
	 *
	 * @param  int $item_id ID of the item we're checking
	 * @param  int $tax_id ID of the tax we're checking
	 * @param  string $item_type type of the item we're checking, if not a line_item
	 * @return integer
	 */
	public function get_tax_refunded_for_item( $item_id, $tax_id, $item_type = 'line_item' ) {
		$total = 0;
		foreach ( $this->get_refunds() as $refund ) {
			foreach ( $refund->get_items( $item_type ) as $refunded_item ) {
				if ( isset( $refunded_item['refunded_item_id'] ) && $refunded_item['refunded_item_id'] == $item_id ) {
					switch ( $item_type ) {
						case 'shipping' :
							$tax_data = maybe_unserialize( $refunded_item['taxes'] );
							if ( isset( $tax_data[ $tax_id ] ) ) {
								$total += $tax_data[ $tax_id ];
							}
						break;
						default :
							$tax_data = maybe_unserialize( $refunded_item['line_tax_data'] );
							if ( isset( $tax_data['total'][ $tax_id ] ) ) {
								$total += $tax_data['total'][ $tax_id ];
							}
						break;
					}
				}
			}
		}
		return $total * -1;
	}
}
