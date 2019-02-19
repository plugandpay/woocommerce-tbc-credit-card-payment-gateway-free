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
 * TBC (Free) extras class.
 */
class PlugandPay_WC_TBC_Credit_Card_Free_Extras {

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
	 * Constructor.
	 *
	 * @since 2.0.0
	 * @param string $file Must be __FILE__ from the root plugin file.
	 * @param string $software_version Current software version of this plugin.
	 */
	public function __construct( $file, $software_version ) {
		$this->file    = $file;
		$this->version = $software_version;

		add_filter( 'woocommerce_gateway_icon', [ $this, 'add_gateway_icons' ], 10, 2 );
		add_action( 'wp_dashboard_setup', [ $this, 'init_dashboard_widgets' ] );
		add_action( 'woocommerce_after_dashboard_status_widget', [ $this, 'add_tbc_status' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'load_report_css' ] );
		add_filter( 'plugin_action_links_' . plugin_basename( $this->file ), [ $this, 'add_upgrade_link' ] );
	}

	/**
	 * Add PlugandPay logo to the gateway.
	 *
	 * @since 2.0.0
	 * @param string $icons Html image tags.
	 * @param string $gateway_id Gateway id.
	 * @return string
	 */
	public function add_gateway_icons( $icons, $gateway_id ) {
		if ( 'tbc_credit_card_free_gateway' === $gateway_id ) {
			$icons .= sprintf(
				'<a href="https://plugandpay.ge%1$s"><img width="40" src="%2$sassets/plugandpay.svg" alt="Plug and Pay" /></a>',
				$this->analytics_campaign( 'checkout', 'chooseGatewayLogo' ),
				plugin_dir_url( $this->file )
			);
		}
		return $icons;
	}

	/**
	 * Init dashboard widgets.
	 *
	 * @since 2.0.0
	 */
	public function init_dashboard_widgets() {

		add_meta_box(
			'plugandpay_products_widget',
			__( 'Plug and Pay - Products', 'tbc-gateway-free' ),
			[ $this, 'display_products_widget' ],
			'dashboard',
			'side',
			'high'
		);

		add_meta_box(
			'plugandpay_blog_widget',
			__( 'Plug and Pay - Blog', 'tbc-gateway-free' ),
			[ $this, 'display_blog_widget' ],
			'dashboard',
			'side',
			'high'
		);

	}

	/**
	 * Products widget.
	 *
	 * @since 2.0.0
	 */
	public function display_products_widget() {
		$campaign = $this->analytics_campaign( 'dashboard', 'productsWidget' );
		$this->display_feed(
			'https://plugandpay.ge/shop/feed/',
			[
				'type'     => 'products',
				'campaign' => $campaign,
			]
		);
		?>

		<p class="community-events-footer" style="margin:-12px;padding-bottom:0;">
			<?php
				printf(
					'<a href="https://plugandpay.ge/shop/%1$s" target="_blank">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>',
					esc_html( $campaign ),
					esc_html__( 'Shop', 'tbc-gateway-free' ),
					/* translators: accessibility text */
					esc_html__( '(opens in a new window)', 'tbc-gateway-free' )
				);
			?>

		<?php
	}

	/**
	 * Blog widget.
	 *
	 * @since 2.0.0
	 */
	public function display_blog_widget() {
		$campaign = $this->analytics_campaign( 'dashboard', 'blogWidget' );
		$this->display_feed(
			'https://plugandpay.ge/feed/',
			[
				'campaign' => $campaign,
			]
		);
		?>

		<p class="community-events-footer" style="margin:-12px;padding-bottom:0;">
			<?php
				printf(
					'<a href="https://plugandpay.ge/blog/%1$s" target="_blank">%2$s <span class="screen-reader-text">%3$s</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>',
					esc_html( $campaign ),
					esc_html__( 'Blog', 'tbc-gateway-free' ),
					/* translators: accessibility text */
					esc_html__( '(opens in a new window)', 'tbc-gateway-free' )
				);
			?>

		<?php
	}

