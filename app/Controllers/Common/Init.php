<?php
namespace SmartPostAggregantor\Controllers\Common;

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
		$this->action( 'wp_head', array( $this, 'modal' ) );
		$this->action( 'admin_head', array( $this, 'modal' ) );
		$this->action( 'wp_enqueue_scripts', array( $this, 'add_assets' ) );
		$this->action( 'admin_enqueue_scripts', array( $this, 'add_assets' ) );
	}

	public function modal() {
		echo '
		<div id="smart-post-aggregantor-modal" style="display: none">
			<img id="smart-post-aggregantor-modal-loader" src="' . esc_attr( SPA_ASSETS_URL . 'common/img/loader.gif' ) . '" />
		</div>';
	}

	public function add_assets() {
		global $current_screen;

		if ( isset( $current_screen->base ) && strpos( $current_screen->base, 'smart-post-aggregantor' ) !== false || ! is_admin() ) {

			$this->enqueue_script(
				'tailwind-css',
				SPA_PLUGIN_URL . 'spa/build/tailwind.bundle.js'
			);

			$this->enqueue_script(
				'smart-post-aggregantor_common',
				SPA_ASSETS_URL . 'common/js/init.js'
			);

			$this->enqueue_style(
				'smart-post-aggregantor_common',
				SPA_ASSETS_URL . 'common/css/init.css'
			);
		}
	}
}
