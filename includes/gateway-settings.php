<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for TBC Gateway
 */
return array(
	'enabled' => array(
		'title'   => __( 'Enable/Disable', 'woo-tbc' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable TBC', 'woo-tbc' ),
		'default' => 'yes',
	),
	'title' => array(
		'title'       => __( 'Title', 'woo-tbc' ),
		'type'        => 'text',
		'description' => __( 'This controls the title which the user sees during checkout.', 'woo-tbc' ),
		'default'     => __( 'TBC', 'woo-tbc' ),
		'desc_tip'    => true,
	),
	'description' => array(
		'title'       => __( 'Description', 'woo-tbc' ),
		'type'        => 'text',
		'desc_tip'    => true,
		'description' => __( 'This controls the description which the user sees during checkout.', 'woo-tbc' ),
		'default'     => __( 'Pay with your credit card via TBC', 'woo-tbc' ),
	),
	'debug' => array(
		'title'       => __( 'Debug Log', 'woo-tbc' ),
		'type'        => 'checkbox',
		'label'       => __( 'Enable logging', 'woo-tbc' ),
		'default'     => 'no',
		'description' => sprintf( __( 'Log TBC events, such as IPN requests, inside <code>%s</code>', 'woo-tbc' ), wc_get_log_file_path( 'tbc' ) ),
	),
	'cert_path' => array(
		'title'       => __( 'Certificate path', 'woo-tbc' ),
		'type'        => 'text',
		'description' => __( 'Absolute path to certificate in .pem format.', 'woo-tbc' ),
		'default'     => __( '/', 'woo-tbc' ),
	),
	'cert_pass' => array(
		'title'       => __( 'Certificate passphrase', 'woo-tbc' ),
		'type'        => 'text',
	),
	'ok_slug' => array(
		'title'       => __( 'Ok', 'woo-tbc' ),
		'type'        => 'text',
		'description' => sprintf( __( '<code>%1$s/wc-api/%2$s</code> - communicate this OK url to TBC', 'woo-tbc' ), get_bloginfo( 'url' ), $this->get_option( 'ok_slug', 'tbcfree/ok' ) ),
		'default'     => __( 'tbcfree/ok', 'woo-tbc' ),
		'custom_attributes' => array(
			'readonly' => 'readonly',
		),
	),
	'fail_slug' => array(
		'title'       => __( 'Fail', 'woo-tbc' ),
		'type'        => 'text',
		'description' => sprintf( __( '<code>%1$s/wc-api/%2$s</code> - communicate this FAIL url to TBC', 'woo-tbc' ), get_bloginfo( 'url' ), $this->get_option( 'fail_slug', 'tbcfree/fail' ) ),
		'default'     => __( 'tbcfree/fail', 'woo-tbc' ),
		'custom_attributes' => array(
			'readonly' => 'readonly',
		),
	),
);
