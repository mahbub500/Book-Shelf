<?php
/**
 * Plugin Name: Book Shelf
 * Plugin URI: https://easycommerce.dev/
 * Author: Mahbub
 * Author URI: https://profiles.wordpress.org/bookshelf/
 * Description: Personal book managment.
 * Version: 0.9
 * Requires at least: 5.0
 * Tested up to: 6.6
 * Requires PHP: 7.4
 * Text Domain: book-shelf
 * Domain Path: /languages
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

namespace BookShelf;

defined( 'ABSPATH' ) || exit;

// Your code continues here


define( 'BOOKSHELF_FILE', __FILE__ );
define( 'BOOKSHELF_VERSION', '0.9' );
define( 'BOOKSHELF_PLUGIN_DIR', plugin_dir_path( BOOKSHELF_FILE ) );
define( 'BOOKSHELF_PLUGIN_URL', plugin_dir_url( BOOKSHELF_FILE ) );
// define( 'BOOKSHELF_ASSETS_URL', BOOKSHELF_PLUGIN_URL . 'assets/' );
// define( 'BOOKSHELF_BUILD_URL', BOOKSHELF_PLUGIN_URL . 'build/' );
// define( 'BOOKSHELF_SPA_URL', BOOKSHELF_PLUGIN_URL . 'spa/' );
define( 'BOOKSHELF_SANDBOX', true );
// define( 'EASYCOMMERCE_STORE', 'http://wp.wp' );
// define( 'EASYCOMMERCE_STORE', 'https://stg.my.easycommerce.dev' );

require_once 'vendor/autoload.php';

/**
 * Register the activation hook.
 * This hook is triggered when the plugin is activated.
 * It installs necessary database tables, sets initial seeds, 
 * and checks database versions.
 */
register_activation_hook( BOOKSHELF_FILE, __NAMESPACE__ . '\\book_shelf_install' );
function book_shelf_install() {
	Bootstrap\Installer::install();
}

/**
 * Add action for plugins_loaded to activate the plugin.
 * This action is triggered when all active plugins are fully loaded.
 * It sets up cron jobs, registers custom user roles, and performs other 
 * necessary activation tasks.
 */
add_action( 'plugins_loaded', __NAMESPACE__ . '\\book_shelf_activate' );
function book_shelf_activate() {
	Bootstrap\Activator::activate();
}

/**
 * Add action for plugins_loaded to initialize the plugin.
 * This action is triggered when all active plugins are fully loaded.
 * It sets the plugin's runtime environment and initializes hooks.
 */
add_action( 'plugins_loaded', __NAMESPACE__ . '\\book_shelf_initialize' );
function book_shelf_initialize() {
	Bootstrap\Initializer::initialize();
}

/**
 * Register the deactivation hook.
 * This hook is triggered when the plugin is deactivated.
 */
register_deactivation_hook( BOOKSHELF_FILE, __NAMESPACE__ . '\\book_shelf_uninstall' );
function book_shelf_uninstall() {
	Bootstrap\Uninstaller::uninstall();
}