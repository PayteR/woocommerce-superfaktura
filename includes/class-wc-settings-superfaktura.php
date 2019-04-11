<?php
class WC_Settings_SuperFaktura extends WC_Settings_Page {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.8.0
	 */
	public function __construct() {
		$this->id	= 'superfaktura';
		$this->label = __( 'SuperFaktúra', 'wc-superfaktura' );

		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
		add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );
	}


	/**
	 * Create sections.
	 *
	 * @since 1.8.0
	 * @return array
	 */
	public function get_sections() {

		$sections = array(
			'' => __( 'Authorization', 'wc-superfaktura' ),
			'invoice' => __( 'Invoice', 'wc-superfaktura' ),
			'invoice_creation' => __( 'Invoice Creation', 'wc-superfaktura' ),
			'integration' => __( 'Integration', 'wc-superfaktura' ),
			'payment' => __( 'Payment', 'wc-superfaktura' ),
			'shipping' => __( 'Shipping', 'wc-superfaktura' )
		);

		return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
	}

	/**
	 * Create settings.
	 *
	 * @since 1.8.0
	 * @param string $current_section Optional. Defaults to empty string.
	 * @return array Array of settings
	 */
	public function get_settings( $current_section = '' ) {
		$gateways = WC()->payment_gateways();

		$settings = array();
		switch ( $current_section ) {

			case 'invoice':
				$settings = array(
					array(
						'title' => __('Invoice Options', 'wc-superfaktura'),
						'type' => 'title',
						'desc' => '',
						'id' => 'woocommerce_sf_invoice_title2'
					),
					array(
						'title' => __('Invoice Sequence ID', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_invoice_sequence_id',
						'desc' => '',
						'type' => 'text',
					),
					array(
						'title' => __('Proforma Invoice Sequence ID', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_proforma_invoice_sequence_id',
						'desc' => '',
						'type' => 'text',
					),
					array(
						'title' => __('Custom invoice numbering', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_invoice_custom_num',
						'default' => 'no',
						'type' => 'checkbox'
					),
					array(
						'title' => __('Invoice Nr.', 'wc-superfaktura'),
						'desc' => sprintf(__('Available Tags: %s', 'wc-superfaktura'), '[YEAR], [YEAR_SHORT], [MONTH], [DAY], [COUNT], [ORDER_NUMBER]'),
						'id' => 'woocommerce_sf_invoice_regular_id',
						'default' => '[YEAR][MONTH][COUNT]',
						'type' => 'text',
					),
					array(
						'title' => __('Proforma Invoice Nr.', 'wc-superfaktura'),
						'desc' => sprintf(__('Available Tags: %s', 'wc-superfaktura'), '[YEAR], [YEAR_SHORT], [MONTH], [DAY], [COUNT], [ORDER_NUMBER]'),
						'id' => 'woocommerce_sf_invoice_proforma_id',
						'default' => 'ZAL[YEAR][MONTH][COUNT]',
						'type' => 'text',
					),
					array(
						'title' => __('Current Invoice Number', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_invoice_regular_count',
						'default' => '1',
						'type' => 'number',
						'class' => 'wi-small',
						'custom_attributes' => array(
							'min' => 1,
							'step' => 1
						)
					),
					array(
						'title' => __('Current Proforma Invoice Number', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_invoice_proforma_count',
						'default' => '1',
						'type' => 'number',
						'class' => 'wi-small',
						'custom_attributes' => array(
							'min' => 1,
							'step' => 1
						)
					),
					array(
						'title' => __('Number of digits for [COUNT]', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_invoice_count_decimals',
						'default' => '4',
						'type' => 'number',
						'class' => 'wi-small',
						'custom_attributes' => array(
							'min' => 1,
							'step' => 1
						)
					),
					array(
						'title' => __('Delivery name', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_invoice_delivery_name',
						'default' => 'no',
						'type' => 'checkbox',
						'desc' => __('Use format <em>CompanyName - FirstName LastName</em>', 'wc-superfaktura')
					),
					array(
						'title' => __( 'Invoice language', 'wc-superfaktura' ),
						'id' => 'woocommerce_sf_invoice_language',
						'default' => 'endpoint',
						'type' => 'select',
						'class' => 'wc-enhanced-select',
						'options' => array(
							'endpoint' => __( 'Default endpoint language', 'wc-superfaktura' ),
							'locale' => __( 'Site locale (fallback to endpoint)', 'wc-superfaktura' ),
							'wpml' => __( 'WPML', 'wc-superfaktura' ),
							'slo' => __( 'Slovak', 'wc-superfaktura' ),
							'cze' => __( 'Czech', 'wc-superfaktura' ),
							'eng' => __( 'Εnglish', 'wc-superfaktura' ),
							'deu' => __( 'German', 'wc-superfaktura' ),
							'rus' => __( 'Russian', 'wc-superfaktura' ),
							'ukr' => __( 'Ukrainian', 'wc-superfaktura' ),
							'hun' => __( 'Hungarian', 'wc-superfaktura' ),
							'pol' => __( 'Polish', 'wc-superfaktura' ),
						),
					),
					array(
						'type' => 'sectionend',
						'id' => 'woocommerce_wi_invoice_title2'
					),
					array(
						'title' => __('Invoice Comments', 'wc-superfaktura'),
						'type' => 'title',
						'desc' => '',
						'id' => 'woocommerce_sf_invoice_title8'
					),
					array(
						'title' => __('Allow custom comments', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_comments',
						'default' => 'yes',
						'type' => 'checkbox',
						'desc' => __('Override default comments options in SuperFaktúra. Adds custom comment, order comment and tax liability if needed.', 'wc-superfaktura')
					),
					/* 2017/09/25 presunutie cisla objednavky do z invoice.comment do invoice.order_no
					array(
						'title' => __('Order number', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_order_number_visibility',
						'type' => 'checkbox',
						'desc' => __( 'Display an order number if comments are enabled.', 'wc-superfaktura' ),
						'default' => 'yes',
					),
					*/
					array(
						'title' => __('Comment', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_comment',
						'class' => 'input-text wide-input',
						'css'   => 'width:100%; height: 75px;',
						//'default' => '',
						'type' => 'textarea',
					),
					array(
						'title' => __('Order note', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_comment_add_order_note',
						'default' => 'no',
						'type' => 'checkbox',
						'desc' => __('Add order note from customer to comment.', 'wc-superfaktura')
					),
					//Prenesená daňová povinnosť
					array(
						'title' => __('Tax Liability', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_tax_liability',
						'class' => 'input-text wide-input',
						'default' => 'Dodanie tovaru je oslobodené od dane. Dodanie služby podlieha preneseniu daňovej povinnosti.',
						'type' => 'textarea',
					),
					array(
						'type' => 'sectionend',
						'id' => 'woocommerce_wi_invoice_title8'
					),
					array(
						'title' => __('Additional Invoice Fields', 'wc-superfaktura'),
						'type' => 'title',
						'desc' => '',
						'id' => 'woocommerce_wi_invoice_title3'
					),
					array(
						'title' => __('Variable symbol', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_variable_symbol',
						'type' => 'radio',
						'default' => 'invoice_nr',
						'options' => array(
							'invoice_nr' => __( 'Use invoice number', 'wc-superfaktura' ),
							'order_nr' => __( 'Use order number', 'wc-superfaktura' ),
						),
					),
					array(
						'title' => __('Add field ID #', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_invoice_checkout_id',
						'default' => 'yes',
						'type' => 'checkbox',
						'desc' => ''
					),
					array(
						'title' => __('Add field VAT #', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_invoice_checkout_vat',
						'default' => 'yes',
						'type' => 'checkbox',
						'desc' => ''
					),
					array(
						'title' => __('Add field TAX ID #', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_invoice_checkout_tax',
						'default' => 'yes',
						'type' => 'checkbox',
						'desc' => ''
					),
					array(
						'title' => __('Checkout fields required', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_invoice_checkout_required',
						'default' => 'no',
						'type' => 'checkbox'
					),
				   array(
						'title' => __('PAY by square', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_bysquare',
						'type' => 'checkbox',
						'desc' => __('Display a QR code', 'wc-superfaktura'),
						'default' => 'yes'
					),
					array(
						'title' => __('Issued by', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_issued_by',
						'type' => 'text',
					),
					array(
						'title' => __('Issued by Phone', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_issued_phone',
						'type' => 'text',
					),
					array(
						'title' => __('Issued by Web', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_issued_web',
						'type' => 'text',
					),
					array(
						'title' => __('Issued by Email', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_issued_email',
						'type' => 'text',
					),
					array(
						'title' => __('Created Date', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_created_date_as_order',
						'type' => 'checkbox',
						'desc' => __('Use order date instead of current date', 'wc-superfaktura'),
						'default' => 'no'
					),
					array(
						'title' => __('Delivery Date', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_delivery_date_visibility',
						'type' => 'checkbox',
						'desc' => __('Display a delivery date', 'wc-superfaktura'),
						'default' => 'yes'
					),
					array(
						'title' => __('Product Description', 'wc-superfaktura'),
						'desc' => sprintf(__('Available Tags: %s', 'wc-superfaktura'), '[ATTRIBUTES], [NON_VARIATIONS_ATTRIBUTES], [VARIATION], [SHORT_DESCR], [SKU]'),
						'id' => 'woocommerce_sf_product_description',
						'css'   => 'width:50%; height: 75px;',
						'default' => '[ATTRIBUTES]' . ( 'yes' === get_option( 'woocommerce_sf_product_description_visibility', 'yes' ) ? "\n[SHORT_DESCR]" : '' ),
						'type' => 'textarea',
					),
					array(
						'title' => __('Discount', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_product_description_show_discount',
						'type' => 'checkbox',
						'desc' => __('Show product discount in description', 'wc-superfaktura'),
						'default' => 'yes'
					),
					array(
						'title' => __('Coupon', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_product_description_show_coupon_code',
						'type' => 'checkbox',
						'desc' => __('Show coupon code in description', 'wc-superfaktura'),
						'default' => 'yes'
					),
					array(
						'title' => __('Discount Name', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_discount_name',
						'default' => 'Zľava',
						'desc' => '',
						'type' => 'text',
					),
					array(
						'title' => __('Shipping Item Name', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_shipping_item_name',
						'default' => 'Poštovné',
						'desc' => '',
						'type' => 'text',
					),
					array(
						'title' => __( 'Free Shipping Name', 'wc-superfaktura' ),
						'id' => 'woocommerce_sf_free_shipping_name',
						'default' => '',
						'desc' => '<br>' . __( 'By default, in case of free shipping, the invoice does not contain shipping item; to force the item to appear, fill in its name in this field', 'wc-superfaktura' ),
						'type' => 'text',
					),
					array(
						'type' => 'sectionend',
						'id' => 'woocommerce_wi_invoice_title3'
					),
				);

				$settings = apply_filters( 'superfaktura_invoice_settings', $settings );
				break;



			case 'invoice_creation':
				$wc_get_order_statuses = $this->get_order_statuses();

				$shop_order_status = array( '0' => __('Don\'t generate', 'wc-superfaktura') );
				$shop_order_status = array_merge( $shop_order_status, $wc_get_order_statuses );

				$settings[] = array(
					'title' => __('Invoice Creation', 'wc-superfaktura'),
					'type' => 'title',
					'desc' => __('Select when you would like to create an invoice for each payment gateway.', 'wc-superfaktura'),
					'id' => 'woocommerce_wi_invoice_title4'
				);

				foreach($gateways as $gateway)
				{
					$settings[] = array(
						'title' => $gateway->title,
						'id' => 'woocommerce_sf_invoice_regular_'.$gateway->id,
						'default' => 0,
						'type' => 'select',
						'class' => 'wc-enhanced-select',
						'options' => $shop_order_status
					);
				}

				$settings[] = array(
					'title' => __('Invoice Paid Status ("Processing")', 'wc-superfaktura'),
					'id' => 'woocommerce_sf_invoice_regular_processing_set_as_paid',
					'default' => 'no',
					'type' => 'checkbox',
					'desc' => __('Set invoice as paid for order status "Processing"', 'wc-superfaktura')
				);

				$settings[] = array(
					'title' => __('Invoice Paid Status ("Completed")', 'wc-superfaktura'),
					'id' => 'woocommerce_sf_invoice_regular_dont_set_as_paid',
					'default' => 'no',
					'type' => 'checkbox',
					'desc' => __('Do not set invoice as paid for order status "Completed"', 'wc-superfaktura')
				);

				$settings[] = array(
					'title' => __('Client Data', 'wc-superfaktura'),
					'id' => 'woocommerce_sf_invoice_update_addressbook',
					'default' => 'no',
					'type' => 'checkbox',
					'desc' => __('Update client data in SuperFaktura', 'wc-superfaktura')
				);

				$settings[] = array(
					'title' => __('Manual Invoice Creation', 'wc-superfaktura'),
					'id' => 'woocommerce_sf_invoice_regular_manual',
					'default' => 'no',
					'type' => 'checkbox',
					'desc' => __('Allow manual invoice creation', 'wc-superfaktura')
				);

				$settings[] = array(
					'type' => 'sectionend',
					'id' => 'woocommerce_wi_invoice_title4'
				);

				$settings[] = array(
						'title' => __('Proforma Invoice Creation', 'wc-superfaktura'),
						'type' => 'title',
						'desc' => __('Select when you would like to create a proforma invoice for each payment gateway.', 'wc-superfaktura'),
						'id' => 'woocommerce_wi_invoice_title5'
				);

				foreach($gateways as $gateway)
				{
					$settings[] = array(
						'title' => $gateway->title,
						'id' => 'woocommerce_sf_invoice_proforma_'.$gateway->id,
						'default' => 0,
						'type' => 'select',
						'class' => 'wc-enhanced-select',
						'options' => $shop_order_status
					);
				}

				$settings[] = array(
					'title' => __('Manual Proforma Invoice Creation', 'wc-superfaktura'),
					'id' => 'woocommerce_sf_invoice_proforma_manual',
					'default' => 'no',
					'type' => 'checkbox',
					'desc' => __('Allow manual proforma invoice creation', 'wc-superfaktura')
				);

				$settings[] = array(
					'type' => 'sectionend',
					'id' => 'woocommerce_wi_invoice_title5'
				);

				$settings = apply_filters( 'superfaktura_invoice_creation_settings', $settings );
				break;



			case 'integration':
				$settings = array(
					array(
						'title' => __('Checkout', 'wc-superfaktura'),
						'type' => 'title',
						'desc' => '',
						'id' => 'woocommerce_sf_invoice_title12'
					),
					array(
						'title' => __('Billing fields', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_add_company_billing_fields',
						'default' => 'yes',
						'type' => 'checkbox',
						'desc' => __('Add company billing fields to checkout', 'wc-superfaktura')
					),
					array(
						'type' => 'sectionend',
						'id' => 'woocommerce_wi_invoice_title12'
					),
					array(
						'title' => __('Order received', 'wc-superfaktura'),
						'type' => 'title',
						'desc' => '',
						'id' => 'woocommerce_sf_invoice_title11'
					),
					array(
						'title' => __('Invoice link', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_order_received_invoice_link',
						'default' => 'yes',
						'type' => 'checkbox',
						'desc' => __('Add invoice link to order received screen', 'wc-superfaktura')
					),
					array(
						'type' => 'sectionend',
						'id' => 'woocommerce_wi_invoice_title11'
					),
					array(
						'title' => __('Emails', 'wc-superfaktura'),
						'type' => 'title',
						'desc' => '',
						'id' => 'woocommerce_sf_invoice_title10'
					),
					array(
						'title' => __('Invoice link', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_email_invoice_link',
						'default' => 'yes',
						'type' => 'checkbox',
						'desc' => __('Add invoice link to WooCommerce emails', 'wc-superfaktura')
					),
					array(
						'title' => __('Invoice PDF attachment', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_invoice_pdf_attachment',
						'default' => 'no',
						'type' => 'checkbox',
						'desc' => __('Attach invoice PDF to WooCommerce emails', 'wc-superfaktura')
					),
					array(
						'title' => __('Cash on delivery orders', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_cod_email_skip_invoice',
						'default' => 'no',
						'type' => 'checkbox',
						'desc' => __('Don\'t add invoice to WooCommerce emails for cash on delivery orders', 'wc-superfaktura')
					),
					array(
						'type' => 'sectionend',
						'id' => 'woocommerce_wi_invoice_title10'
					),
				);

				$settings = apply_filters( 'superfaktura_integration_settings', $settings );
				break;



			case 'payment':
				$settings[] = array(
					'title' => __('Payment Methods', 'wc-superfaktura'),
					'type' => 'title',
					'desc' => __('Map Woocommerce payment methods to ones in SuperFaktura', 'wc-superfaktura'),
					'id' => 'woocommerce_wi_invoice_title6'
				);

				$gateway_mapping = array(
					'0'			 => __('Don\'t use', 'wc-superfaktura'),
					'transfer'	  => __('Transfer', 'wc-superfaktura'),
					'cash'		  => __('Cash', 'wc-superfaktura'),
					'paypal'		=> __('Paypal', 'wc-superfaktura'),
					'trustpay'	  => __('Trustpay', 'wc-superfaktura'),
					'besteron'	  => __('Besteron', 'wc-superfaktura'),
					'credit'		=> __('Credit card', 'wc-superfaktura'),
					'debit'		 => __('Debit card', 'wc-superfaktura'),
					'cod'		   => __('Cash on delivery', 'wc-superfaktura'),
					'accreditation' => __('Mutual credit', 'wc-superfaktura'),
					'gopay'		 => __('GoPay', 'wc-superfaktura'),
					'viamo'		 => __('Viamo', 'wc-superfaktura'),
					'postal_order'  => __('Postal money order', 'wc-superfaktura'),
					'other'		 => __('Other', 'wc-superfaktura'),
				);

				foreach($gateways as $gateway)
				{
					$settings[] = array(
						'title' => $gateway->title,
						'id' => 'woocommerce_sf_gateway_'.$gateway->id,
						'default' => 0,
						'type' => 'select',
						'class' => 'wc-enhanced-select',
						'options' => $gateway_mapping
					);
				}

				$settings[] = array(
					'type' => 'sectionend',
					'id' => 'woocommerce_wi_invoice_title6'
				);



				// cash registers

				$settings[] = array(
					'title' => __('Cash Registers', 'wc-superfaktura'),
					'type' => 'title',
					'desc' => __('Map Woocommerce payment methods to cash registers in SuperFaktura', 'wc-superfaktura'),
					'id' => 'woocommerce_wi_invoice_title7'
				);

				foreach($gateways as $gateway)
				{
					$settings[] = array(
						'title' => $gateway->title,
						'id' => 'woocommerce_sf_cash_register_'.$gateway->id,
						'desc' => 'Cash register ID',
						'type' => 'text',
					);
				}

				$settings[] = array(
					'type' => 'sectionend',
					'id' => 'woocommerce_wi_invoice_title7'
				);

				$settings = apply_filters( 'superfaktura_payment_settings', $settings );
				break;



			case 'shipping':
				$shipping_mapping = array(
					'0'			=> __('Don\'t use', 'wc-superfaktura'),
					'mail'		 => __('By mail', 'wc-superfaktura'),
					'courier'	  => __('By courier', 'wc-superfaktura'),
					'personal'	 => __('Personal pickup', 'wc-superfaktura'),
					'haulage'	  => __('Freight', 'wc-superfaktura'),
					'pickup_point' => __('Pickup point', 'wc-superfaktura'),
				);

				if ( class_exists( 'WC_Shipping_Zones') ) {
					$zones = WC_Shipping_Zones::get_zones();

					// rest of the world zone
					$rest = new WC_Shipping_Zone( 0 );
					$zones[0] = $rest->get_data();
					$zones[0]['formatted_zone_location'] = $rest->get_formatted_location();
					$zones[0]['shipping_methods'] = $rest->get_shipping_methods();

					foreach ( $zones as $id => $zone ) {
						$settings[] = array(
							'title' => __( 'Shipping Methods', 'wc-superfaktura' ) . ': ' . $zone['formatted_zone_location'],
							'type' => 'title',
							'id' => 'woocommerce_wi_invoice_title_zone_' . $id,
						);

						foreach ( $zone['shipping_methods'] as $method ) {
							if ( 'no' === $method->enabled ) {
								continue;
							}
							$legacy = get_option( 'woocommerce_sf_shipping_' . $method->id );
							$settings[] = array(
								'title' => $method->title,
								'id' => 'woocommerce_sf_shipping_' . $method->id . ':' . $method->instance_id,
								'default' => empty( $legacy ) ? 0 : $legacy,
								'type' => 'select',
								'class' => 'wc-enhanced-select',
								'options' => $shipping_mapping,
							);
						}

						$settings[] = array(
							'type' => 'sectionend',
							'id' => 'woocommerce_wi_invoice_title_zone_' . $id,
						);
					}
				}
				else {
					$wc_shipping = WC()->shipping();
					$shippings = $wc_shipping->get_shipping_methods();

					if ( $shippings )
					{
						$settings[] = array(
							'title' => __('Shipping Methods', 'wc-superfaktura'),
							'type' => 'title',
							'desc' => 'Map Woocommerce shipping methods to ones in SuperFaktúra.sk',
							'id' => 'woocommerce_wi_invoice_title7'
						);

						foreach($shippings as $shipping)
						{
							if ( $shipping->enabled == 'no' )
								continue;

							$settings[] = array(
								'title' => $shipping->title,
								'id' => 'woocommerce_sf_shipping_'.$shipping->id,
								'default' => 0,
								'type' => 'select',
								'class' => 'wc-enhanced-select',
								'options' => $shipping_mapping
							);
						}

						//array_shift( $shipping_mapping );

						// $settings[] = array(
						//	 'title' => __('Delivery Date', 'wc-superfaktura'),
						//	 'id' => 'woocommerce_sf_delivery_date_visibility',
						//	 'type' => 'multiselect',
						//	 'desc' => 'Display a delivery date only for selected shipping methods.',
						//	 'default' => array_flip( $shipping_mapping ),
						//	 'options' => $shipping_mapping
						// );

						$settings[] = array(
							'type' => 'sectionend',
							'id' => 'woocommerce_wi_invoice_title7'
						);
					}
				}

				$settings = apply_filters( 'superfaktura_shipping_settings', $settings );
				break;



			case '':
			default:
				$settings = array(
					array(
						'title' => __('Authorization', 'wc-superfaktura'),
						'type' => 'title',
						'desc' => __('You can find your API access credentials in your SuperFaktura account at <a href="https://moja.superfaktura.sk/api_access">Tools &gt; API</a>', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_invoice_title1'
					),
					array(
						'title' => __('Version', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_lang',
						'type' => 'radio',
						'desc' => '',
						'default' => 'sk',
						'options' => array( 'sk' => 'SuperFaktura.sk', 'cz' => 'SuperFaktura.cz', 'at' => 'SuperFaktura.at' )
					),
					array(
						'title' => __('API Email', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_email',
						'desc' => '',
						'class' => 'input-text regular-input',
						'type' => 'text',
					),
					array(
						'title' => __('API Key', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_apikey',
						'desc' => '',
						'class' => 'input-text regular-input',
						'type' => 'text',
					),
					array(
						'title' => __('Company ID', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_company_id',
						'desc' => '',
						'type' => 'text',
					),
					array(
						'title' => __('Logo ID', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_logo_id',
						'desc' => '',
						'type' => 'text',
					),
					array(
						'title' => __('Bank Account ID', 'wc-superfaktura'),
						'id' => 'woocommerce_sf_bank_account_id',
						'desc' => '',
						'type' => 'text',
					),
					array(
						'type' => 'sectionend',
						'id' => 'woocommerce_wi_invoice_title1'
					),
				);

				$settings = apply_filters( 'superfaktura_authorization_settings', $settings);
				break;
		}

		return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $current_section );

	}

    /**
     * :TODO:
     *
     * @since 1.8.0
     */
    function get_order_statuses()
    {
        if ( function_exists( 'wc_order_status_manager_get_order_status_posts' ) ) // plugin WooCommerce Order Status Manager
        {
            $wc_order_statuses = array_reduce(
                wc_order_status_manager_get_order_status_posts(),
                function($result, $item)
                {
                    $result[$item->post_name] = $item->post_title;
                    return $result;
                },
                array()
            );

            return $wc_order_statuses;
        }

        if ( function_exists( 'wc_get_order_statuses' ) )
        {
            $wc_get_order_statuses = wc_get_order_statuses();

            return $this->alter_wc_statuses( $wc_get_order_statuses );
        }

        $order_status_terms = get_terms('shop_order_status','hide_empty=0');

        $shop_order_statuses = array();
        if ( ! is_wp_error( $order_status_terms ) )
        {
            foreach ( $order_status_terms as $term )
            {
                $shop_order_statuses[$term->slug] = $term->name;
            }
        }

        return $shop_order_statuses;
    }

    /**
     * :TODO:
     *
     * @since 1.8.0
     */
    function alter_wc_statuses( $array )
    {
        $new_array = array();
        foreach ( $array as $key => $value )
        {
            $new_array[substr($key,3)] = $value;
        }

        return $new_array;
    }



	/**
	 * Output the settings.
	 *
	 * @since 1.8.0
	 */
	public function output() {
		?>

		<div class="updated woocommerce-message">
			<p><?php _e( 'Do you have a technical issue with the plugin? Contact us at <a href="mailto:superfaktura@2day.sk">superfaktura@2day.sk</a>', 'wc-superfaktura' ); ?></p>
		</div>

		<?php
		global $current_section;
		$settings = $this->get_settings( $current_section );
		WC_Admin_Settings::output_fields( $settings );
	}


	/**
 	 * Save settings.
 	 *
 	 * @since 1.8.0
	 */
	public function save() {
		global $current_section;
		$settings = $this->get_settings( $current_section );
		WC_Admin_Settings::save_fields( $settings );
	}
}
