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

	/**
	 * Every submenu below (including this "Dashboard" one) shares the exact
	 * same page slug prefix, so they all resolve server-side to the same
	 * `callback_main_menu()` hook — clicking any of them is a normal wp-admin
	 * navigation, but only the `#/...` fragment differs between them, and
	 * fragments are never sent to the server. That's what makes them work as
	 * real, bookmarkable sidebar links while still being 100% client-side
	 * hash routes inside the one mounted React app underneath.
	 */
	const ROUTES = array(
		''          => 'Dashboard',
		'#/sources' => 'Sources',
		'#/review'  => 'Review',
		'#/logs'    => 'Logs',
		'#/settings' => 'Settings',
	);

	public function register() {
		$this->add_menu(
			__( 'Post Aggregator', 'smart-post-aggregator' ),
			__( 'Post Aggregator', 'smart-post-aggregator' ),
			'manage_options',
			'smart-post-aggregator',
			array( $this, 'callback_main_menu' ),
			'dashicons-wordpress',
			2
		);

		foreach ( self::ROUTES as $hash => $label ) {
			$this->add_submenu(
				'smart-post-aggregator',
				__( $label, 'smart-post-aggregator' ),
				__( $label, 'smart-post-aggregator' ),
				'manage_options',
				'smart-post-aggregator' . $hash,
				function () {}
			);
		}
	}

	public function callback_main_menu() {
		printf(
			'<div class="wrap">
				<h2>%1$s</h2>
				<div id="smart-post-aggregator_render">%2$s</div>
			</div>',
			esc_html__( 'Post Aggregator', 'smart-post-aggregator' ),
			__( 'Loading..', 'smart-post-aggregator' )
		);
	}
}
