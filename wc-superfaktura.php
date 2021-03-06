<?php
/**
 * @package   WooCommerce SuperFaktúra
 * @author    Webikon (Ján Bočínec) <info@webikon.sk>
 * @license   GPL-2.0+
 * @link      http://www.webikon.sk
 * @copyright 2013 Webikon s.r.o.
 *
 * @wordpress-plugin
 * Plugin Name: WooCommerce SuperFaktúra
 * Plugin URI:  http://www.platobnebrany.sk/
 * Description: WooCommerce integrácia služby <a href="http://www.superfaktura.sk/api/">SuperFaktúra.sk</a> Máte s modulom technický problém? Napíšte nám na <a href="mailto:superfaktura@2day.sk">superfaktura@2day.sk</a>
 * Version:     1.8.15
 * Author:      Webikon (Ján Bočínec), 2day.sk
 * Author URI:  http://www.webikon.sk
 * Text Domain: wc-superfaktura
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 * WC requires at least: 2.6.0
 * WC tested up to: 3.2.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) )
{
	die;
}

require_once( plugin_dir_path( __FILE__ ) . 'vendors/SFAPIclient.php' );

require_once( plugin_dir_path( __FILE__ ) . 'class-wc-superfaktura.php' );

// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
register_activation_hook( __FILE__, ['WC_SuperFaktura', 'activate']);
register_deactivation_hook( __FILE__, ['WC_SuperFaktura', 'deactivate']);

WC_SuperFaktura::get_instance();

//pridáva k zoznamu pluginov link na nastavenia superfaktury
function sf_action_links( $links ) {

return array_merge(
		[
			'settings' => '<a href="'. get_admin_url(null, 'admin.php?page=wc-settings&tab=superfaktura') .'">Settings</a>'
        ],
		$links
	);

}
add_filter( 'plugin_action_links_' .plugin_basename( __FILE__), 'sf_action_links' );
