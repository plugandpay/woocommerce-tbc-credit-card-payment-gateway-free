<?php
/**
 * Intellectual Property rights, and copyright, reserved by Plug and Pay, Ltd. as allowed by law include,
 * but are not limited to, the working concept, function, and behavior of this software,
 * the logical code structure and expression as written.
 *
 * @package     WooCommerce TBC Credit Card Payment Gateway (Free)
 * @author      Plug and Pay Ltd. https://plugandpay.ge/
 * @copyright   Copyright (c) Plug and Pay Ltd. (support@plugandpay.ge)
 * @since       1.0.0
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for TBC Gateway (Free)
 */
return array(
	'enabled'       => array(
		'title'   => __( 'Enable/Disable', 'tbc-gateway-free' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable TBC', 'tbc-gateway-free' ),
		'default' => 'yes',
	),
	'title'         => array(
		'title'       => __( 'Title', 'tbc-gateway-free' ),
		'type'        => 'text',
		'description' => __( 'This controls the title which the user sees during checkout.', 'tbc-gateway-free' ),
		'default'     => __( 'TBC', 'tbc-gateway-free' ),
		'desc_tip'    => true,
	),
	'description'   => array(
		'title'       => __( 'Description', 'tbc-gateway-free' ),
		'type'        => 'text',
		'desc_tip'    => true,
		'description' => __( 'This controls the description which the user sees during checkout.', 'tbc-gateway-free' ),
		'default'     => __( 'Pay with your credit card via TBC', 'tbc-gateway-free' ),
	),
	'debug'         => array(
		'title'       => __( 'Debug Log', 'tbc-gateway-free' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable logging', 'tbc-gateway-free' ),
		'default'     => 'no',
		/* translators: %s: log file path */
		'description' => sprintf( __( 'Log TBC events, such as IPN requests, inside <code>%s</code>', 'tbc-gateway-free' ), wc_get_log_file_path( 'tbc_credit_card_free_gateway' ) ),
	),
	'merchant_host' => array(
		'title'       => __( 'Merchant host', 'tbc-gateway-free' ),
		'type'        => 'select',
		'class'       => 'wc-enhanced-select',
		'description' => __( 'TBC merchants registered before June 2018 must use Securepay host, newer merchant accounts must use Ecommerce (default).', 'tbc-gateway-free' ),
		'default'     => 'ecommerce',
		'desc_tip'    => true,
		'options'     => array(
			'securepay' => __( 'Securepay', 'tbc-gateway-free' ),
			'ecommerce' => __( 'Ecommerce', 'tbc-gateway-free' ),
		),
	),
	'cert_path'     => array(
		'title'       => __( 'Certificate path', 'tbc-gateway-free' ),
		'type'        => 'text',
		'description' => __( 'Absolute path to certificate in .pem format.', 'tbc-gateway-free' ),
		'default'     => __( '/', 'tbc-gateway-free' ),
	),
	'cert_pass'     => array(
		'title' => __( 'Certificate passphrase', 'tbc-gateway-free' ),
		'type'  => 'text',
	),
	'ok_slug'       => array(
		'title'             => __( 'Ok', 'tbc-gateway-free' ),
		'type'              => 'text',
		/* translators: %s: OK url */
		'description'       => sprintf( __( '<code>%1$s/wc-api/%2$s</code> - communicate this OK url to TBC', 'tbc-gateway-free' ), get_bloginfo( 'url' ), $this->get_option( 'ok_slug', 'tbcfree/ok' ) ),
		'default'           => __( 'tbcfree/ok', 'tbc-gateway-free' ),
		'custom_attributes' => array(
			'readonly' => 'readonly',
		),
	),
	'fail_slug'     => array(
		'title'             => __( 'Fail', 'tbc-gateway-free' ),
		'type'              => 'text',
		/* translators: %s: FAIL url */
		'description'       => sprintf( __( '<code>%1$s/wc-api/%2$s</code> - communicate this FAIL url to TBC', 'tbc-gateway-free' ), get_bloginfo( 'url' ), $this->get_option( 'fail_slug', 'tbcfree/fail' ) ),
		'default'           => __( 'tbcfree/fail', 'tbc-gateway-free' ),
		'custom_attributes' => array(
			'readonly' => 'readonly',
		),
	),
);
