<?php
/**
 * Plugin Name: WordPress Correios Webservice
 * Plugin URI: https://github.com/voltsdigital/wocommerce-correios-offline
 * Description: Correios Webservice para Woocommerce/WooCommerce
 * Author: Ricardo Haas @voltsdigital
 * Author URI: https://github.com/ricardohaas
 * Version: 0.0.1
 * License: GPLv2 or later
 * Text Domain: wcorreios_webservice
 * Domain Path: /languages/
 */

define( 'WOO_CORREIOS_WEBSERVICE_PATH', plugin_dir_path( __FILE__ ) );
define( 'WOO_CORREIOS_WEBSERVICE_URL', plugin_dir_url( __FILE__ ) );

/**
 * SimpleXML missing notice.
 */
function wcorreios_webservice_extensions_missing_simple_xml_notice( $missing_msg ) {
	echo '<div class="error"><p>' . sprintf( __( 'WooCommerce Correios Webservice depends to %s to work!', 'wcorreios_webservice' ), '<a href="http://php.net/manual/en/book.simplexml.php">SimpleXML</a>' ) . '</p></div>';
}

/**
 * Odin missing notice.
 */
function wcorreios_webservice_extensions_missing_odin_notice( $missing_msg ) {
    echo '<div class="error"><p>' . sprintf( __( 'WooCommerce Correios Webservice depends to %s to work!', 'wcorreios_webservice' ), '<a href="https://github.com/wpbrasil/odin">Odin</a>' ) . '</p></div>';
}

/**
 * Load functions.
 */
function wcorreios_webservice_load() {
    include_once get_stylesheet_directory() . '/core/classes/class-theme-options.php';

	if ( ! class_exists( 'SimpleXmlElement' ) ) {
        add_action( 'admin_notices', 'wcorreios_webservice_extensions_missing_simple_xml_notice' );
        return;
    }

    if ( ! class_exists( 'Odin_Theme_Options' ) ) {
		add_action( 'admin_notices', 'wcorreios_webservice_extensions_missing_odin_notice'  );
		return;
	}

	/**
	 * Load textdomain.
	 */
	load_plugin_textdomain( 'wcorreios_webservice', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	// WC_Correios_Webservice class.
}
include_once WOO_CORREIOS_WEBSERVICE_PATH . 'includes/class-wc-correios-webservice.php';
$wp_correios_webservice = new WP_Correios_Webservice;
register_activation_hook( __FILE__, array( $wp_correios_webservice, 'install' ) );
add_action( 'plugins_loaded', 'wcorreios_webservice_load', 0 );
