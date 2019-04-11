<?php
/**
 * WooCommerce SuperFaktúra.
 *
 * @package   WooCommerce SuperFaktúra
 * @author    Webikon (Ján Bočínec) <info@webikon.sk>
 * @license   GPL-2.0+
 * @link      http://www.webikon.sk
 * @copyright 2013 Webikon s.r.o.
 */

/**
 * WC_SuperFaktura.
 *
 * @package WooCommerce SuperFaktúra
 * @author  Webikon (Ján Bočínec) <info@webikon.sk>
 */
class WC_SuperFaktura {
    /**
     * Plugin version, used for cache-busting of style and script file references.
     *
     * @since   1.0.0
     *
     * @var     string
     */
    protected $version = '1.8.12';

    /**
     * Unique identifier for your plugin.
     *
     * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
     * match the Text Domain file header in the main plugin file.
     *
     * @since    1.0.0
     *
     * @var      string
     */
    protected $plugin_slug = 'wc-superfaktura';

    /**
     * Instance of this class.
     *
     * @since    1.0.0
     *
     * @var      object
     */
    protected static $instance = null;

    /**
     * Slug of the plugin screen.
     *
     * @since    1.0.0
     *
     * @var      string
     */
    protected $plugin_screen_hook_suffix = null;

    /**
     * Default product description template
     * @var string
     */
    protected $product_description_template_default;

    /**
     * Stored result of detection wc-nastavenia-skcz plugin
     * @var bool
     */
    protected $wc_nastavenia_skcz_activated;

    /**
     * Initialize the plugin by setting localization, filters, and administration functions.
     *
     * @since     1.0.0
     */
    private function __construct()
    {
        // Define custom functionality. Read more about actions and filters: http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
        add_action('init', array($this, 'init'));
        add_action('admin_init', array($this, 'admin_init'));

        // Register hooks for warnings about problematic configuration
        add_action( 'admin_notices', array( __CLASS__, 'order_number_notice_all' ) );
        add_action( 'woocommerce_settings_wc_superfaktura', array( __CLASS__, 'order_number_notice' ) );

        // Load public-facing style sheet and JavaScript.
        //add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

        // backward compatibility with previous (now gone) option
        $this->product_description_template_default = '[ATTRIBUTES]' . ( 'yes' === get_option( 'woocommerce_sf_product_description_visibility', 'yes' ) ? "\n[SHORT_DESCR]" : '' );
    }

    /**
     * Return an instance of this class.
     *
     * @since     1.0.0
     *
     * @return    object    A single instance of this class.
     */
    public static function get_instance() {

        // If the single instance hasn't been set, set it now.
        if ( null == self::$instance ) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Fired when the plugin is activated.
     *
     * @since    1.0.0
     *
     * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
     */
    public static function activate( $network_wide ) {
        // TODO: Define activation functionality here
    }

    /**
     * Fired when the plugin is deactivated.
     *
     * @since    1.0.0
     *
     * @param    boolean    $network_wide    True if WPMU superadmin uses "Network Deactivate" action, false if WPMU is disabled or plugin is deactivated on an individual blog.
     */
    public static function deactivate( $network_wide ) {
        // TODO: Define deactivation functionality here
    }

    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {

        $domain = $this->plugin_slug;
        $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

        load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Register and enqueues public-facing JavaScript files.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        if(is_checkout() || is_account_page())
        {
            wp_enqueue_script('wc-sf-invoice-checkout-js', plugins_url( 'wc-superfaktura.js', __FILE__ ), array('jquery') );
        }
    }

    function admin_init()
    {
        if (isset($_GET['sfi_regen']) || isset($_GET['sf_invoice_proforma_create']) || isset($_GET['sf_invoice_regular_create'])) {
            if(!current_user_can('manage_woocommerce') || !isset($_GET['order'])) {
                wp_die('Unauthorized');
            }

            $order_id = (int)$_GET['order'];
            if (isset($_GET['sfi_regen']))
            {
                $this->sf_regen_invoice($order_id);
            }
            if (isset($_GET['sf_invoice_proforma_create'])) {
                $order = new WC_Order($order_id);
                $this->sf_generate_invoice($order, 'proforma');
            }
            if (isset($_GET['sf_invoice_regular_create'])) {
                $order = new WC_Order($order_id);
                $this->sf_generate_invoice($order, 'regular');
            }

            wp_safe_redirect(admin_url('post.php?post=' . $order_id . '&action=edit'));
            die();
        }
    }

    static function order_number_notice_all()
    {
        // avoid double notice on superfaktura settings tab
        if ( isset( $_GET['page'], $_GET['tab'] ) && 'wc-settings' === $_GET['page'] && 'wc_superfaktura' === $_GET['tab'] ) {
            return;
        }

        self::order_number_notice();
    }

    static function order_number_notice()
    {
        // display warning if we use custom numbering + [ORDER_NUMBER] variable
        // and do not have active plugin Woocommerce Sequential Order Numbers
        if ( ! is_admin() || defined('DOING_AJAX') && DOING_AJAX ) {
            return;
        }

        if ( is_plugin_active( 'woocommerce-sequential-order-numbers/woocommerce-sequential-order-numbers.php' ) ) {
            return;
        }

        if ( is_plugin_active( 'woocommerce-sequential-order-numbers-pro/woocommerce-sequential-order-numbers.php' ) ) {
            return;
        }

        if ( 'no' === get_option( 'woocommerce_sf_invoice_custom_num' ) ) {
            return;
        }

        $tmpl1 = get_option( 'woocommerce_sf_invoice_regular_id' ) ?: '';
        $tmpl2 = get_option( 'woocommerce_sf_invoice_proforma_id' ) ?: '';
        if ( false !== strpos( $tmpl1 . $tmpl2, '[ORDER_NUMBER]' ) )
        {
            ?>
            <div class="notice notice-error is-dismissible">
            <p><b>Woocommerce SuperFaktúra</b>: <?php printf( __( 'You use variable %1$s in your invoice nr. or proforma invoice nr., but the plugin "%2$s" is not activated. This may cause that your invoice numbers will not be sequential.', 'wc-superfaktura' ), '[ORDER_NUMBER]', 'WooCommerce Sequential Order Numbers' ) ?></p>
            </div>
            <?php
        }
    }

    function init()
    {
        // Load plugin text domain
        $this->load_plugin_textdomain();

        $this->wc_nastavenia_skcz_activated = class_exists( 'Webikon\Woocommerce_Plugin\WC_Nastavenia_SKCZ\Plugin', false );

        // woocommerce settings
        add_action( 'woocommerce_get_settings_pages', array( $this, 'woocommerce_settings' ) );

        if ( get_option('woocommerce_sf_add_company_billing_fields', 'yes') == 'yes' && ! $this->wc_nastavenia_skcz_activated ) {
            // woo checkout billing fields
            add_filter('woocommerce_billing_fields', array($this, 'billing_fields'));
        }
        // woo checkout billing fields processing + meta has_shipping
        add_action('woocommerce_checkout_update_order_meta', array($this, 'checkout_order_meta'));

        // metabox hook
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));

