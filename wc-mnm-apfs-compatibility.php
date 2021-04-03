<?php
/**
 * Plugin Name: WooCommerce Mix and Match - All Products for Subscriptions Compatibility
 * Plugin URI:  http://www.woocommerce.com/products/woocommerce-mix-and-match-products/?aff=5151&cid=4951026 
 * Description: Adds All Products for Subscriptions support for Mix and Match Products.
 * Version: 2.0.2
 * Author: Kathy Darling
 * Author URI: http://kathyisawesome.com/
 * WC requires at least: 4.0.0
 * WC tested up to: 4.4.0
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
		const VERSION = '2.0.2';

		/**
		 * @var required versions
		 * @since 2.0.0
		 */
		public static $required = array( 
			'woo'  => '4.0.0',
			'mnm'  => '1.9.0',
			'apfs' => '3.0.0',
			'subs' => '3.0.0'
		);

		/**
		 * Initialize.
		 */
		public static function init() {

			if( ! self::environment_check() ) {
				add_action('admin_notices', array( __CLASS__, 'admin_notices' ) );
				return false;
			}

			require_once 'includes/wc-mnm-apfs-per-item-pricing.php';
			require_once 'includes/wc-mnm-apfs-subscription-switching.php';

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
			if ( function_exists( 'WC' ) && version_compare(  WC()->version, self::$required[ 'woo' ] ) < 0 ) {
				$notices[] = sprintf( __( '<strong>WooCommerce Mix and Match: All Products for Subscriptions Compatibility</strong> mini-extension is inactive. The <strong>WooCommerce</strong> plugin must be active and atleast version %s for Mix and Match: All Products for Subscriptions Compatibility to function</strong>. Please update or activate WooCommerce.', 'wc_mnm_apfs_compatibility' ), self::$required[ 'woo' ] );
			}
				
			if( class_exists( 'WC_Subscriptions' ) && version_compare( WC_Subscriptions::$version, self::$required[ 'subs' ] ) < 0 ) {
				$notices[] = sprintf( __( '<strong>WooCommerce Mix and Match: All Products for Subscriptions Compatibility</strong> mini-extension is inactive. The <strong>WooCommerce Subscriptions</strong> plugin must be active and atleast version %s for Mix and Match: All Products for Subscriptions Compatibility to function</strong>. Please update or activate WooCommerce Subscriptions.', 'wc_mnm_apfs_compatibility' ), self::$required[ 'subs' ] );
			}

			if( ! class_exists( 'WCS_ATT_Helpers' ) || ( class_exists( 'WCS_ATT' ) && version_compare( WCS_ATT::VERSION, self::$required[ 'apfs' ] ) < 0 ) ) {
				$notices[] = sprintf( __( '<strong>WooCommerce Mix and Match: All Products for Subscriptions Compatibility</strong> mini-extension is inactive. The <strong>WooCommerce All Products for Subscriptions</strong> plugin must be active and atleast version %s for Mix and Match: All Products for Subscriptions Compatibility to function</strong>. Please update or activate WooCommerce All Products for Subscriptions.', 'wc_mnm_apfs_compatibility' ), self::$required[ 'apfs' ] );
			}

			if( ! did_action( 'woocommerce_mnm_loaded' ) || version_compare( WC_Mix_and_Match()->version, self::$required[ 'mnm' ] ) < 0 ) {
				$notices[] = sprintf( __( '<strong>WooCommerce Mix and Match: All Products for Subscriptions Compatibility</strong> mini-extension is inactive. The <strong>WooCommerce Mix and Match Products</strong> plugin must be active and atleast version %s for Mix and Match: All Products for Subscriptions Compatibility to function</strong>. Please update or activate WooCommerce Mix and Match Products.', 'wc_mnm_apfs_compatibility' ), self::$required[ 'mnm' ] );
			}

			if( empty( $notices ) ) {
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

			if( is_array( $notices ) ) {
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

// Launch the whole plugin... after APFS compat class.
add_action( 'plugins_loaded', array( 'WC_MNM_APFS_Compatibility', 'init' ), 100 );

