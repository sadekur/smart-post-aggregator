<?php
namespace SmartPostAggregantor\Core;

defined( 'ABSPATH' ) || exit;

class Activator {

	/**
	 * Static method for plugin activation tasks.
	 */
	public static function activate() {
		$activator = new self();

		$activator->set_cron();

		// Set a flag that indicates the plugin has been activated
		update_option( 'smart-post-aggregantor_activated', true );
	}

	public function set_cron() {
		// code...
	}
}
