<?php
namespace SmartPostAggregator\Traits;

defined( 'ABSPATH' ) || exit;

trait Screen {

	/**
	 * Whether the current admin screen belongs to this plugin.
	 *
	 * @return bool
	 */
	protected function is_plugin_admin_screen() {
		global $current_screen;

		return isset( $current_screen->base ) && strpos( $current_screen->base, 'smart-post-aggregator' ) !== false;
	}
}
