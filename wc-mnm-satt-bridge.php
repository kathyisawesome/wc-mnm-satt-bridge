<?php
/**
 * Plugin Name: WooCommerce Mix and Match - Subscriptions
 * Plugin URI:  http://www.woocommerce.com/products/woocommerce-mix-and-match-products/
 * Description: Enables full All Products for Subscriptions support for Mix and Match Products.
 * Version: 2.2.0
 * Author: Kathy Darling
 * Author URI: http://kathyisawesome.com/
 * Text Domain: wc-mnm-satt-bridge
 * Domain Path: /languages
 * WC requires at least: 5.8.0
 * WC tested up to: 6.1.0
 * 
 * GitHub Plugin URI: kathyisawesome/wc-mnm-satt-bridge
 * GitHub Plugin URI: https://github.com/kathyisawesome/wc-mnm-satt-bridge
 * Release Asset: true
 *
 * Copyright: © 2019 Kathy Darling
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

/**
 * The Main WC_MNM_APFS_Compatibility class
 **/
if ( ! class_exists( 'WC_MNM_APFS_Compatibility' ) ) :

	class WC_MNM_APFS_Compatibility {

		/**
		 * @constant plugin version
		 * @since 0.1.0
		 */
		const VERSION = '2.2.0';

		/**
		 * @var required versions
		 * @since 2.0.0
		 */
		public static $required = array( 
			'woo'  => '4.0.0',
			'mnm'  => '1.12.0',
			'apfs' => '3.0.0',
			'subs' => '3.0.0'
		);

		/**
		 * Initialize.
		 */
		public static function init() {

			// Load translation files.
			add_action( 'init', __NAMESPACE__ . '\load_plugin_textdomain' );

			if ( ! self::environment_check() ) {
				add_action('admin_notices', array( __CLASS__, 'admin_notices' ) );
				return false;
			}

			require_once 'includes/wc-mnm-apfs-per-item-pricing.php';
			require_once 'includes/wc-mnm-apfs-subscription-switching.php';

		}

		/**
		 * Make the plugin translation ready
		 */
		public static function load_plugin_textdomain() {
			load_plugin_textdomain( 'wc-mnm-satt-bridge' , false , dirname( plugin_basename( __FILE__ ) ) .  '/languages/' );
		}

		/**
		 * Check all requirements are met.
		 *
		 * @return bool
		 * @since  2.0.0
		 */
		public static function environment_check() {

			$notices = array();

			// Check WooCommerce version.
			if ( ! function_exists( 'WC' ) || version_compare(  WC()->version, self::$required[ 'woo' ], '<' ) ) {
				$notices[] = sprintf( __( '<strong>WooCommerce Mix and Match: All Products for Subscriptions Compatibility</strong> mini-extension is inactive. The <strong>WooCommerce</strong> plugin must be active and atleast version %s for Mix and Match: All Products for Subscriptions Compatibility to function</strong>. Please update or activate WooCommerce.', 'wc-mnm-satt-bridge' ), self::$required[ 'woo' ] );
			}
				
			if ( ! class_exists( 'WC_Subscriptions' ) || version_compare( WC_Subscriptions::$version, self::$required[ 'subs' ], '<' ) ) {
				$notices[] = sprintf( __( '<strong>WooCommerce Mix and Match: All Products for Subscriptions Compatibility</strong> mini-extension is inactive. The <strong>WooCommerce Subscriptions</strong> plugin must be active and atleast version %s for Mix and Match: All Products for Subscriptions Compatibility to function</strong>. Please update or activate WooCommerce Subscriptions.', 'wc-mnm-satt-bridge' ), self::$required[ 'subs' ] );
			}

			if ( ! class_exists( 'WCS_ATT' ) || version_compare( WCS_ATT::VERSION, self::$required[ 'apfs' ], '<' ) ) {
				$notices[] = sprintf( __( '<strong>WooCommerce Mix and Match: All Products for Subscriptions Compatibility</strong> mini-extension is inactive. The <strong>WooCommerce All Products for Subscriptions</strong> plugin must be active and atleast version %s for Mix and Match: All Products for Subscriptions Compatibility to function</strong>. Please update or activate WooCommerce All Products for Subscriptions.', 'wc-mnm-satt-bridge' ), self::$required[ 'apfs' ] );
			}

			if ( ! function_exists( 'WC_Mix_and_Match' ) || version_compare( WC_Mix_and_Match()->version, self::$required[ 'mnm' ], '<' ) ) {
				$notices[] = sprintf( __( '<strong>WooCommerce Mix and Match: All Products for Subscriptions Compatibility</strong> mini-extension is inactive. The <strong>WooCommerce Mix and Match Products</strong> plugin must be active and atleast version %s for Mix and Match: All Products for Subscriptions Compatibility to function</strong>. Please update or activate WooCommerce Mix and Match Products.', 'wc-mnm-satt-bridge' ), self::$required[ 'mnm' ] );
			}

			if ( empty( $notices ) ) {
				return true;
			} else {
				update_option( 'wc_mnm_apfs_compatibility_notices', $notices );
				return false;
			}

	    }

		/**
		 * Display notices.
		 *
		 * @return bool
		 * @since  2.0.0
		 */
		public static function admin_notices( $data ) {

			$notices = get_option( 'wc_mnm_apfs_compatibility_notices' );

			if ( is_array( $notices ) ) {
				foreach( $notices as $notice ) {
					echo '<div class="notice notice-error">
						<p>' . wp_kses_post( $notice ) . '</p>
					</div>';
				}
				delete_option( 'wc_mnm_apfs_compatibility_notices' );
			}

	    }

	} // End class: do not remove or there will be no more guacamole for you.

endif; // End class_exists check.

// Launch the whole plugin.
add_action( 'plugins_loaded', array( 'WC_MNM_APFS_Compatibility', 'init' ), 100 );

