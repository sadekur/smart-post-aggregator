<?php
namespace SmartPostAggregator\Controllers\Admin;

defined( 'ABSPATH' ) || exit;

use SmartPostAggregator\Traits\Hook;
use SmartPostAggregator\Traits\Asset;

class Init {

	use Hook;
	use Asset;

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		$this->action( 'admin_enqueue_scripts', array( $this, 'add_assets' ) );
	}

	public function add_assets() {

		$this->enqueue_script(
			'smart-post-aggregator_admin',
			SPA_PLUGIN_URL . 'assets/admin/js/init.js'
		);

		global $spa_menus;

		$this->localize_script(
			'smart-post-aggregator_admin',
			'spa_PLUGIN_ADMIN',
			array(
				'menus'    => $spa_menus,
				'api_base' => rest_url( 'smart-post-aggregator/v1' ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
			)
		);
	}
}
