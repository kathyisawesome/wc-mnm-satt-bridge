<?php
/**
 * Plugin Name: WooCommerce Mix and Match + All Products for Subscriptions Bridge
 * Plugin URI: http://www.woocommerce.com/products/woocommerce-mix-and-match-products/
 * Description: Adds All Products for Subscriptions support for Mix and Match per-item pricing.
 * Version: 1.0.3
 * Author: Kathy Darling
 * Author URI: http://kathyisawesome.com/
 * WC requires at least: 3.6.0
 * WC tested up to: 3.8.0
 *
 *
 * Copyright: © 2019 Kathy Darling
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */


/**
 * Initialize plugin.
 *
 * @return void
 */
function wc_mnm_satt_satt_bridge() {
	$version = function_exists( 'WC_Mix_and_Match' ) ? WC_Mix_and_Match()->version : '';
	if( class_exists( 'WCS_ATT_Integrations' ) && version_compare( $version, '1.4.0', '>=' ) ) {
		// SATT 2.2.0 moved this filter to new WCS_ATT_Integration_PB_CP class.
		if( is_callable( 'WCS_ATT_Integration_PB_CP', 'get_product_bundle_schemes' ) ) {
			remove_filter( 'wcsatt_product_subscription_schemes', array( 'WCS_ATT_Integration_PB_CP', 'get_product_bundle_schemes' ), 10, 2 );
		} else {
			remove_filter( 'wcsatt_product_subscription_schemes', array( 'WCS_ATT_Integrations', 'get_product_bundle_schemes' ), 10, 2 );
		}
		remove_filter( 'wcsatt_product_subscription_schemes', array( 'WCS_ATT_Integrations', 'get_product_bundle_schemes' ), 10, 2 );
		add_filter( 'wcsatt_product_subscription_schemes', 'wc_mnm_satt_get_product_bundle_schemes', 10, 2 );
		add_action( 'wp_enqueue_scripts', 'wc_mnm_satt_enqueue_scripts' );
		add_action( 'woocommerce_mix-and-match_add_to_cart', 'wc_mnm_satt_load_sccript', 20 );
	}
}
add_action( 'plugins_loaded', 'wc_mnm_satt_satt_bridge', 20 );



/**
 * Sub schemes attached on a Product Bundle should not work if the bundle contains a non-convertible product, such as a "legacy" subscription.
 *
 * WCS_ATT_Integration_PB_CP::bundle_contains_subscription() is private and can't be used here, so duplicate it's logic.
 *
 * @param  array       $schemes
 * @param  WC_Product  $product
 * @return array
 */
function wc_mnm_satt_get_product_bundle_schemes( $schemes, $product ) {

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
function wc_mnm_satt_enqueue_scripts() {
	wp_register_script( 'wc-add-to-cart-mnm-satt', plugins_url( '/js/wc-mnm-satt-bridge.js', __FILE__ ), array( 'jquery', 'jquery-blockui', 'wc-add-to-cart-mnm', 'wcsatt-single-product' ), '1.0.3', true );
}

/**
 * Load our custom script on Mix and Match products.
 *
 * @return void
 */
function wc_mnm_satt_load_sccript() {
	wp_enqueue_script( 'wc-add-to-cart-mnm-satt' );
}


