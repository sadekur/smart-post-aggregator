<?php
namespace SmartPostAggregator\Core;

defined( 'ABSPATH' ) || exit;

use SmartPostAggregator\Models\Database;

class Deactivator {

	/**
	 * Run deactivation routines.
	 */
	public static function deactivate() {
		$deactivater = new self();

		$deactivater->remove_db_version();
	}

	/**
	 * Remove the database version from the options table.
	 */
	protected function remove_db_version() {
		delete_option( 'smart-post-aggregator_db_version' );
	}
}
