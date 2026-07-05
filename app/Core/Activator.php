<?php
namespace SmartPostAggregator\Core;

defined( 'ABSPATH' ) || exit;

use SmartPostAggregator\Controllers\Common\Cron;

class Activator {

	/**
	 * Static method for plugin activation tasks.
	 */
	public static function activate() {
		$activator = new self();

		$activator->set_cron();

		// Set a flag that indicates the plugin has been activated
		update_option( 'smart-post-aggregator_activated', true );
	}

	/**
	 * Schedules the source-fetch sweep immediately on activation, rather than
	 * waiting for the `Cron` controller's own `plugins_loaded`-time scheduling
	 * to happen on the next page load.
	 */
	public function set_cron() {
		add_filter( 'cron_schedules', array( Cron::class, 'register_interval' ) );

		if ( ! wp_next_scheduled( Cron::SWEEP_HOOK ) ) {
			wp_schedule_event( time(), Cron::INTERVAL, Cron::SWEEP_HOOK );
		}
	}
}
