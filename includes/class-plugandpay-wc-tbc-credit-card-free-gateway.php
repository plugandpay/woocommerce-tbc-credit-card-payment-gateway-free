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

use WeAreDe\TbcPay\TbcPayProcessor;

/**
 * TBC (Free) credit card payment gateway class.
 *
 * @class   \PlugandPay_WC_TBC_Credit_Card_Free_Gateway
 * @extends \WC_Payment_Gateway
 */
class PlugandPay_WC_TBC_Credit_Card_Free_Gateway extends WC_Payment_Gateway {

	/**
	 * Whether or not logging is enabled.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	public static $log_enabled = false;

	/**
	 * Logger instance.
	 *
	 * @since 1.0.0
	 * @var WC_Logger|false
	 */
	public static $log = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id                 = 'tbc_credit_card_free_gateway';
		$this->has_fields         = false;
		$this->order_button_text  = __( 'Proceed to TBC', 'tbc-gateway-free' );
		$this->method_title       = __( 'TBC (Free)', 'tbc-gateway-free' );
		$this->method_description = __( 'Accept Visa/Mastercard payments in your WooCommerce shop using TBC gateway.', 'tbc-gateway-free' );
		$this->supports           = array(
			'products',
		);

		$this->init_form_fields();
		$this->init_settings();

		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
		$this->debug       = 'yes' === $this->get_option( 'debug', 'no' );
		$this->cert_path   = $this->get_option( 'cert_path' );
		$this->cert_pass   = $this->get_option( 'cert_pass' );
		$this->ok_slug     = $this->get_option( 'ok_slug' );
		$this->fail_slug   = $this->get_option( 'fail_slug' );

