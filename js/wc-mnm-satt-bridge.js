;( function( $ ) {

	// Ensure wcsatt_single_product_params exists to continue.
	if ( typeof wcsatt_single_product_params === 'undefined' ) {
		return false;
	}

	// Mix and Match integration.
	var MNM_Integration = function( container ) {

		var self = this;

		// Moves SATT options after the price.
		this.initialize_ui = function() {

			var $satt_options = container.$mnm_cart.find( '.wcsatt-options-wrapper' );

			if ( $satt_options.length > 0 ) {
				if ( container.$addons_totals !== false ) {
					container.$addons_totals.after( $satt_options );
				} else {
					container.$mnm_price.after( $satt_options );
				}
			}
		};

		// Scans for SATT schemes attached on the Bundle.
		this.initialize_schemes = function() {

			container.satt_schemes = [];

			// Store scheme data for options that override the default prices.
			var $scheme_options = container.$mnm_cart.find( '.wcsatt-options-product .subscription-option' );

			$scheme_options.each( function() {

				var $scheme_option = $( this ),
					scheme_data    = $scheme_option.find( 'input' ).data( 'custom_data' );

				container.satt_schemes.push( {
					el:           $scheme_option,
					data:         scheme_data,
					price_html:   $scheme_option.find( '.subscription-price' ).html(),
					details_html: $scheme_option.find( '.subscription-details' ).prop( 'outerHTML' )
				} );

			} );

		};

		// Init.
		this.integrate = function() {

			self.initialize_ui();
			self.initialize_schemes();

			container.$mnm_form.on( 'wc-mnm-updated-totals', self.update_subscription_totals );

		};

		// Update totals displayed in SATT options.
		this.update_subscription_totals = function( event, container ) {

			if ( container.satt_schemes.length > 0 ) {

				$.each( container.satt_schemes, function( index, scheme ) {

					var scheme_price_html       = container.$mnm_price,
						scheme_price_inner_html = $( scheme_price_html ).html();

					// If only a single option is present, then bundle prices are already overridden on the server side.
					// In this case, simply grab the subscription details from the option and append them to the container price string.
					if ( container.satt_schemes.length === 1 && container.$mnm_cart.find( '.wcsatt-options-product .one-time-option' ).length === 0 ) {

						container.$mnm_price.find( '.price' ).html( scheme_price_inner_html + scheme.details_html );

					// If multiple options are present, then calculate the subscription price for each option that overrides default prices and update its html string.
					} else {

						var $scheme_price = scheme.el.find( '.subscription-price' );

						if ( scheme.data.overrides_price === true ) {

							var price_data = $.extend( true, {}, container.price_data );

							if ( scheme.data.subscription_scheme.pricing_mode === 'inherit' && scheme.data.subscription_scheme.discount > 0 ) {

								$.each( container.child_items, function( index, child_item ) {
									var child_item_id = child_item.get_item_id();

									if ( scheme.data.discount_from_regular ) {
										price_data.prices[ child_item_id ] = price_data.regular_prices[ child_item_id ] * ( 1 - scheme.data.subscription_scheme.discount / 100 );
									} else {
										price_data.prices[ child_item_id ] = price_data.prices[ child_item_id ] * ( 1 - scheme.data.subscription_scheme.discount / 100 );
									}
									
								} );

								price_data.base_price = price_data.base_price * ( 1 - scheme.data.subscription_scheme.discount / 100 );

							} else if ( scheme.data.subscription_scheme.pricing_mode === 'override' ) {
								price_data.base_regular_price = Number( scheme.data.subscription_scheme.regular_price );
								price_data.base_price         = Number( scheme.data.subscription_scheme.price );
							}

							price_data = container.calculate_subtotals( false, price_data );
							price_data = container.calculate_totals( price_data );

							scheme_price_html       = container.get_price_html( price_data );
							scheme_price_inner_html = $( scheme_price_html ).html() + ' ';

						} else {
							scheme_price_inner_html = '';
						}

						if ( container.passes_validation() ) {
							$scheme_price.html( scheme_price_inner_html + scheme.details_html ).find( 'span.total' ).remove();
						} else {
							$scheme_price.html( scheme.price_html );
						}

						$scheme_price.trigger( 'wcsatt-updated-container-price', [ scheme_price_html, scheme, container, self ] );
					}
				} );
			}
		};

		// Lights on.
		this.integrate();
	};

	// Hook into Mix and Match.
	$( '.mnm_form' ).each( function() {
		$( this ).on( 'wc-mnm-initializing', function( event, container ) {
			new MNM_Integration( container );
		} );
	} );

} ) ( jQuery );
