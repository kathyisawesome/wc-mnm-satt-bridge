<?php
/**
 * Plugin Name: WooCommerce Mix and Match + Subscribe All the Things Bridge
 * Plugin URI: http://www.woocommerce.com/products/woocommerce-mix-and-match-products/
 * Description: Adds Subscribe All the Things support for Mix and Match per-item pricing.
 * Version: 1.0.1
 * Author: Kathy Darling
 * Author URI: http://kathyisawesome.com/
 * WC requires at least: 3.0.0
 * WC tested up to: 3.5.3
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
	if( class_exists( 'WCS_ATT_Integrations' ) && version_compare( $version, '1.4.0-beta-1', '>=' ) ) {
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
 * @param  array       $schemes
 * @param  WC_Product  $product
 * @return array
 */
function wc_mnm_satt_get_product_bundle_schemes( $schemes, $product ) {

	if ( $product->is_type( 'bundle' ) && WCS_ATT_Integrations::bundle_contains_subscription( $product ) ) {
		$schemes = array();
	}

	return $schemes;
}

/**
 * Register our custom script.
 *
 * @return void
 */
function wc_mnm_satt_enqueue_scripts() {
	wp_register_script( 'wc-add-to-cart-mnm-satt', plugins_url( '/js/wc-mnm-satt-bridge.js', __FILE__ ), array( 'jquery', 'jquery-blockui', 'wc-add-to-cart-mnm', 'wcsatt-single-product' ), '1.0.0', true );
}

/**
 * Load our custom script on Mix and Match products.
 *
 * @return void
 */
function wc_mnm_satt_load_sccript() {
	wp_enqueue_script( 'wc-add-to-cart-mnm-satt' );
}


