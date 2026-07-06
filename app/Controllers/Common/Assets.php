<?php
namespace SmartPostAggregator\Controllers\Common;

defined( 'ABSPATH' ) || exit;

use SmartPostAggregator\Traits\Hook;
use SmartPostAggregator\Traits\Asset;
use SmartPostAggregator\Traits\Screen;

/**
 * Single place for every script/style this plugin enqueues, across admin, frontend,
 * and shared contexts. Controllers should hook business logic, not enqueue assets.
 */
class Assets {

	use Hook;
	use Asset;
	use Screen;

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		$this->action( 'admin_enqueue_scripts', array( $this, 'common_assets' ) );
		$this->action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
		$this->action( 'wp_enqueue_scripts', array( $this, 'common_assets' ) );
		$this->action( 'admin_head', array( $this, 'hide_admin_notices' ) );
	}

	/**
	 * Suppress every WP core and third-party admin notice on this plugin's own
	 * screens, since they render inside a full-screen React app with no room
	 * for banners. Runs on `admin_head`, which fires before `admin_notices`/
	 * `all_admin_notices` are triggered later in the page render.
	 */
	public function hide_admin_notices() {

		if ( ! $this->is_plugin_admin_screen() ) {
			return;
		}

		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'all_admin_notices' );
		remove_all_actions( 'network_admin_notices' );
	}

	/**
	 * Assets shared by this plugin's own admin screens and the public-facing site.
	 */
	public function common_assets() {

		if ( is_admin() && ! $this->is_plugin_admin_screen() ) {
			return;
		}

		$this->enqueue_script(
			'tailwind-css',
			SPA_PLUGIN_URL . 'build/tailwind.bundle.js'
		);

		$this->enqueue_script(
			'smart-post-aggregator_common',
			SPA_ASSETS_URL . 'common/js/init.js'
		);

		$this->enqueue_style(
			'smart-post-aggregator_common',
			SPA_ASSETS_URL . 'common/css/init.css'
		);
	}

	/**
	 * Assets used only on this plugin's own admin screens.
	 */
	public function admin_assets() {

		if ( ! $this->is_plugin_admin_screen() ) {
			return;
		}

		$this->enqueue_script(
			'smart-post-aggregator_admin',
			SPA_PLUGIN_URL . 'assets/admin/js/init.js'
		);

		global $spa_menus;

		$this->localize_script(
			'smart-post-aggregator_admin',
			'SPA_PLUGIN_ADMIN',
			array(
				'menus'    => $spa_menus,
				'api_base' => rest_url( 'smart-post-aggregator/v1' ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
			)
		);

		$this->enqueue_script(
			'smart-post-aggregator_main-menu',
			SPA_PLUGIN_URL . 'spa/build/admin.bundle.js',
			array( 'wp-element', 'smart-post-aggregator_common' )
		);
	}
}
