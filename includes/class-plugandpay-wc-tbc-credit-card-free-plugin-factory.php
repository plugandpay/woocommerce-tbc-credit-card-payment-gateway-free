<?php
/**
 * Intellectual Property rights, and copyright, reserved by Plug and Pay, Ltd. as allowed by law include,
 * but are not limited to, the working concept, function, and behavior of this software,
 * the logical code structure and expression as written.
 *
 * @package     WooCommerce TBC Credit Card Payment Gateway (Free)
 * @author      Plug and Pay Ltd. https://plugandpay.ge/
 * @copyright   Copyright (c) Plug and Pay Ltd. (support@plugandpay.ge)
 * @since       2.0.0
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TBC (Free) plugin factory class.
 */
class PlugandPay_WC_TBC_Credit_Card_Free_Plugin_Factory {

	/**
	 * __FILE__ from the root plugin file.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	public $file;

	/**
	 * The current version of the plugin.
	 *
	 * @since 2.0.0
	 * @var string
	 */
	public $version;

	/**
	 * Extras / misc.
	 *
	 * @since 2.0.0
	 * @var \PlugandPay_WC_TBC_Credit_Card_Free_Extras|null
	 */
	public $extras = null;

	/**
	 * Holds a single instance of this class.
	 *
	 * @since 2.0.0
	 * @var \PlugandPay_WC_TBC_Credit_Card_Free_Plugin_Factory|null
	 */
	protected static $_instance = null;

	/**
	 * Returns a single instance of this class.
	 *
	 * @since 2.0.0
	 * @param string $file Must be __FILE__ from the root plugin file.
	 * @param string $software_version Current software version of this plugin.
	 * @return \PlugandPay_WC_TBC_Credit_Card_Free_Plugin_Factory|null
	 */
	public static function instance( $file, $software_version ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $software_version );
		}
		return self::$_instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 * @param string $file Must be __FILE__ from the root plugin file.
	 * @param string $software_version Current software version of this plugin.
	 */
	public function __construct( $file, $software_version ) {
		$this->file    = $file;
		$this->version = $software_version;

		$this->init_dependencies();

		add_action( 'woocommerce_api_is_plugandpay', array( $this, 'list_installed_plugins' ) );
		add_filter( 'plugandpay_installed_plugins', array( $this, 'add_to_installed_plugins' ) );
		add_filter( 'woocommerce_payment_gateways', array( $this, 'register_payment_gateway' ) );
		add_action( 'init', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Create the list of installed (Plug and Pay) plugins.
	 *
	 * @since 2.0.0
	 */
	public function list_installed_plugins() {
		$array = apply_filters( 'plugandpay_installed_plugins', array() );
		header( 'Content-Type: application/json' );
		echo json_encode( $array, JSON_PRETTY_PRINT );
		exit;
	}

	/**
	 * Add this plugin to the list of installed (Plug and Pay) plugins.
	 *
	 * @since 2.0.0
	 * @param array $list Plugandpay installed plugins list.
	 * @return array
	 */
	public function add_to_installed_plugins( $list ) {
		$list['tbc_credit_card_free_gateway'] = $this->whoami();
		return $list;
	}

	/**
	 * Diagnostic information about this plugin.
	 *
	 * @since 2.0.0
	 * @return array
	 */
	public function whoami() {
		return array(
			'version' => $this->version,
		);
	}

	/**
	 * Init plugin dependencies.
	 *
	 * @since 2.0.0
	 */
	public function init_dependencies() {

		/**
		 * Extras / misc.
		 *
		 * @since 2.0.0
		 * @param string $file Must be __FILE__ from the root plugin file.
		 * @param string $software_version Current software version of this plugin.
		 */
		$this->extras = new PlugandPay_WC_TBC_Credit_Card_Free_Extras( $this->file, $this->version );

	}

	/**
	 * Register the payment gateway.
	 *
	 * @since 2.0.0
	 * @param array $gateways Payment gateways.
	 */
	public function register_payment_gateway( $gateways ) {
		$gateways[] = 'PlugandPay_WC_TBC_Credit_Card_Free_Gateway';
		return $gateways;
	}

	/**
	 * Load textdomain.
	 *
	 * @since 2.0.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'tbc-gateway-free', false, dirname( plugin_basename( $this->file ) ) . '/languages' );
	}

}

