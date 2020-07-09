<?php
/**
 * WC_MNM_APFS_Per_Item_Pricing class
 * Adds support for per-item priced MNM containers to APFS subscriptions
 *
 * @author   Kathy Darling <kathy@kathyisawesome.com>
 * @package  WooCommerce Mix and Match Products: All Products For Subscriptions Compatibility
 * @since    2.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Main WC_MNM_APFS_Per_Item_Pricing class
 **/
if ( ! class_exists( 'WC_MNM_APFS_Per_Item_Pricing' ) ) :

	class WC_MNM_APFS_Per_Item_Pricing {

		/**
		 * Initialize.
		 */
		public static function init() {
			self::add_hooks();
		}

		/**
		 * Hooks for MNM/APFS Per-Item Pricing Compat.
		 */
		private static function add_hooks() {

			// SATT 2.2.0 moved this filter to new WCS_ATT_Integration_PB_CP class.
			if( is_callable( 'WCS_ATT_Integration_PB_CP', 'get_product_bundle_schemes' ) ) {
				remove_filter( 'wcsatt_product_subscription_schemes', array( 'WCS_ATT_Integration_PB_CP', 'get_product_bundle_schemes' ), 10, 2 );
			} else {
				remove_filter( 'wcsatt_product_subscription_schemes', array( 'WCS_ATT_Integrations', 'get_product_bundle_schemes' ), 10, 2 );
			}
			remove_filter( 'wcsatt_product_subscription_schemes', array( 'WCS_ATT_Integrations', 'get_product_bundle_schemes' ), 10, 2 );
			add_filter( 'wcsatt_product_subscription_schemes', array( __CLASS__, 'get_product_bundle_schemes' ), 10, 2 );

			add_action( 'wcsatt_add_price_filters', array( __CLASS__, 'add_price_filters' ) );
			add_action( 'wcsatt_remove_price_filters', array( __CLASS__, 'remove_price_filters' ) );

			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
			add_action( 'woocommerce_mix-and-match_add_to_cart', array( __CLASS__, 'load_script' ), 20 );

	    }

		/**
		 * Sub schemes attached on a Product Bundle should not work if the bundle contains a non-convertible product, such as a "legacy" subscription.
		 */
		public static function add_price_filters( $context = '' ) {
			if ( in_array( $context, array( 'price', '' ) ) ) {
				add_filter( 'woocommerce_product_get_price', array( __CLASS__, 'filter_price' ), -1, 2 );
				add_filter( 'woocommerce_product_get_price', array( __CLASS__, 'filter_price' ), 101, 2 );
			}
		}

		/**
		 * Sub schemes attached on a Product Bundle should not work if the bundle contains a non-convertible product, such as a "legacy" subscription.
		 */
		public static function remove_price_filters( $context = '' ) {
			if ( in_array( $context, array( 'price', '' ) ) ) {
				remove_filter( 'woocommerce_product_get_price', array( __CLASS__, 'filter_price' ), -1, 2 );
				remove_filter( 'woocommerce_product_get_price', array( __CLASS__, 'filter_price' ), 101, 2 );
			}
		}

		/**
		 * Filter get_price() calls to take scheme price overrides into account.
		 *
		 * @param  double      $price
		 * @param  WC_Product  $product
		 * @return double
		 */
		public static function filter_price( $price, $product ) {

			if ( WCS_ATT_Product::is_subscription( $product ) ) {

				if ( '' === $price && $product->is_type( 'mix-and-match' ) && $product->is_priced_per_product() ) {
					$price = (double) $price;
				}

			}

			return $price;

		}

		/**
		 * Sub schemes attached on a Product Bundle should not work if the bundle contains a non-convertible product, such as a "legacy" subscription.
		 *
		 * WCS_ATT_Integration_PB_CP::bundle_contains_subscription() is private and can't be used here, so duplicate it's logic.
		 *
		 * @param  array       $schemes
		 * @param  WC_Product  $product
		 * @return array
		 */
		public static function get_product_bundle_schemes( $schemes, $product ) {

			if ( $product->is_type( 'bundle' ) && function_exists( 'WC_PB' ) ) {
				if ( version_compare( WC_PB()->version, '5.0.0' ) < 0 ) {
					$contains_subs = $product->contains_sub();
				} else {
					$contains_subs = $product->contains( 'subscription' );
				}
				if( $contains_subs ) {
					$schemes = array();
				}
			}

			return $schemes;
		}

		/**
		 * Register our custom script.
		 *
		 * @return void
		 */
		public static function enqueue_scripts() {
			wp_register_script( 'wc-add-to-cart-mnm-satt', plugins_url( '../js/wc-mnm-apfs-compatibility.js', __FILE__ ), array( 'jquery', 'jquery-blockui', 'wc-add-to-cart-mnm', 'wcsatt-single-product' ), WC_MNM_APFS_Compatibility::VERSION, true );
		}

		/**
		 * Load our custom script on Mix and Match products.
		 *
		 * @return void
		 */
		public static function load_script() {
			wp_enqueue_script( 'wc-add-to-cart-mnm-satt' );
		}

	} // End class: do not remove or there will be no more guacamole for you.

endif; // End class_exists check.

