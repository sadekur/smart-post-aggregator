<?php
namespace SmartPostAggregator\Core;

defined( 'ABSPATH' ) || exit;

use SmartPostAggregator\Models\Database;

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

		$db = new Database( 'contacts' );

		// Define columns and options for the new table
		$columns = array(
			'id'         => 'BIGINT(20) NOT NULL AUTO_INCREMENT',
			'name'       => 'VARCHAR(255) NOT NULL',
			'email'      => 'VARCHAR(100) NOT NULL',
			'created_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
		);

		// example options
		$options = array(
			'primary_key' => 'id',
			'unique_keys' => array(
				'unique_email' => 'email',
			),
			'indexes'     => array(
				'index_name' => 'name',
			),
			'engine'      => 'InnoDB',
		);

		// Call the create_table method
		$db->create_table( $columns, $options );
	}

	/**
	 * Update or add the database version to the options table.
	 */
	protected function update_db_version() {
		update_option( 'smart-post-aggregator_db_version', SPA_VERSION );
	}
}
