<?php
namespace SmartPostAggregator\API;

defined( 'ABSPATH' ) || exit;

use SmartPostAggregator\Traits\Rest;
use SmartPostAggregator\Models\Source as SourceModel;

/**
 * CRUD for `spa_sources` — the RSS/API feeds the Cron sweep pulls content from.
 */
class Source {

	use Rest;

	const ALLOWED_TYPES = array( 'rss', 'api' );

	/**
	 * List every source for the admin dashboard.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function list( $request ) {
		$model = new SourceModel();

		return rest_ensure_response( $model->get_rows( array(), 100, 0, 'DESC' ) );
	}

	/**
	 * Create a new source.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function create( $request ) {

		$name = sanitize_text_field( (string) $request->get_param( 'name' ) );
		$type = (string) $request->get_param( 'type' );
		$url  = esc_url_raw( (string) $request->get_param( 'url' ) );

		if ( ! in_array( $type, self::ALLOWED_TYPES, true ) ) {
			return new \WP_Error( 'spa_invalid_type', __( 'Invalid source type.', 'smart-post-aggregator' ), array( 'status' => 400 ) );
		}

		if ( '' === $name || '' === $url ) {
			return new \WP_Error( 'spa_missing_fields', __( 'Name and URL are required.', 'smart-post-aggregator' ), array( 'status' => 400 ) );
		}

		// Every source URL gets fetched server-side on a recurring cron sweep,
		// so an unvalidated URL here is a standing SSRF vector (a source could
		// point at an internal service or a cloud metadata endpoint). WP core's
		// own SSRF guard rejects private/loopback/reserved-range hosts unless
		// they match the site's own domain, but it doesn't cover the
		// 169.254.0.0/16 link-local range — notably 169.254.169.254, the
		// cloud metadata IP on AWS/GCP/Azure — so that's checked separately.
		if ( ! wp_http_validate_url( $url ) || $this->resolves_to_link_local( $url ) ) {
			return new \WP_Error( 'spa_invalid_url', __( 'That URL is not allowed as a source.', 'smart-post-aggregator' ), array( 'status' => 400 ) );
		}

		$fetch_interval = (int) $request->get_param( 'fetch_interval' );
		$fetch_interval = $fetch_interval > 0 ? $fetch_interval : 900;

		$config = $request->get_param( 'config' );
		$config = is_array( $config ) ? wp_json_encode( $config ) : null;

		$model = new SourceModel();
		$id    = $model->insert_row(
			array(
				'name'           => $name,
				'type'           => $type,
				'url'            => $url,
				'config'         => $config,
				'fetch_interval' => $fetch_interval,
				'status'         => 'active',
			)
		);

		if ( ! $id ) {
			return new \WP_Error( 'spa_create_failed', __( 'Could not create source.', 'smart-post-aggregator' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response( $model->get_by_id( $id ) );
	}

	/**
	 * Delete a source.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function delete( $request ) {

		$id = (int) $request->get_param( 'id' );

		( new SourceModel() )->delete_row( $id );

		return rest_ensure_response( array( 'deleted' => $id ) );
	}
}
