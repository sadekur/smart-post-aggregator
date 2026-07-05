<?php
namespace SmartPostAggregator\Controllers\Admin;

defined( 'ABSPATH' ) || exit;

use SmartPostAggregator\Traits\Hook;
use SmartPostAggregator\Traits\Menu as Menu_Trait;

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

		/**
		 * WordPress always creates a first submenu item matching the parent menu.
		 * Re-registering it here (same parent slug + menu slug) just relabels that
		 * automatic entry instead of showing a duplicate "Smart Post Aggregator" link.
		 * It shares the same admin page hook as the parent, so callback_main_menu()
		 * still renders it — every other screen (help, settings, sources, review,
		 * logs, ...) is a client-side hash route inside that one mounted React app,
		 * not a separate wp-admin submenu.
		 */
		$this->add_submenu(
			'smart-post-aggregator',
			__( 'Dashboard', 'smart-post-aggregator' ),
			__( 'Dashboard', 'smart-post-aggregator' ),
			'manage_options',
			'smart-post-aggregator',
			function () {}
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
}
