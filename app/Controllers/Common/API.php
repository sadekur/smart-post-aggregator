<?php
namespace SmartPostAggregator\Controllers\Common;

defined( 'ABSPATH' ) || exit;

use WP_REST_Server;
use SmartPostAggregator\API\Content;
use SmartPostAggregator\Traits\Hook;
use SmartPostAggregator\Traits\Auth;
use SmartPostAggregator\Traits\Rest;

class API {

	use Hook;
	use Auth;
	use Rest;

	/**
	 * Constructor to add all hooks.
	 */
	public function __construct() {
		$this->action( 'rest_api_init', array( $this, 'register_endpoints' ) );
	}

	public function register_endpoints() {

		/**
		 * Aggregated content APIs
		 */
		register_rest_route(
			$this->namespace,
			'/content',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( new Content(), 'list' ),
				'permission_callback' => array( $this, 'is_admin' ),
			)
		);
	}
}
