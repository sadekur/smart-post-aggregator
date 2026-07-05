<?php
namespace SmartPostAggregator\Models;

defined( 'ABSPATH' ) || exit;

/**
 * Data-access class for the `spa_duplicate_log` table — every duplicate-detection
 * event (resolved or not), backing the Review, Logs, and Dashboard screens.
 */
class DuplicateLog extends Database {

	public function __construct() {
		parent::__construct( 'duplicate_log' );
	}

	/**
	 * Creates the `spa_duplicate_log` table if it doesn't already exist.
	 */
	public static function install() {
		$log = new self();

		$log->create_table(
			array(
				'id'                => 'BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT',
				'new_post_id'       => 'BIGINT(20) UNSIGNED NOT NULL',
				'matched_post_id'   => 'BIGINT(20) UNSIGNED NULL',
				'algorithm'         => 'VARCHAR(50) NOT NULL',
				'score'             => 'DECIMAL(5,2) NOT NULL',
				'threshold_at_time' => 'DECIMAL(5,2) NOT NULL',
				'resolution'        => "VARCHAR(20) NOT NULL DEFAULT 'queued'",
				'resolved_by'       => 'BIGINT(20) UNSIGNED NULL',
				'created_at'        => 'DATETIME DEFAULT CURRENT_TIMESTAMP',
			),
			array(
				'primary_key' => 'id',
				'indexes'     => array(
					'idx_new_post'   => 'new_post_id',
					'idx_resolution' => 'resolution',
					'idx_created_at' => 'created_at',
				),
			)
		);
	}
}
