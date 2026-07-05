<?php
/**
 * Plugin Name: Smart Post Aggregator
 * Plugin URI: https://sadekurrahman.net
 * Author: Sadekur Rahman
 * Author URI: https://sadekurrahman.net
 * Description: Aggregate content from multiple sources with intelligent duplicate detection.
 * Version: 0.9
 * Requires at least: 5.0
 * Tested up to: 6.5
 * Requires PHP: 7.4
 * Text Domain: smart-post-aggregantor
 * Domain Path: /languages
 */

namespace SmartPostAggregantor;

defined( 'ABSPATH' ) || exit;

define( 'SPA_FILE', __FILE__ );
define( 'SPA_VERSION', '0.9' );
define( 'SPA_PLUGIN_DIR', plugin_dir_path( SPA_FILE ) );
define( 'SPA_PLUGIN_URL', plugin_dir_url( SPA_FILE ) );
define( 'SPA_ASSETS_URL', SPA_PLUGIN_URL . 'assets/' );

require_once 'vendor/autoload.php';

/**
 * Register the activation hook.
 * This hook is triggered when the plugin is activated.
 * It installs necessary database tables, sets initial seeds, 
 * and checks database versions.
 */
register_activation_hook( SPA_FILE, __NAMESPACE__ . '\\spa_install' );
function spa_install() {
	Core\Installer::install();
}

/**
 * Register the deactivation hook.
 * This hook is triggered when the plugin is activated.
 * It deactivates necessary database tables, sets initial seeds, 
 * and checks database versions.
 */
register_deactivation_hook( SPA_FILE, __NAMESPACE__ . '\\spa_deactivate' );
function spa_deactivate() {
	Core\Deactivator::deactivate();
}

/**
 * Add action for plugins_loaded to activate the plugin.
 * This action is triggered when all active plugins are fully loaded.
 * It sets up cron jobs, registers custom user roles, and performs other 
 * necessary activation tasks.
 */
add_action( 'plugins_loaded', __NAMESPACE__ . '\\spa_activate' );
function spa_activate() {
	Core\Activator::activate();
}

/**
 * Add action for plugins_loaded to initialize the plugin.
 * This action is triggered when all active plugins are fully loaded.
 * It sets the plugin's runtime environment and initializes hooks.
 */
// add_action( 'plugins_loaded', __NAMESPACE__ . '\\spa_initialize' );
// function spa_initialize() {
// 	Core\Initializer::initialize();
// }

/**
 * Init Plugin
 */
if ( class_exists( 'SmartPostAggregantor\\Core\\Initializer' ) ) {
	$init = Core\Initializer::get_instance();
	$init->init();
}
