/*
Plugin Name: Pickup Days Toggle
Plugin URI: https://github.com/sajidashrafdev/pickup-days-toggle
Description: Toggle pickup days from backend and control Elementor tabs visibility.
Version: 1.1
Author: Sajid Ashraf
Author URI: https://pk.linkedin.com/in/sajidashrafdev
Requires Plugins: woocommerce
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// ===============================
// 1. CREATE SETTINGS MENU
// ===============================
add_action('admin_menu', function() {
    add_menu_page(
        'Pickup Days Settings',
        'Pickup Days',
        'manage_options',
        'pickup-days-settings',
        'pdt_settings_page',
        'dashicons-calendar',
        25
    );
});
