<?php
namespace SmartPostAggregantor\Core;

defined( 'ABSPATH' ) || exit;

use SmartPostAggregantor\Models\Database;

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
		delete_option( 'smart-post-aggregantor_db_version' );
	}
}
