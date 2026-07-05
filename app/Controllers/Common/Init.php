<?php
namespace SmartPostAggregator\Controllers\Common;

defined( 'ABSPATH' ) || exit;

use SmartPostAggregator\Traits\Hook;

class Init {

	use Hook;

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		$this->action( 'wp_head', array( $this, 'modal' ) );
		$this->action( 'admin_head', array( $this, 'modal' ) );
	}

	public function modal() {
		echo '
		<div id="smart-post-aggregator-modal" style="display: none">
			<img id="smart-post-aggregator-modal-loader" src="' . esc_attr( SPA_ASSETS_URL . 'common/img/loader.gif' ) . '" />
		</div>';
	}
}
