<?php
namespace SmartPostAggregator\Core;

defined( 'ABSPATH' ) || exit;

class Initializer {

	/**
	 * Instance
	 */
	private static $instance = null;

	/**
	 * Get Instance (Singleton)
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Init Plugin
	 */
	public function init() {

		do_action( 'spa_before_initialize', $this );

		$this->init_controllers();

		do_action( 'spa_after_initialize', $this );
	}

	/**
	 * Load Controllers
	 */
	private function init_controllers() {

		do_action( 'spa_before_load_controllers' );

		if ( is_admin() ) {
			$this->load_admin_controllers();
		}

		if ( ! is_admin() ) {
			$this->load_frontend_controllers();
		}

		$this->load_common_controllers();

		do_action( 'spa_after_load_controllers' );
	}

	/**
	 * Admin Controllers
	 */
	private function load_admin_controllers() {

		$controller_dir = SPA_PLUGIN_DIR . 'app/Controllers/Admin/';

		if ( ! is_dir( $controller_dir ) ) return;

		$controllers = glob( $controller_dir . '*.php' );
		$controllers = apply_filters( 'spa_admin_controllers', $controllers );

		foreach ( $controllers as $file ) {
			$class_name = basename( $file, '.php' );
			$class = "\\SmartPostAggregator\\Controllers\\Admin\\{$class_name}";

			if ( class_exists( $class ) ) {
				new $class();
			}
		}
	}

	/**
	 * Frontend Controllers
	 */
	private function load_frontend_controllers() {

		$controller_dir = SPA_PLUGIN_DIR . 'app/Controllers/Front/';

		if ( ! is_dir( $controller_dir ) ) return;

		$controllers = glob( $controller_dir . '*.php' );
		$controllers = apply_filters( 'spa_frontend_controllers', $controllers );

		foreach ( $controllers as $file ) {
			$class_name = basename( $file, '.php' );
			$class = "\\SmartPostAggregator\\Controllers\\Front\\{$class_name}";

			if ( class_exists( $class ) ) {
				new $class();
			}
		}
	}

	/**
	 * Common Controllers
	 */
	private function load_common_controllers() {

		$controller_dir = SPA_PLUGIN_DIR . 'app/Controllers/Common/';

		if ( ! is_dir( $controller_dir ) ) return;

		$controllers = glob( $controller_dir . '*.php' );
		$controllers = apply_filters( 'spa_common_controllers', $controllers );

		foreach ( $controllers as $file ) {
			$class_name = basename( $file, '.php' );
			$class = "\\SmartPostAggregator\\Controllers\\Common\\{$class_name}";

			if ( class_exists( $class ) ) {
				new $class();
			}
		}
	}
}