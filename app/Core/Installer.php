<?php
namespace SmartPostAggregator\Core;

defined( 'ABSPATH' ) || exit;

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
	 *
	 * @todo Add the aggregator's own tables here (feed sources, duplicate-detection log)
	 *       once that schema is designed.
	 */
	protected function create_tables() {
	}

	/**
	 * Update or add the database version to the options table.
	 */
	protected function update_db_version() {
		update_option( 'smart-post-aggregator_db_version', SPA_VERSION );
	}
}
