<?php
namespace SmartPostAggregator\Controllers\Common;

defined( 'ABSPATH' ) || exit;

use SmartPostAggregator\Traits\Hook;
use SmartPostAggregator\Traits\Asset;

/**
 * Single place for every script/style this plugin enqueues, across admin, frontend,
 * and shared contexts. Controllers should hook business logic, not enqueue assets.
 */
class Assets {

	use Hook;
	use Asset;

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		$this->action( 'admin_enqueue_scripts', array( $this, 'common_assets' ) );
		$this->action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
		$this->action( 'wp_enqueue_scripts', array( $this, 'common_assets' ) );
	}

	/**
	 * Whether the current admin screen belongs to this plugin.
	 *
	 * @return bool
	 */
	private function is_plugin_admin_screen() {
		global $current_screen;

		return isset( $current_screen->base ) && strpos( $current_screen->base, 'smart-post-aggregator' ) !== false;
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
			SPA_PLUGIN_URL . 'spa/build/tailwind.bundle.js'
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
			'spa_PLUGIN_ADMIN',
			array(
				'menus'     => $spa_menus,
				'api_base'  => rest_url( 'smart-post-aggregator/v1' ),
				'rest_root' => rest_url(),
				'nonce'     => wp_create_nonce( 'wp_rest' ),
			)
		);

		$this->enqueue_script(
			'smart-post-aggregator_main-menu',
			SPA_PLUGIN_URL . 'spa/build/admin.bundle.js',
			array( 'wp-element', 'smart-post-aggregator_common' )
		);

		$this->enqueue_style(
			'smart-post-aggregator_settings',
			SPA_ASSETS_URL . 'admin/css/settings.css'
		);

		$this->enqueue_script(
			'smart-post-aggregator_settings',
			SPA_ASSETS_URL . 'admin/js/settings.js'
		);
	}
}
