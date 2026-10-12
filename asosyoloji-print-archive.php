<?php
/**
 * Plugin Name: Asosyoloji Basılı Yayın Arşivi
 * Description: Basılı dergi sayılarını başlık, sayı, yayın tarihi, kapak ve PDF belgesiyle yönetir; widget ve kısa kod ile listeler.
 * Version: 0.1.1
 * Author: Asosyoloji
 * License: GPL-3.0-or-later
 * Text Domain: asosyoloji-print-archive
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ASOPRINT_VERSION', '0.1.1' );
define( 'ASOPRINT_FILE', __FILE__ );
define( 'ASOPRINT_DIR', plugin_dir_path( __FILE__ ) );
define( 'ASOPRINT_URL', plugin_dir_url( __FILE__ ) );

require_once ASOPRINT_DIR . 'inc/post-type.php';
require_once ASOPRINT_DIR . 'inc/meta-box.php';
require_once ASOPRINT_DIR . 'inc/render.php';
require_once ASOPRINT_DIR . 'inc/widget.php';
require_once ASOPRINT_DIR . 'inc/github-updater.php';

/**
 * Register public assets.
 */
function asoprint_register_assets() {
	wp_enqueue_style(
		'asosyoloji-print-archive',
		ASOPRINT_URL . 'assets/archive.css',
		array(),
		ASOPRINT_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'asoprint_register_assets' );

/**
 * Load the media selector on issue editing screens.
 *
 * @param string $hook Current admin hook.
 */
function asoprint_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || 'aso_print_issue' !== $screen->post_type ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_script(
		'asosyoloji-print-archive-admin',
		ASOPRINT_URL . 'assets/admin.js',
		array( 'jquery' ),
		ASOPRINT_VERSION,
		true
	);
}
add_action( 'admin_enqueue_scripts', 'asoprint_admin_assets' );

/**
 * Prepare rewrite rules on activation.
 */
function asoprint_activate() {
	asoprint_register_post_type();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'asoprint_activate' );

/**
 * Refresh rewrite rules on deactivation.
 */
function asoprint_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'asoprint_deactivate' );

