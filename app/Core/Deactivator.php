<?php
namespace SmartPostAggregator\Core;

defined( 'ABSPATH' ) || exit;

use SmartPostAggregator\Controllers\Common\Cron;

class Deactivator {

	/**
	 * Run deactivation routines.
	 */
	public static function deactivate() {
		$deactivater = new self();

		$deactivater->remove_db_version();
		$deactivater->remove_cron();

		flush_rewrite_rules();
	}

	/**
	 * Remove the database version from the options table.
	 */
	protected function remove_db_version() {
		delete_option( 'smart-post-aggregator_db_version' );
	}

	/**
	 * Unschedules the source-fetch sweep so it doesn't keep firing while the
	 * plugin is inactive.
	 */
	protected function remove_cron() {
		wp_clear_scheduled_hook( Cron::SWEEP_HOOK );
	}
}
