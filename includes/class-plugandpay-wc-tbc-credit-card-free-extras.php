<?php
/**
 * Intellectual Property rights, and copyright, reserved by Plug and Pay, Ltd. as allowed by law include,
 * but are not limited to, the working concept, function, and behavior of this software,
 * the logical code structure and expression as written.
 *
 * @package     WooCommerce TBC Credit Card Payment Gateway (Free)
 * @author      Plug and Pay Ltd. https://plugandpay.ge/
 * @copyright   Copyright (c) Plug and Pay Ltd. (support@plugandpay.ge)
 * @since       1.0.4
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TBC (Free) extras class.
 */
class PlugandPay_WC_TBC_Credit_Card_Free_Extras {

	/**
	 * __FILE__ from the root plugin file.
	 *
	 * @since 1.0.4
	 * @var string
	 */
	public $file;

	/**
	 * Constructor.
	 *
	 * @since 1.0.4
	 * @param string $file Must be __FILE__ from the root plugin file.
	 */
	public function __construct( $file ) {
		$this->file = $file;

		add_filter( 'woocommerce_gateway_icon', array( $this, 'add_gateway_icons' ), 10, 2 );
	}

	/**
	 * Add PlugandPay logo to the gateway.
	 *
	 * @since 1.0.4
	 * @param string $icons Html image tags.
	 * @param string $gateway_id Gateway id.
	 * @return string
	 */
	public function add_gateway_icons( $icons, $gateway_id ) {
		if ( 'tbc_credit_card_free_gateway' === $gateway_id ) {
			$icons .= sprintf(
				'<a href="https://plugandpay.ge"><img width="40" src="%1$sassets/plugandpay.svg" alt="Plug and Pay" /></a>',
				plugin_dir_url( $this->file )
			);
		}
		return $icons;
	}

}

