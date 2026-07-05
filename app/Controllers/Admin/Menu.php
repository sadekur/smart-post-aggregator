<?php
namespace SmartPostAggregantor\Controllers\Admin;

defined( 'ABSPATH' ) || exit;

use SmartPostAggregantor\Traits\Hook;
use SmartPostAggregantor\Traits\Asset;
use SmartPostAggregantor\Traits\Menu as Menu_Trait;
use SmartPostAggregantor\Helpers\Utility;

class Menu {

	use Hook;
	use Asset;
	use Menu_Trait;

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		$this->action( 'admin_enqueue_scripts', array( $this, 'add_assets' ) );
		$this->action( 'admin_menu', array( $this, 'register' ) );
	}

	public function add_assets() {
		global $current_screen;

		if ( strpos( $current_screen->base, 'smart-post-aggregantor' ) !== false ) {

			$this->enqueue_script(
				'smart-post-aggregantor_main-menu',
				SPA_PLUGIN_URL . 'spa/build/admin.bundle.js',
				array( 'wp-element', 'smart-post-aggregantor_common' )
			);
		}

		if ( strpos( $current_screen->base, 'smart-post-aggregantor' ) !== false ) {

			$this->enqueue_style(
				'smart-post-aggregantor_settings',
				SPA_ASSETS_URL . 'admin/css/settings.css'
			);

			$this->enqueue_script(
				'smart-post-aggregantor_settings',
				SPA_ASSETS_URL . 'admin/js/settings.js'
			);
		}
	}

	public function register() {
		$this->add_menu(
			__( 'Smart Post Aggregator', 'smart-post-aggregantor' ),
			__( 'Smart Post Aggregator', 'smart-post-aggregantor' ),
			'manage_options',
			'smart-post-aggregantor',
			array( $this, 'callback_main_menu' ),
			'dashicons-wordpress',
			2
		);

		$this->add_submenu(
			'smart-post-aggregantor',
			__( 'Dashboard', 'smart-post-aggregantor' ),
			__( 'Dashboard', 'smart-post-aggregantor' ),
			'manage_options',
			'smart-post-aggregantor',
			function () {}
		);

		$this->add_submenu(
			'smart-post-aggregantor',
			__( 'Help', 'smart-post-aggregantor' ),
			__( 'Help', 'smart-post-aggregantor' ),
			'manage_options',
			'smart-post-aggregantor#/help',
			function () {}
		);

		$this->add_submenu(
			'smart-post-aggregantor',
			__( 'Settings', 'smart-post-aggregantor' ),
			__( 'Settings', 'smart-post-aggregantor' ),
			'manage_options',
			'smart-post-aggregantor-settings',
			array( $this, 'callback_submenu' ),
		);
	}

	public function callback_main_menu() {
		printf(
			'<div class="wrap">
				<h2>%1$s</h2>
				<div id="smart-post-aggregantor_render">%2$s</div>
			</div>',
			'Smart Post Aggregator',
			__( 'Loading..', 'smart-post-aggregantor' )
		);
	}

	public function callback_submenu() {
		echo Utility::get_template( 'settings/layout.php' );
	}
}
