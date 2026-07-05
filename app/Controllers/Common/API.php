<?php
namespace SmartPostAggregator\Controllers\Common;

defined( 'ABSPATH' ) || exit;

use WP_REST_Server;
use SmartPostAggregator\API\Content;
use SmartPostAggregator\API\Source;
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

		/**
		 * Source (feed) management APIs
		 */
		register_rest_route(
			$this->namespace,
			'/sources',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( new Source(), 'list' ),
					'permission_callback' => array( $this, 'is_admin' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( new Source(), 'create' ),
					'permission_callback' => array( $this, 'is_admin' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/sources/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( new Source(), 'delete' ),
				'permission_callback' => array( $this, 'is_admin' ),
			)
		);
	}
}