        // customer order list actions filter
        add_filter( 'woocommerce_my_account_my_orders_actions', array( $this, 'my_orders_actions' ), 10, 2 );

        $wc_get_order_statuses = $this->get_order_statuses();

        foreach ( $wc_get_order_statuses as $key => $status )
        {
            add_action( 'woocommerce_order_status_'.$key, array( $this, 'sf_new_invoice' ), 5 );
        }

        add_action('woocommerce_checkout_order_processed', array( $this, 'sf_new_invoice' ), 5 );

        add_action( 'woocommerce_email_order_meta', array( $this, 'sf_invoice_link_email' ), 10, 2 );
        add_filter( 'woocommerce_email_attachments', array( $this, 'sf_invoice_attachment_email' ), 10, 3);

        //add_action( 'woocommerce_order_status_on-hold_notification', array( 'WC_Email_Customer_Completed_Order', 'trigger' ) );
        add_action( 'woocommerce_thankyou', array( $this, 'sf_invoice_link_page' ) );
    }

    /**
     * NOTE:  Actions are points in the execution of a page or process
     *        lifecycle that WordPress fires.
     *
     *        WordPress Actions: http://codex.wordpress.org/Plugin_API#Actions
     *        Action Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
     *
     * @since    1.0.0
     */
    public function action_method_name() {
        // TODO: Define your action hook callback here
    }

    public function sf_api()
    {
        $sf_email = get_option('woocommerce_sf_email');
        $sf_key = get_option('woocommerce_sf_apikey');
        $sf_company_id = get_option('woocommerce_sf_company_id');

        $module_id = sprintf('WordPress %s (WC %s, WC SF %s)', get_bloginfo('version'), WC()->version, $this->version);

        switch (get_option('woocommerce_sf_lang', 'sk')) {
            case 'at':
                return new SFAPIclientAT( $sf_email, $sf_key, $_SERVER['SERVER_NAME'], $module_id, $sf_company_id );

            case 'cz':
                return new SFAPIclientCZ( $sf_email, $sf_key, $_SERVER['SERVER_NAME'], $module_id, $sf_company_id );

            default:
                return new SFAPIclient( $sf_email, $sf_key, $_SERVER['SERVER_NAME'], $module_id, $sf_company_id );
        }
    }

    public function sf_clean_invoice_items($invoice_id, $api)
    {
        $response = $api->invoice($invoice_id);
        if(!isset($response->error) || $response->error==0)
        {
            if(isset($response->InvoiceItem) && is_array($response->InvoiceItem))
            {
                foreach($response->InvoiceItem as $item)
                    $api->deleteInvoiceItem($invoice_id, $item->id);
            }
        }
    }

    public function sf_new_invoice($order_id)
    {
        $order = new WC_Order($order_id);

        foreach( array('regular','proforma') as $type )
        {
            if ( ! $invoice_status = $this->generate_invoice_status($this->get_order_field('payment_method',$order), $type) )
                continue;

            if ( $invoice_status != $this->get_order_field('status',$order) )
                continue;

            $this->sf_generate_invoice( $order, $type );
        }
    }

    public function sf_regen_invoice($order_id)
    {
        $order = new WC_Order($order_id);

        foreach( array('regular','proforma') as $type )
        {
            $sf_id = get_post_meta($this->get_order_field('id',$order), 'wc_sf_internal_' . $type . '_id', true);
            if (!empty($sf_id)) {
                $this->sf_generate_invoice( $order, $type );
            }
        }
    }

    private function sf_generate_invoice( $order, $type ) {
        $edit = false;
        $api = $this->sf_api();

        $sf_id = get_post_meta($this->get_order_field('id',$order), 'wc_sf_internal_' . $type . '_id', true);

        // plugin-update checking code
        if ( ! $sf_id ) {
            $old_sf_id = get_post_meta( $this->get_order_field('id',$order), 'wc_sf_internal_id', true );
            if ( $old_sf_id ) {
                // if there is generated regular invoice (link), then it's regular id
                if ( get_post_meta( $this->get_order_field('id',$order), 'wc_sf_invoice_regular', true ) ) {
                    update_post_meta( $this->get_order_field('id',$order), 'wc_sf_internal_regular_id', $old_sf_id );
                    // use only if we are generating regular invoice
                    if ( 'regular' === $type ) {
                        $sf_id = $old_sf_id;
                    }
                }
                // else if there is generated proforma invoice (link), then it's proforma id
                elseif ( get_post_meta( $this->get_order_field('id',$order), 'wc_sf_invoice_proforma', true ) ) {
                    update_post_meta( $this->get_order_field('id',$order), 'wc_sf_internal_proforma_id', $old_sf_id );
                    // use only if we are generating proforma invoice
                    if ( 'proforma' === $type ) {
                        $sf_id = $old_sf_id;
                    }
                }
            }
        }
        // ---------------------------

        if(!empty($sf_id))
        {
            if(!$this->sf_can_regenerate($order)) {
                return false;
            }

            $this->sf_clean_invoice_items($sf_id, $api);
            $edit = true;
        }

        //$invoice_id = get_option('woocommerce_sf_invoice_custom_num')=='yes' ? $this->generate_invoice_id($order->get_id()) : '';
        if ( $this->wc_nastavenia_skcz_activated ) {
            $plugin = Webikon\Woocommerce_Plugin\WC_Nastavenia_SKCZ\Plugin::get_instance();
            $details = $plugin->get_customer_details( $this->get_order_field( 'id', $order ) );
            $ico = $details->get_company_id();
            $ic_dph = $details->get_company_vat_id();
            $dic = $details->get_company_tax_id();
        }
        else {
            $ico = get_post_meta($this->get_order_field('id',$order), 'billing_company_wi_id', true);
            $ic_dph = get_post_meta($this->get_order_field('id',$order), 'billing_company_wi_vat', true);
            $dic = get_post_meta($this->get_order_field('id',$order), 'billing_company_wi_tax', true);
        }
        //$constant = get_option('woocommerce_sf_constant');
        //$specific = get_option('woocommerce_sf_specific');

        $name = ( $this->get_order_field('billing_company',$order) ) ? $this->get_order_field('billing_company',$order) : $this->get_order_field('billing_first_name',$order).' '.$this->get_order_field('billing_last_name',$order);

        if ($this->get_order_field('shipping_company',$order)) {
            if (get_option('woocommerce_sf_invoice_delivery_name') == 'yes') {
                $shipping_name = sprintf('%s - %s %s', $this->get_order_field('shipping_company',$order), $this->get_order_field('shipping_first_name',$order), $this->get_order_field('shipping_last_name',$order));
            }
            else {
                $shipping_name = $this->get_order_field('shipping_company',$order);
            }
        }
        else {
            $shipping_name = $this->get_order_field('shipping_first_name',$order) . ' ' . $this->get_order_field('shipping_last_name',$order);
        }

        //$api->getCountries()

        // $all_countries = WC()->countries->get_countries();

        $billing_address_2 = ( $this->get_order_field('billing_address_2',$order) ) ? ' ' . $this->get_order_field('billing_address_2',$order) : '';

        $client_data = array(
            'name'                      => $name,
            'ico'                       => $ico,
            'dic'                       => $dic,
            'ic_dph'                    => $ic_dph,
            'email'                     => $this->get_order_field('billing_email',$order),
            'address'                   => $this->get_order_field('billing_address_1',$order) . $billing_address_2,
            // 'country'                => $all_countries[$order->get_billing_country()],
            'country_iso_id'            => $this->get_order_field('billing_country',$order),
            'city'                      => $this->get_order_field('billing_city',$order),
            'zip'                       => $this->get_order_field('billing_postcode',$order),
            'phone'                     => $this->get_order_field('billing_phone',$order),
            'update_addressbook'        => (get_option('woocommerce_sf_invoice_update_addressbook', 'no') == 'yes')
        );

        if ( $order->get_formatted_billing_address() != $order->get_formatted_shipping_address() )
        {
            $shipping_address_2 = ( $this->get_order_field('shipping_address_2',$order) ) ? ' ' . $this->get_order_field('shipping_address_2',$order) : '';
            $client_data['delivery_address']        = $this->get_order_field('shipping_address_1',$order)  . $shipping_address_2;
            $client_data['delivery_city']           = $this->get_order_field('shipping_city',$order);
            // $client_data['delivery_country'  => $oder->get_shipping_address_1();
            $client_data['delivery_country_iso_id'] = $this->get_order_field('shipping_country',$order);
            $client_data['delivery_name']           = $shipping_name;
            $client_data['delivery_zip']            = $this->get_order_field('shipping_postcode',$order);
        }

        $client_data = apply_filters( 'sf_client_data', $client_data, $order );

        //nastavime udaje klienta
        $api->setClient($client_data);

        $shipping_methods = $order->get_shipping_methods();
        $shipping_method = reset( $shipping_methods );
        if ( class_exists( 'WC_Shipping_Zones') ) {
            $delivery_type = get_option( 'woocommerce_sf_shipping_' . $shipping_method['method_id'] . ':' . $shipping_method['instance_id'] );
        }
        else {
            $delivery_type = get_option( 'woocommerce_sf_shipping_' . $shipping_method['method_id'] );
        }

        $set_invoice_data = array(
            //vsetky polozky su nepovinne, v pripade ze nie su uvedene, budu doplnene automaticky
            // 'name'                 => 'nazov faktury',
            // 'variable'              => $variable, //variabilný symbol
            // 'constant'             => $constant, //konštantný symbol
            // 'specific'             => $specific, //specificky symbol
            'invoice_currency'      => get_post_meta($this->get_order_field('id',$order), '_order_currency', true), //mena, v ktorej je faktúra vystavená. Možnosti: EUR, USD, GBP, HUF, CZK, PLN, CHF, RUB
            'payment_type'          => get_option( 'woocommerce_sf_gateway_'.$this->get_order_field('payment_method',$order) ),
            'delivery_type'         => $delivery_type,
            // 'created'              => '2013-07-01', //datum vystavenia
            // 'due'                  => '2013-07-01', //datum splatnosti
            'rounding'              => (wc_prices_include_tax()) ? 'item_ext' : 'document',
            'issued_by'             => get_option('woocommerce_sf_issued_by'), //faktúru vystavil
            'issued_by_phone'       => get_option('woocommerce_sf_issued_phone'), //faktúru vystavil telefón
            'issued_by_web'         => get_option('woocommerce_sf_issued_web'), //faktúru vystavil web
            'issued_by_email'       => get_option('woocommerce_sf_issued_email'), //faktúru vystavil email
            'internal_comment'      => $this->get_order_field('customer_note',$order),
            'comment'               => '',
            'order_no'              => $order->get_order_number(), //číslo objednávky
        );
        if (get_option('woocommerce_sf_delivery_date_visibility') == 'no') {
            $set_invoice_data['delivery'] = -1;
        }

        if (get_option('woocommerce_sf_created_date_as_order') == 'yes') {
            $set_invoice_data['created'] = (string)$this->get_order_field('date_created', $order);
        }

        if ( 'order_nr' === get_option( 'woocommerce_sf_variable_symbol' ) ) {
            $set_invoice_data['variable'] = $order->get_order_number();
        }
        // else: pole `variable' zostane prazdne a VS bude cislo novej FA (@SF)

        // if ( $order->get_total_discount() )
        // {
        //     if ( wc_prices_include_tax() ) {
        //         $set_invoice_data['discount_total'] = $order->get_total_discount(false);
        //     } else {
        //         $tax = 1 + round( $order->get_total_tax()/($order->get_total()-$order->get_total_tax()), 2 );
        //         $set_invoice_data['discount_total'] = $order->get_total_discount()*$tax;
        //     }
        // }

        //komentár, poznámka
        if (get_option('woocommerce_sf_comments') == 'yes') {
            $comment_parts = array();

            if ((WC()->countries->get_base_country() != $this->get_order_field('billing_country',$order)) && $ic_dph) {
                $sf_tax_liability = get_option('woocommerce_sf_tax_liability');
                if ($sf_tax_liability) {
                    $comment_parts[] = $sf_tax_liability;
                }
            }

            $sf_comment = get_option('woocommerce_sf_comment');
            if ($sf_comment) {
                $comment_parts[] = $sf_comment;
            }

            if (get_option('woocommerce_sf_comment_add_order_note') == 'yes') {
                $customer_note = $this->get_order_field('customer_note',$order);
                if ($customer_note) {
                    $comment_parts[] = $customer_note;
                }
            }

            $set_invoice_data['comment'] = implode("\r\n\r\n", $comment_parts);
        }

        $set_invoice_data['logo_id'] = get_option('woocommerce_sf_logo_id');
        $bank_account_id = get_option('woocommerce_sf_bank_account_id', null);
        if ($bank_account_id) {
            $set_invoice_data['bank_accounts'] = array(
                array(
                    'id' => $bank_account_id
                )
            );
        }

        switch ($type) {
            case 'regular':
                $set_invoice_data['sequence_id'] = get_option('woocommerce_sf_invoice_sequence_id');
                break;

            case 'proforma':
                $set_invoice_data['sequence_id'] = get_option('woocommerce_sf_proforma_invoice_sequence_id');
                break;
        }

        $set_invoice_data = apply_filters( 'sf_invoice_data', $set_invoice_data, $order );

        //nastavime udaje pre fakturu
        $api->setInvoice($set_invoice_data);
        $api->setInvoiceSettings(array(
            'language' => $this->get_language( $this->get_order_field( 'id', $order ), get_option( 'woocommerce_sf_invoice_language' ), true ),
            'signature' => true,
            'payment_info' => true,
            'bysquare' => get_option( 'woocommerce_sf_bysquare', 'yes' ) == 'yes',
        ));



        $is_paid = false;

        if ( function_exists( 'wc_order_status_manager' ) ) // plugin WooCommerce Order Status Manager
        {
            $order_status = new WC_Order_Status_Manager_Order_Status( $this->get_order_field('status',$order) );
            $is_paid = $order_status->is_paid();
        }
        elseif ( $this->order_is_paid( $order ) )
        {
            $is_paid = true;
        }
        if ($is_paid)
        {
            $api->setInvoice(array(
                'already_paid' => true, // bola uz faktura uhradena?
                'cash_register_id' => get_option('woocommerce_sf_cash_register_'.$this->get_order_field('payment_method',$order))
            ));
        }



        //pridanie polozky na fakturu, metoda moze byt volana viackrat
        //v pripade ze nie ste platca dph, uvadzajte polozku tax = 0

        $items = $order->get_items();

        foreach ( $items as $item_id => $item )
        {
            $product = $order->get_product_from_item($item);

            $processed_item_meta = $item['item_meta'];

            // compatibility with N-Media WooCommerce PPOM plugin
            if (function_exists('ppom_woocommerce_order_key')) {

                $processed_item_meta = array();
                foreach ($item['item_meta'] as $meta_key => $meta_value) {
                    $meta_key = ppom_woocommerce_order_key($meta_key, null, $item);
                    $processed_item_meta[$meta_key] = html_entity_decode(strip_tags($meta_value));
                }
            }

            $item_meta = new WC_Order_Item_Meta($processed_item_meta);

            $item_subtotal = $order->get_item_subtotal( $item, false, false );
            $item_tax = ( $item_subtotal > 0 ) ? round( ( $item['line_subtotal_tax'] / max( 1, $item['qty'] ) ) / $item_subtotal * 100 ) : 0;

            $item_data = array(
                'name'        => html_entity_decode($item['name']),
                //'description' => $product->get_post_data()->post_excerpt,
                //'description' => 'Číslo objednávky: '.$order->get_id(),
                'quantity'    => $item['qty'],
                'sku'         => $product->get_sku(),
                'unit'        => 'ks',
                // 'unit_price'  => $order->get_item_subtotal($item, false, false),
                'unit_price'  => $order->get_item_subtotal($item, true, false) / (1 + $item_tax / 100),
                'tax'         => $item_tax,
            );

            if ( isset( $item['variation_id'] ) &&  $item['variation_id'] > 0 )
                $product_id = $item['variation_id'];
            else
                $product_id = $item['product_id'];

            $product = wc_get_product( $product_id );

            $attributes = $item_meta->meta ? $item_meta->display( true, true, '_', ', ' ) : '';
            $non_variations_attributes = $this->get_non_variations_attributes($item['product_id']);
            $variation = $product instanceof WC_Product_Variation ? $this->convert_to_plaintext( $product->get_variation_description() ) : '';
            $short_descr = $this->convert_to_plaintext( $product->get_post_data()->post_excerpt );
            $template = get_option( 'woocommerce_sf_product_description', $this->product_description_template_default );

            $item_data['description'] = strtr( $template, array(
                '[ATTRIBUTES]' => $attributes,
                '[NON_VARIATIONS_ATTRIBUTES]' => $non_variations_attributes,
                '[VARIATION]' => $variation,
                '[SHORT_DESCR]' => $short_descr,
                '[SKU]' => $product->get_sku(), // this may be different SKU that that above (in case of variable product)
            ) );

            //Fix for WooCommerce Wholesale Pricing plugin
            $wprice = get_post_meta( $product->id, 'wholesale_price', true );

            if ( ! $wprice && $product->is_on_sale() )
            {
                $tax = 1 + (($product->get_price_excluding_tax() == 0) ? 0 : round( (( $product->get_price_including_tax() - $product->get_price_excluding_tax() ) / $product->get_price_excluding_tax()), 2 ));
                // $item_data['unit_price'] = ($tax)? $product->get_regular_price() / $tax : $product->get_regular_price();

                //$zlava = round( ( $product->get_regular_price() - $product->get_sale_price() ) / $product->get_regular_price() * 100 );

                /* 20170801
                $discount = ($tax)? ($product->get_regular_price() - $product->get_sale_price()) / $tax : $product->get_regular_price() - $product->get_sale_price();
                */
                $discount = $product->get_regular_price() - $product->get_sale_price();

                //$item_data['discount'] = '50'; //%
                if ( 'yes' == get_option( 'woocommerce_sf_product_description_show_discount', 'yes' ) && $discount ) {
                    /* 2017/05/19 zlava pre polozku
                    $item_data['discount_no_vat'] = $discount;
                    */

                    /* 20170801
                    $item_data['unit_price'] = ($tax)? $product->get_sale_price() / $tax : $product->get_sale_price();
                    */

                    $item_data['description'] = trim($item_data['description'] . PHP_EOL . get_option( 'woocommerce_sf_discount_name', 'Zľava' ) . ' -' . $discount . ' ' . html_entity_decode(get_woocommerce_currency_symbol()));
                }

                //$item_data['description'] = "Bola poskytnutá zľava. Pôvodná cena: " . $product->get_regular_price() . html_entity_decode( get_woocommerce_currency_symbol() ) . "\r\n";
            }

            $api->addItem($item_data);
        }

        if ( $order->get_fees() )
        {
            foreach ( $order->get_fees() as $fee )
            {
                //poplatky
                $api->addItem(array(
                        'name'        => $fee['name'],
                        'quantity'    => '',
                        'unit'        => '',
                        'unit_price'  => $fee['line_total'],
                        'tax' => round(($fee['line_tax']/$fee['line_total'])*100),
                    ));
            }
        }

        $shipping_price = $this->get_shipping_total($order) + $order->get_shipping_tax();
        $shipping_tax = ( $shipping_price > 0 ) ? round( $order->get_shipping_tax() / $this->get_shipping_total($order) * 100 ) : 0;

        $shipping_item_name = $shipping_price > 0
            ? get_option( 'woocommerce_sf_shipping_item_name', 'Poštovné' )
            : get_option( 'woocommerce_sf_free_shipping_name' );

        if ( $shipping_item_name )
        {
            //poštovné a balné
            $api->addItem(array(
                    'name'        => $shipping_item_name,
                    'quantity'    => '',
                    'unit'        => '',
                    'unit_price'  => $shipping_price / (1 + $shipping_tax / 100),
                    'tax'         => $shipping_tax,
                ));
        }

        if ( $order->get_total_discount() )
        {
            $coupons = array();
            if ( $coupons_codes = $order->get_used_coupons() ) {
                foreach( $coupons_codes as $coupon_code ) {
                    $coupon = new WC_Coupon( $coupon_code );

                    if ( $coupon->is_type( 'fixed_cart' ) ) {
                        $sign = $order->get_order_currency();
                    } elseif ( $coupon->is_type( 'percent' ) ) {
                        $sign = '%';
                    }

                    if ( 'yes' == get_option( 'woocommerce_sf_product_description_show_coupon_code', 'yes' ) ) {
                        $coupons[] = $coupon_code . ' (' . $coupon->coupon_amount . ' ' . $sign . ')';
                    }
                    else {
                        $coupons[] = $coupon->coupon_amount . ' ' . $sign;
                    }
                }

                $description = 'Kupóny: ' . implode( ', ', $coupons );
            }

            $discount_price = $order->get_total_discount(false);
            $discount_tax = round( ($order->get_total_discount(false) - $order->get_total_discount()) / $order->get_total_discount() * 100 );

            $api->addItem(array(
                    'name'        => get_option( 'woocommerce_sf_discount_name', 'Zľava' ),
                    'description' => $description ? $description : '',
                    'quantity'    => '',
                    'unit'        => '',
                    'unit_price'  => ($discount_price / (1 + $discount_tax / 100)) * -1,
                    'tax'         => $discount_tax,
                ));

            // if ( wc_prices_include_tax() ) {
            //     $set_invoice_data['discount_total'] = $order->get_total_discount(false);
            // } else {
            //     $tax = 1 + round( $order->get_total_tax()/($order->get_total()-$order->get_total_tax()), 2 );
            //     $set_invoice_data['discount_total'] = $order->get_total_discount()*$tax;
            // }
        }

        foreach ( apply_filters( 'woocommerce_sf_invoice_extra_items', array(), $order ) as $extra_item ) {
            $api->addItem( $extra_item );
        }

        if($edit)
        {
            $api->setInvoice(array(
                'type' => apply_filters( 'woocommerce_sf_invoice_type', $type, 'edit' ),
                'id' => $sf_id,
            ));

            $response = $api->edit();
        }
        else
        {
            $args = array(
                'type' => apply_filters( 'woocommerce_sf_invoice_type', $type, 'create' ),
            );

            $sequence_id = apply_filters( 'wc_sf_sequence_id', false, $type, $order );

            if ( ! $sequence_id ) {
              $sequence_id = '';
            }

            if ( $sequence_id ) {
              $args['sequence_id'] = $sequence_id;
            } else {
              $invoice_id = apply_filters( 'wc_sf_invoice_id', false, $type, $order );

              if ( ! $invoice_id ) {
                  $invoice_id = get_option('woocommerce_sf_invoice_custom_num')=='yes' ? $this->generate_invoice_id($order,$type) : '';
              }

              $args['invoice_no_formatted'] = $invoice_id;
            }

            $api->setInvoice( $args );

            $response = $api->save();
        }

        if( $response->error === 0 )
        {
            $internal_id = $response->data->Invoice->id;

            update_post_meta($this->get_order_field('id',$order), 'wc_sf_internal_' . $type . '_id', $internal_id);

            $language = $this->get_language( $this->get_order_field( 'id', $order ), get_option( 'woocommerce_sf_invoice_language' ), true );

            $pdf = $api::SFAPI_URL . '/' . $language . '/invoices/pdf/' . $internal_id . '/token:' . $response->data->Invoice->token;

            update_post_meta($this->get_order_field('id',$order), 'wc_sf_invoice_'.$type, $pdf);

            if($edit)
            {

            }
        }
        else
        {
            $pdf = $order->get_view_order_url(); //?
        }
    }



    function get_language( $order_id, $woocommerce_sf_invoice_language, $strict = false )
    {
        $locale_map = array(
            'sk' => 'slo',
            'cs' => 'cze',
            'en' => 'eng',
            'de' => 'deu',
            'ru' => 'rus',
            'uk' => 'ukr',
            'hu' => 'hun',
            'pl' => 'pol',
        );

        $language = $woocommerce_sf_invoice_language;
        switch ( $language ) {
            case 'locale':
                $locale = substr( get_locale(), 0, 2 );
                if ( isset( $locale_map[ $locale ] ) ) {
                    $language = $locale_map[ $locale ];
                }
                break;

            case 'wpml':
                $wpml_language = get_post_meta( $order_id, 'wpml_language', true );
                if ( isset( $locale_map[ $wpml_language ] ) ) {
                    $language = $locale_map[ $wpml_language ];
                }
                break;

            case 'endpoint':
            default:
                // nothing to do
                break;
        }

        if ($strict) {
             if ( ! in_array( $language, $locale_map ) ) {
                $language = ( 'cz' === get_option( 'woocommerce_sf_lang' ) ) ? 'cze' : 'slo';
            }
        }

        return $language;
    }



    /**
     * Get non-variation product attributes
     *
     * @since    1.6.17
     */
    function get_non_variations_attributes($product_id)
    {
        $attributes = get_post_meta($product_id, '_product_attributes');
        if (!$attributes) {
            return false;
        }
        $result = [];
        foreach ($attributes[0] as $attribute) {
            if ($attribute['is_variation']) {
                continue;
            }

            $result[] = $attribute['name'] . ': ' . $attribute['value'];
        }

        return implode(', ', $result);
    }

    /**
     * Save our meta data to an order.
     *
     * @since    1.0.0
     */
    function checkout_order_meta($order_id)
    {
        if(isset($_POST['shiptobilling']) && $_POST['shiptobilling']=='1')
            update_post_meta($order_id, 'has_shipping', '0');
        else
            update_post_meta($order_id, 'has_shipping', '1');


        if(isset($_POST['wi_as_company']) && $_POST['wi_as_company']=='1')
        {
            $valid = array('billing_company_wi_id', 'billing_company_wi_vat', 'billing_company_wi_tax');

            foreach($valid as $attr)
            {
                if(isset($_POST[$attr]))
                    update_post_meta($order_id, $attr, esc_attr($_POST[$attr]));
            }
        }
    }

    /**
     * Add company information fields on checkout page.
     *
     * @since    1.0.0
     */
    function billing_fields($fields)
    {
        $required = false;
        if(get_option('woocommerce_sf_invoice_checkout_required', false)=='yes')
            $required = true;

        $new_fields = array();
        foreach($fields as $key=>$value)
        {
            // add pay as company checkbox
            if($key=='billing_company')
            {
                $new_fields['wi_as_company'] = array(
                    'type' => 'checkbox',
                    'label' => __('Buy as Business client', 'wc-superfaktura'),
                    'class' => array('form-row-wide')
                );
            }

            $new_fields[$key] = $value;

            if($key=='billing_company')
            {
                $new_fields[$key]['required'] = $required;

                if(get_option('woocommerce_sf_invoice_checkout_id', false)=='yes')
                {
                    $new_fields['billing_company_wi_id'] = array(
                        'type' => 'text',
                        'label' => __('ID #', 'wc-superfaktura'),
                        'required' => $required,
                        'class' => array('form-row-wide')
                    );
                }

                if(get_option('woocommerce_sf_invoice_checkout_vat', false)=='yes')
                {
                    $new_fields['billing_company_wi_vat'] = array(
                        'type' => 'text',
                        'label' => __('VAT #', 'wc-superfaktura'),
                        'required' => $required,
                        'class' => array('form-row-wide')
                    );
                }

                if(get_option('woocommerce_sf_invoice_checkout_tax', false)=='yes')
                {
                    $new_fields['billing_company_wi_tax'] = array(
                        'type' => 'text',
                        'label' => __('TAX ID #', 'wc-superfaktura'),
                        'required' => $required,
                        'class' => array('form-row-wide')
                    );
                }
            }
        }

        return $new_fields;
    }



    /**
     * Create tab in WooCommerce settings for this plugin.
     *
     * @since 1.8.0
     */
    public function woocommerce_settings( $settings ) {
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-wc-settings-superfaktura.php';
        $settings[] = new WC_Settings_SuperFaktura();
        return $settings;
    }



    function add_meta_boxes()
    {
        add_meta_box('wc_sf_invoice_box', __('Invoices', 'wc-superfaktura'), array($this, 'add_box'), 'shop_order', 'side');
    }

    function my_orders_actions( $actions, $order )
    {
        $pdf = get_post_meta( $this->get_order_field('id',$order), 'wc_sf_invoice_proforma', true );
        if ( $pdf ) {
            $actions['wc_sf_invoice_proforma'] = array(
                'url' => $pdf,
                'name' => __( 'Proforma', 'wc-superfaktura' ),
            );
        }
        $pdf = get_post_meta( $this->get_order_field('id',$order), 'wc_sf_invoice_regular', true );
        if ( $pdf ) {
            $actions['wc_sf_invoice_regular'] = array(
                'url' => $pdf,
                'name' => __( 'Invoice', 'wc-superfaktura' ),
            );
        }

        return $actions;
    }

    function sf_can_regenerate($order)
    {
        if($this->get_order_field('status',$order)=='completed')
            return false;

        if($this->get_order_field('status',$order)=='processing' && $this->get_order_field('payment_method',$order) != 'cod')
            return false;

        return true;
    }

    function add_box($post)
    {
        $invoice = get_post_meta($post->ID, 'wc_sf_invoice_regular', true);
        $proforma = get_post_meta($post->ID, 'wc_sf_invoice_proforma', true);

        echo '<p><strong>' . __('View Generated Invoices', 'wc-superfaktura') . '</strong>:';
        if(empty($proforma) && empty($invoice))
        {
            echo '<br>' . __('No invoice was generated', 'wc-superfaktura');
        }
        echo '</p>';

        if(!empty($proforma))
        {
            echo '<p><a href="'.$proforma.'" class="button" target="_blank">'.__('Proforma', 'wc-superfaktura').'</a></p>';
        }
        elseif (get_option('woocommerce_sf_invoice_proforma_manual', 'no') == 'yes')
        {
            echo '<p><a href="' . admin_url('admin.php?sf_invoice_proforma_create=1&order=' . $_GET['post']) . '">' . __('Create proforma invoice', 'wc-superfaktura') . '</a></p>';
        }

        if(!empty($invoice))
        {
            echo '<p><a href="'.$invoice.'" class="button" target="_blank">'.__('Invoice', 'wc-superfaktura').'</a></p>';
        }
        elseif (get_option('woocommerce_sf_invoice_regular_manual', 'no') == 'yes')
        {
            echo '<p><a href="' . admin_url('admin.php?sf_invoice_regular_create=1&order=' . $_GET['post']) . '">' . __('Create invoice', 'wc-superfaktura') . '</a></p>';
        }

        if(!empty($proforma) || !empty($invoice)) {
            $order = new WC_Order($post->ID);
            if($this->sf_can_regenerate($order))
            {
                echo '<p><a href="'.admin_url('admin.php?sfi_regen=1&order='.$_GET['post']).'">'.__('Regenerate existing invoices', 'wc-superfaktura').'</a></p>';
            }
        }

        //if(!empty($proforma) || !empty($invoice))
            //echo '<p><a href="'.admin_url('post.php?post='.$_GET['post'].'&action=edit&wc_sf_invoice_resend').'" class="button">'.__('Resend Invoices', 'wc-superfaktura').'</a></p>';
    }

    function generate_invoice_id( $order, $key = 'regular' )
    {
        $order_id = $this->get_order_field('id',$order);

        $invoice_id = get_post_meta($order_id, 'wc_sf_invoice_'.$key.'_id', true);
        if(!empty($invoice_id))
            return $invoice_id;

        $invoice_id_template = get_option('woocommerce_sf_invoice_'.$key.'_id', true);
        if(empty($invoice_id_template))
            $invoice_id_template = '[YEAR][MONTH][COUNT]';

        $num_decimals = get_option('woocommerce_sf_invoice_count_decimals', true);
        if(empty($num_decimals))
            $num_decimals = 4;

        $count = get_option('woocommerce_sf_invoice_'.$key.'_count', true);
        update_option('woocommerce_sf_invoice_'.$key.'_count', intval($count)+1);
        $count = str_pad($count, intval($num_decimals), '0', STR_PAD_LEFT);

        $date = current_time('timestamp');

        $invoice_id = strtr( $invoice_id_template, array(
            '[YEAR]' => date( 'Y', $date ),
            '[MONTH]' => date( 'm', $date ),
            '[DAY]' => date( 'd', $date ),
            '[COUNT]' => $count,
            '[ORDER_NUMBER]' => $order->get_order_number(),
        ) );

        update_post_meta($order_id, 'wc_sf_invoice_'.$key.'_id', $invoice_id);

        return $invoice_id;
    }

    function generate_invoice_status($payment_method, $type = 'regular')
    {
        if($type!='regular' && $type!='proforma')
            $type = 'regular';

        $generate = get_option('woocommerce_sf_invoice_'.$type.'_'.$payment_method);

        // if(!in_array($generate, array('new_order', 'processing', 'completed')))
        //     $generate = false;

        return $generate;
    }

    function sf_invoice_link_page( $order_id )
    {
        if ( get_option('woocommerce_sf_order_received_invoice_link', 'yes') == 'yes' ) {

            if ( $pdf = get_post_meta( $order_id, 'wc_sf_invoice_regular', true ) ) {
                echo "<h2>" . __('Invoice', 'wc-superfaktura') . "</h2>\n\n"
                   . '<a href="' . esc_attr( $pdf ) . '">' . esc_html( $pdf ) . "</a>\n\n";
            }
            elseif ( $pdf = get_post_meta( $order_id, 'wc_sf_invoice_proforma', true ) ) {
                echo "<h2>" . __('Proforma invoice', 'wc-superfaktura') . "</h2>\n\n"
                    . '<a href="' . esc_attr( $pdf ) . '">' . esc_html( $pdf ) . "</a>\n\n";
            }

        }
    }



    function get_invoice_data( $order_id )
    {
        if ( $pdf = get_post_meta( $order_id, 'wc_sf_invoice_regular', true ) ) {
            return array(
                'type' => 'regular',
                'pdf' => $pdf,
                'invoice_id' => (int) get_post_meta( $order_id, 'wc_sf_internal_regular_id', true )
            );
        }

        if ( $pdf = get_post_meta( $order_id, 'wc_sf_invoice_proforma', true ) ) {
            return array(
                'type' => 'proforma',
                'pdf' => $pdf,
                'invoice_id' => (int) get_post_meta( $order_id, 'wc_sf_internal_proforma_id', true )
            );
        }

        return false;
    }



    function sf_invoice_link_email( $order, $sent_to_admin = false )
    {

        if ( $this->get_order_field('payment_method', $order) == 'cod' && get_option('woocommerce_sf_cod_email_skip_invoice', 'no') == 'yes' ) {
            return;
        }

        if ( get_option('woocommerce_sf_email_invoice_link', 'yes') == 'yes' && $invoice_data = $this->get_invoice_data($this->get_order_field('id',$order)) )
        {
            echo "<h2>" . ( ( $invoice_data['type'] == 'regular' ) ? __('Download invoice', 'wc-superfaktura') : __('Download proforma invoice', 'wc-superfaktura') ) . "</h2>\n\n";
            echo '<p><a href="' . esc_attr( $invoice_data['pdf'] ) . '">' . esc_html( $invoice_data['pdf'] ) . "</a></p>\n\n";

            if ( ! $sent_to_admin && ! empty( $invoice_data['invoice_id'] ) ) {
                try {
                    $this->sf_api()->markAsSent( $invoice_data['invoice_id'], $this->get_order_field('billing_email',$order) );
                } catch (Exception $e) {
                    // do not report anything
                }
            }
        }
    }



    function sf_invoice_attachment_email ( $attachments , $status, $order )
    {
        if ( 'WC_Order' != get_class($order)) {
            return $attachments;
        }

        if ( $this->get_order_field('payment_method', $order) == 'cod' && get_option('woocommerce_sf_cod_email_skip_invoice', 'no') == 'yes' ) {
            return $attachments;
        }

        if ( get_option('woocommerce_sf_invoice_pdf_attachment') == 'yes' && $invoice_data = $this->get_invoice_data($this->get_order_field('id',$order)) )
        {
            $tmp_dir = ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir();
            $pdf_path = $tmp_dir . DIRECTORY_SEPARATOR . $invoice_data['invoice_id'] . '.pdf';
            file_put_contents($pdf_path, fopen($invoice_data['pdf'], 'r'));
            $attachments[] = $pdf_path;

            if (! empty( $invoice_data['invoice_id'] ) ) {
                try {
                    $this->sf_api()->markAsSent( $invoice_data['invoice_id'], $this->get_order_field('billing_email',$order) );
                } catch (Exception $e) {
                    // do not report anything
                }
            }
        }

        return $attachments;
    }



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

    function alter_wc_statuses( $array )
    {
        $new_array = array();
        foreach ( $array as $key => $value )
        {
            $new_array[substr($key,3)] = $value;
        }

        return $new_array;
    }

    function get_order_field( $id, $order ) {
        if ( $this->version_check() ) {
            $fn = "get_{$id}";
            return $order->$fn($order);
        } else {
            return $order->{$id};
        }
    }

    function get_shipping_total( $order ) {
        if ( $this->version_check() ) {
            return $order->get_shipping_total();
        } else {
            return $order->order_shipping;
        }
    }

    function version_check( $version = '3.2' ) {
        if ( version_compare( WC()->version, $version, ">=" ) ) {
            return true;
        }

        return false;
    }

    function order_is_paid( $order ) {

        switch ($this->get_order_field( 'status', $order )) {

            case 'processing':
                if (get_option('woocommerce_sf_invoice_regular_processing_set_as_paid', 'no') == 'yes') {
                    $is_paid = true;
                }
                break;

            case 'completed':
                if (get_option('woocommerce_sf_invoice_regular_dont_set_as_paid', 'no') == 'no') {
                    $is_paid = true;
                }
                break;

            default:
                $is_paid = false;
                break;
        }

        return apply_filters( 'woocommerce_sf_order_is_paid', $is_paid, $order );
    }

    function convert_to_plaintext( $string )
    {
        return html_entity_decode( wp_strip_all_tags( $string ), ENT_QUOTES, get_option( 'blog_charset' ) );
    }
}
