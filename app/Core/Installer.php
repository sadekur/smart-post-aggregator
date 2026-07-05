<?php
namespace SmartPostAggregator\Core;

defined( 'ABSPATH' ) || exit;

use SmartPostAggregator\Models\Source;
use SmartPostAggregator\Models\DuplicateLog;

class Installer {

	/**
	 * Run installation routines.
	 */
	public static function install() {
		$installer = new self();

		if ( ! $installer->is_database_up_to_date() ) {
			$installer->create_tables();
			$installer->update_db_version();
		}
	}

	/**
	 * Check if the database is up to date.
	 *
	 * @return bool
	 */
	protected function is_database_up_to_date() {
		$installed_ver = get_option( 'smart-post-aggregator_db_version' );
		return version_compare( $installed_ver, SPA_VERSION, '=' );
	}

	/**
	 * Create database tables.
	 */
	protected function create_tables() {
		Source::install();
		DuplicateLog::install();
	}

	/**
	 * Update or add the database version to the options table.
	 */
	protected function update_db_version() {
		update_option( 'smart-post-aggregator_db_version', SPA_VERSION );
	}
}
