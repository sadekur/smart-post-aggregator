<?php
namespace SmartPostAggregator\Controllers\Admin;

defined( 'ABSPATH' ) || exit;

use SmartPostAggregator\Traits\Hook;
use SmartPostAggregator\Traits\Menu as Menu_Trait;
use SmartPostAggregator\Helpers\Utility;

class Menu {

	use Hook;
	use Menu_Trait;

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		$this->action( 'admin_menu', array( $this, 'register' ) );
	}

	public function register() {
		$this->add_menu(
			__( 'Smart Post Aggregator', 'smart-post-aggregator' ),
			__( 'Smart Post Aggregator', 'smart-post-aggregator' ),
			'manage_options',
			'smart-post-aggregator',
			array( $this, 'callback_main_menu' ),
			'dashicons-wordpress',
			2
		);

		$this->add_submenu(
			'smart-post-aggregator',
			__( 'Dashboard', 'smart-post-aggregator' ),
			__( 'Dashboard', 'smart-post-aggregator' ),
			'manage_options',
			'smart-post-aggregator',
			function () {}
		);

		$this->add_submenu(
			'smart-post-aggregator',
			__( 'Help', 'smart-post-aggregator' ),
			__( 'Help', 'smart-post-aggregator' ),
			'manage_options',
			'smart-post-aggregator#/help',
			function () {}
		);

		$this->add_submenu(
			'smart-post-aggregator',
			__( 'Settings', 'smart-post-aggregator' ),
			__( 'Settings', 'smart-post-aggregator' ),
			'manage_options',
			'smart-post-aggregator-settings',
			array( $this, 'callback_submenu' ),
		);
	}

	public function callback_main_menu() {
		printf(
			'<div class="wrap">
				<h2>%1$s</h2>
				<div id="smart-post-aggregator_render">%2$s</div>
			</div>',
			'Smart Post Aggregator',
			__( 'Loading..', 'smart-post-aggregator' )
		);
	}

	public function callback_submenu() {
		echo Utility::get_template( 'settings/layout.php' );
	}
}