		self::$log_enabled = $this->debug;

		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'woocommerce_api_redirect_to_payment_form', array( $this, 'redirect_to_payment_form' ) );
		add_action( 'woocommerce_api_' . $this->ok_slug, array( $this, 'return_from_payment_form_ok' ) );
		add_action( 'woocommerce_api_' . $this->fail_slug, array( $this, 'return_from_payment_form_fail' ) );
		add_action( 'woocommerce_api_close_business_day', array( $this, 'close_business_day' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

		$this->tbc             = new TbcPayProcessor( $this->cert_path, $this->cert_pass, $_SERVER['REMOTE_ADDR'] );
		$this->tbc->submit_url = sprintf( 'https://%s.ufc.ge:18443/ecomm2/MerchantHandler', $this->get_option( 'merchant_host' ) );
	}

	/**
	 * Logging method.
	 *
	 * @since 1.0.0
	 * @param string $message Log message.
	 * @param string $level Optional. Default 'info'. Possible values:
	 *                      emergency|alert|critical|error|warning|notice|info|debug.
	 */
	public static function log( $message, $level = 'info' ) {
		if ( self::$log_enabled ) {
			if ( empty( self::$log ) ) {
				self::$log = wc_get_logger();
			}
			self::$log->log( $level, $message, array( 'source' => 'tbc_credit_card_free_gateway' ) );
		}
	}

	/**
	 * Initialise gateway settings.
	 *
	 * @since 1.0.0
	 */
	public function init_form_fields() {
		$this->form_fields = include 'settings/gateway.php';
	}

	/**
	 * Display notices in admin dashboard.
	 *
	 * @since 1.0.0
	 */
	public function admin_notices() {
		if ( 'no' === $this->enabled ) {
			return;
		}

		if ( ! $this->cert_path ) {
			/* translators: %s Gateway settings URL */
			echo '<div class="error"><p>' . wp_kses_data( sprintf( __( 'TBC error: Please enter certificate path <a href="%s">here</a>.', 'tbc-gateway-free' ), esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . $this->id ) ) ) ) . '</p></div>';
		}

		if ( ! $this->cert_pass ) {
			/* translators: %s Gateway settings URL */
			echo '<div class="error"><p>' . wp_kses_data( sprintf( __( 'TBC error: Please enter certificate passphrase <a href="%s">here</a>.', 'tbc-gateway-free' ), esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . $this->id ) ) ) ) . '</p></div>';
		}
	}

	/**
	 * Is this gateway available?
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function is_available() {
		return parent::is_available() && $this->has_transactions() && $this->has_required_options();
	}

	/**
	 * Are there transactions left?
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function has_transactions() {
		return (int) get_option( sprintf( 'woocommerce_%s_transactions_%s', $this->id, date( 'm_Y' ) ) ) < 10 ? true : false;
	}

	/**
	 * Are all required options filled out?
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function has_required_options() {
		return $this->cert_path && $this->cert_pass ? true : false;
	}

	/**
	 * Increment transactions.
	 *
	 * @since 2.0.0
	 * @return bool
	 */
	public function increment_transactions() {
		$opt     = sprintf( 'woocommerce_%s_transactions_%s', $this->id, date( 'm_Y' ) );
		$current = (int) get_option( $opt );
		$current++;
		return update_option( $opt, $current );
	}

	/**
	 * Process the payment.
	 *
	 * @since 1.0.0
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order    = wc_get_order( $order_id );
		$currency = $order->get_currency() ? $order->get_currency() : get_woocommerce_currency();
		$amount   = $order->get_total();

		/* translators: %s order id */
		$this->tbc->description = sprintf( __( 'Order id %s', 'tbc-gateway-free' ), $order->get_id() );
		$this->tbc->language    = $this->get_payment_form_language();
		$this->tbc->amount      = $amount * 100;
		$this->tbc->currency    = 981;

		$this->log( sprintf( 'Info ~ Order id: %s - amount: %s (%s) %s (%s), language: %s.', $order->get_id(), $amount, $this->tbc->amount, $currency, $this->tbc->currency, $this->tbc->language ) );

		try {
			$start = $this->tbc->sms_start_transaction();
			if ( ! isset( $start['error'] ) && isset( $start['TRANSACTION_ID'] ) ) {
				$trans_id = $start['TRANSACTION_ID'];
			} else {
				if ( isset( $start['error'] ) ) {
					// Log returned error.
					$this->log( sprintf( 'Error ~ Order id: %s - Error msg: %s.', $order->get_id(), $start['error'] ) );
				} else {
					// Log generic error.
					$this->log( sprintf( 'Error ~ Order id: %s - no TRANSACTION_ID from Tbc.', $order->get_id() ) );
				}
				throw new Exception( __( 'Tbc did not return TRANSACTION_ID.', 'tbc-gateway-free' ) );
			}
		} catch ( Exception $e ) {
			// Add private note to order details.
			$order_note = $e->getMessage();
			$order->update_status( 'failed', $order_note );

			return array(
				'result' => 'failure',
			);
		}

		$this->log( sprintf( 'Success ~ Order id: %s -> transaction id: %s obtained successfully', $order->get_id(), $trans_id ) );

		// Save trans_id for reference.
		update_post_meta( $order->get_id(), '_transaction_id', $trans_id );

		$this->increment_transactions();

		$this->log( sprintf( 'Info ~ Order id: %s, redirecting user to Tbc gateway', $order->get_id() ) );

		return array(
			'result'   => 'success',
			'messages' => __( 'Success! redirecting to Tbc now ...', 'tbc-gateway-free' ),
			// Redirect user to tbc payment form.
			'redirect' => $this->get_payment_form_url( $trans_id ),
		);
	}

	/**
	 * OK endpoint
	 *
	 * Landing page for customers returning from Tbc after payment
	 * here we verify that transaction was indeed successful
	 * and update order status accordingly
	 *
	 * @uses WC_Gateway_TBC::get_order_id_by_transaction_id()
	 */
	public function return_from_payment_form_ok() {

		try {
			if ( ! isset( $_REQUEST['trans_id'] ) ) {
				$this->log( 'Error ~ Tbc did not return trans_id in $_REQUEST on OK page' );
				throw new Exception( __( 'Tbc did not return transaction id!', 'tbc-gateway-free' ) );
			}
			$trans_id = $_REQUEST['trans_id'];

			$order_id = $this->get_order_id_by_transaction_id( $trans_id );
			if ( ! $order_id ) {
				$this->log( sprintf( 'Error ~ could not find order id associated with transaction id: %s', $trans_id ) );
				throw new Exception( __( 'We could not find your order id!', 'tbc-gateway-free' ) );
			}

			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				$this->log( sprintf( 'Error ~ could not find order associated with order id: %s', $order_id ) );
				throw new Exception( __( 'We could not find your order!', 'tbc-gateway-free' ) );
			}
		} catch ( Exception $e ) {
			wc_add_notice( $e->getMessage(), 'error' );
			wp_safe_redirect( wc_get_page_permalink( 'checkout' ) );
			exit();
		}

		try {
			$transaction = $this->tbc->get_transaction_result( $trans_id );
			if ( ! isset( $transaction['RESULT'] ) || 'OK' !== $transaction['RESULT'] ) {
				$this->log( sprintf( 'Error ~ could not verify transaction result, Tbc did not return OK: %s', json_encode( $transaction ) ) );
				throw new Exception( __( 'We could not verify transaction result, logs should contain more information about this failure.', 'tbc-gateway-free' ) );
			}
		} catch ( Exception $e ) {
			// Add private note to order details.
			$order_note = $e->getMessage();
			$order->update_status( 'failed', $order_note );
			wc_add_notice( $order_note, 'error' );
			wp_safe_redirect( wc_get_page_permalink( 'checkout' ) );
			exit();
		}

		// Payment complete.
		$order->payment_complete();

		// Add order note.
		$complete_message = __( 'Tbc charge complete', 'tbc-gateway-free' );
		$order->add_order_note( $complete_message );
		$this->log( sprintf( 'Success ~ %s, transaction id: %s, order id: %s', $complete_message, $trans_id, $order_id ) );

		// Remove cart.
		WC()->cart->empty_cart();

		// Redirect to thank you.
		wp_safe_redirect( $this->get_return_url( $order ) );
		exit();
	}

	/**
	 * FAIL endpoint.
	 *
	 * @since 1.0.0
	 */
	public function return_from_payment_form_fail() {
		$error = __( 'Technical faulure in ECOMM system!', 'tbc-gateway-free' );
		$this->log( sprintf( 'Error ~ %s', $error ) );
		wp_die( esc_html( $error ) );
	}

	/**
	 * Create payment form url.
	 *
	 * @since 1.0.0
	 * @param string $trans_id Transaction id from gateway.
	 * @return string
	 */
	public function get_payment_form_url( $trans_id ) {
		return sprintf( '%s/wc-api/redirect_to_payment_form?merchant_host=%s&transaction_id=%s', get_bloginfo( 'url' ), $this->get_option( 'merchant_host' ), rawurlencode( $trans_id ) );
	}

	/**
	 * TBC payment page language selection.
	 *
	 * @since 2.0.0
	 * @return string
	 */
	public function get_payment_form_language() {
			$locale = get_locale();
			$langs  = array(
				'ka_GE' => 'GE',
				'en_US' => 'EN',
				'ru_RU' => 'RU',
			);
			$lang   = isset( $langs[ $locale ] ) ? $langs[ $locale ] : strtoupper( substr( $locale, 0, -3 ) );
			return $lang;
	}

	/**
	 * Redirect user to TBC payment page.
	 *
	 * @since 1.0.0
	 */
	public function redirect_to_payment_form() {
		?>

		<html>
			<head>
				<title>TBC</title>
				<script type="text/javascript" language="javascript">
					function redirect() {
						document.returnform.submit();
					}
				</script>
			</head>

			<body onLoad="javascript:redirect()">
				<form name="returnform" action="<?php echo esc_url( sprintf( 'https://%s.ufc.ge/ecomm2/ClientHandler', $_GET['merchant_host'] ) ); ?>" method="POST">
					<input type="hidden" name="trans_id" value="<?php echo rawurldecode( $_GET['transaction_id'] ); ?>">

					<noscript>
						<center>
							<?php esc_html_e( 'Please click the submit button below.', 'tbc-gateway-free' ); ?><br>
							<input type="submit" name="submit" value="Submit">
						</center>
					</noscript>
				</form>
			</body>

		</html>

		<?php
		exit();
	}

	/**
	 * Close business day, needs to run via cron every 24h.
	 *
	 * @since 1.0.0
	 */
	public function close_business_day() {
		$result = $this->tbc->close_day();
		$this->log( sprintf( 'Info ~ close business day result: %s', json_encode( $result ) ) );
		exit();
	}

	/**
	 * Get order id by transaction id.
	 *
	 * @since 1.0.0
	 * @param string $trans_id Transaction id from gateway.
	 * @return string
	 */
	public function get_order_id_by_transaction_id( $trans_id ) {
		global $wpdb;

		$meta = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT * FROM $wpdb->postmeta
				 WHERE meta_key = '_transaction_id'
				   AND meta_value = %s
				 LIMIT 1
				",
				$trans_id
			)
		);

		if ( ! empty( $meta ) && is_array( $meta ) && isset( $meta[0] ) ) {
			$meta = $meta[0];
		}

		if ( is_object( $meta ) ) {
			return $meta->post_id;
		}

		return false;
	}

}