	/**
	 * Display feed.
	 *
	 * @since 2.0.0
	 * @param string $url RSS feed url.
	 * @param array  $args Optional arguments.
	 */
	public function display_feed( $url, $args = [] ) {
		$feed = fetch_feed( $url );

		if ( ! is_object( $feed ) ) {
			return;
		}

		if ( is_wp_error( $feed ) ) {
			if ( is_admin() || current_user_can( 'manage_options' ) ) {
				echo '<p><strong>' . esc_html__( 'RSS Error:', 'tbc-gateway-free' ) . '</strong> ' . esc_html( $feed->get_error_message() ) . '</p>';
				return;
			}
		}

		$default_args = [
			'type'     => 'posts',
			'items'    => 5,
			'campaign' => '',
		];
		$args         = wp_parse_args( $args, $default_args );

		if ( ! $feed->get_item_quantity() ) {
			echo '<ul><li>' . esc_html__( 'An error has occurred, which probably means the feed is down. Try again later.', 'tbc-gateway-free' ) . '</li></ul>';
			$feed->__destruct();
			unset( $feed );
			return;
		}

		echo '<ul>';
		switch ( $args['type'] ) {

			case 'posts':
				foreach ( $feed->get_items( 0, $args['items'] ) as $item ) {
					$image = $item->get_item_tags( '', 'image' );
					echo sprintf(
						'<li><img src="%s" style="float:left;width:30%%;padding-right:5%%;" /><div style="float:left;width:65%%;"><a href="%s%s">%s</a><p>%s</p></div><div style="clear: both;"></div></li>',
						esc_url( isset( $image[0]['data'] ) ? $image[0]['data'] : '' ),
						esc_url( $item->get_link() ),
						esc_html( $args['campaign'] ),
						esc_html( $item->get_title() ),
						esc_html( wp_trim_words( $item->get_description(), 14, ' [&hellip;]' ) )
					);
				}
				break;

			case 'products':
				foreach ( $feed->get_items( 0, $args['items'] ) as $item ) {
					$image = $item->get_item_tags( '', 'image' );
					echo sprintf(
						'<li style="display:inline-block;width:33.33%%;"><a href="%s%s"><img src="%s" style="width:95%%;padding-bottom:5%%;" /></a></li>',
						esc_url( $item->get_link() ),
						esc_html( $args['campaign'] ),
						esc_url( isset( $image[0]['data'] ) ? $image[0]['data'] : '' )
					);
				}
				break;
		}
		echo '</ul>';

		$feed->__destruct();
		unset( $feed );
	}

	/**
	 * Add TBC status to WooCommerce reports widget.
	 *
	 * @since 2.0.0
	 */
	public function add_tbc_status() {

		if ( current_user_can( 'view_woocommerce_reports' ) ) {
			?>

			<li class="tbc-gateway-free-report-counter">
				<a target="_blank" href="https://plugandpay.ge/product/woocommerce-tbc-credit-card-payment-gateway/<?php echo esc_html( $this->analytics_campaign( 'dashboard', 'upgradeNagCounter' ) ); ?>">
					<?php
						$current = (int) get_option( sprintf( 'woocommerce_tbc_credit_card_free_gateway_transactions_%s', date( 'm_Y' ) ) );
						$total   = 10;
						printf(
							/* translators: %s: current / total */
							esc_html__( '%s TBC transactions this month', 'tbc-gateway-free' ),
							sprintf( '<strong>%d / %d</strong>', esc_html( $current ), esc_html( $total ) )
						);
					?>
				</a>
			</li>

			<li class="tbc-gateway-free-report-notice">
				<a target="_blank" href="https://plugandpay.ge/product/woocommerce-tbc-credit-card-payment-gateway/<?php echo esc_html( $this->analytics_campaign( 'dashboard', 'upgradeNagNotice' ) ); ?>">
					<?php
						esc_html_e( 'Upgrade to TBC Premium plugin and have unlimited monthly transactions.', 'tbc-gateway-free' );
					?>
				</a>
			</li>
			<?php
		}

	}

	/**
	 * Load report css in dashboard.
	 *
	 * @since 2.0.0
	 */
	public function load_report_css() {
		wp_enqueue_style( 'tbc-gateway-free-report', plugins_url( 'assets/tbc-gateway-free-report.css', $this->file ), [], $this->version );
	}

	/**
	 * Add upgrade notice to plugin action links.
	 *
	 * @since 2.0.0
	 * @param array $links Plugin action links.
	 * @return array
	 */
	public function add_upgrade_link( $links ) {
		$upgrade_link = sprintf(
			'<a href="https://plugandpay.ge/product/woocommerce-tbc-credit-card-payment-gateway/%s" target="_blank"><strong style="color: #11967A; display: inline;">%s</strong></a>',
			esc_html( $this->analytics_campaign( 'installedPlugins', 'upgradeNag' ) ),
			esc_html__( 'Upgrade To Premium', 'tbc-gateway-free' )
		);
		$links        = array_merge(
			[
				'tbc-gateway-free-upgrade' => $upgrade_link,
			],
			$links
		);
		return $links;
	}

	/**
	 * Generate analytics campaign query string.
	 *
	 * @since 2.0.0
	 * @param string $source Source (Where).
	 * @param string $medium Medium (What).
	 * @param string $campaign Optional campaign name, defaults to tbcfree.
	 * @return string
	 */
	public function analytics_campaign( $source, $medium, $campaign = 'tbcfree' ) {
		return sprintf(
			'?utm_source=%s&utm_medium=%s&utm_campaign=%s',
			$source,
			$medium,
			$campaign
		);
	}

}

