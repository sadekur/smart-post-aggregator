<?php
namespace SmartPostAggregantor\Controllers\Admin;

defined( 'ABSPATH' ) || exit;

use SmartPostAggregantor\Traits\Hook;
use SmartPostAggregantor\Traits\Asset;

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
			'smart-post-aggregantor_admin',
			SPA_PLUGIN_URL . 'assets/admin/js/init.js'
		);

		global $spa_menus;

		$this->localize_script(
			'smart-post-aggregantor_admin',
			'spa_PLUGIN_ADMIN',
			array(
				'menus'    => $spa_menus,
				'api_base' => rest_url( 'smart-post-aggregantor/v1' ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
			)
		);
	}
}
