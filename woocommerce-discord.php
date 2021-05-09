<?php
/**
 * Plugin Name: WooCommerce Discord
 * Plugin URI: #
 * Description: Connect discord accounts on checkout page on selected products from woocoomerce and give them access to Discord role
 * Author: Frederick Calderon
 * Author URI: https://github.com/engelcalderon
 * Version: 1.0.2
 *
 * @package		WooCommerce Discord
 * @author		Frederick.
 * @since		1.0
 */

/**
 * Check if WooCommerce is activated
 */
if ( ! function_exists( 'is_plugin_activated' ) ) {
	function is_plugin_activated($pluginPath) {
        if ( in_array( $pluginPath, apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            return true;
        } else {
            return false;
        }
	}
}

function woocommerce_required_notice() {
    $class = 'notice notice-error';
    $message = __( 'You need to enable Woocommerce in order to use this plugin', 'sample-text-domain' );
 
    printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
}

function woocommerce_subscriptions_required_notice() {
    $class = 'notice notice-error';
    $message = __( 'You need to enable Woocommerce Subscriptions in order to use this plugin', 'sample-text-domain' );
 
    printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
}

function bot_token_required_notice() {
    $class = 'notice notice-error';
    $message = __( 'Add your bot token on the Discord Bot tab', 'sample-text-domain' );
 
    printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
}

if (!is_plugin_activated('woocommerce/woocommerce.php')) {
    add_action( 'admin_notices', 'woocommerce_required_notice' );
}
if (!is_plugin_activated('woocommerce-subscriptions/woocommerce-subscriptions.php')) {
    add_action( 'admin_notices', 'woocommerce_subscriptions_required_notice' );
}

$botToken = get_option('bot_token');

if ($botToken == false) {
    add_action( 'admin_notices', 'bot_token_required_notice' );
}

include __DIR__.'/vendor/autoload.php';
include_once WP_PLUGIN_DIR .'/woocommerce/woocommerce.php';
use RestCord\DiscordClient;

$discord = new DiscordClient(['token' => $botToken != false ? $botToken : '']);

require_once __DIR__.'/includes/admin/class-wc-discord-admin.php';
require_once __DIR__.'/includes/class-discord-user-auth.php';
require_once __DIR__.'/includes/class-wc-discord-checkout.php';
require_once __DIR__.'/includes/class-wc-discord.php';

WC_Discord_Admin::init();
WC_Discord_Checkout::init();
WC_Discord::init();


function deactivate_activate_woocommerce_discord() {
    flush_rewrite_rules();
}

register_activation_hook(__FILE__,'deactivate_activate_woocommerce_discord' );
register_deactivation_hook(__FILE__,'deactivate_activate_woocommerce_discord' );


?>