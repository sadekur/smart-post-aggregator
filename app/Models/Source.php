<?php
namespace SmartPostAggregator\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Data-access class for the `spa_sources` table — the RSS/API feeds being aggregated.
 */
class Source extends Database {

	public function __construct() {
		parent::__construct( 'sources' );
	}

	/**
	 * Creates the `spa_sources` table if it doesn't already exist.
	 */
	public static function install() {
		$source = new self();

		$source->create_table(
			array(
				'id'              => 'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
				'name'            => 'VARCHAR(255) NOT NULL',
				'type'            => "VARCHAR(20) NOT NULL DEFAULT 'rss'",
				'url'             => 'VARCHAR(500) NOT NULL',
				'config'          => 'LONGTEXT NULL',
				'fetch_interval'  => 'INT UNSIGNED NOT NULL DEFAULT 900',
				'last_fetched_at' => 'DATETIME NULL',
				'status'          => "VARCHAR(20) NOT NULL DEFAULT 'active'",
				'retry_count'     => 'INT UNSIGNED NOT NULL DEFAULT 0',
				'next_retry_at'   => 'DATETIME NULL',
				'created_at'      => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
			),
			array(
				'primary_key' => 'id',
				'indexes'     => array(
					'idx_status' => 'status',
				),
			)
		);
	}
}
