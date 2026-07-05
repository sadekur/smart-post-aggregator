<?php
namespace SmartPostAggregator\API;

defined( 'ABSPATH' ) || exit;

use SmartPostAggregator\Traits\Rest;
use SmartPostAggregator\Services\Similarity\SimilarityFactory;
use SmartPostAggregator\Services\DuplicateDetector;

/**
 * Scoped read/write for the plugin's own duplicate-detection settings —
 * unlike the removed generic `Option` endpoint, this only ever touches the
 * single `smart-post-aggregator_settings` option and whitelists every value.
 */
class Settings {

	use Rest;

	const ALLOWED_ALGORITHMS  = array( 'jaccard' );
	const ALLOWED_RESOLUTIONS = array( 'mark', 'trash' );

	/**
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function get( $request ) {

		$defaults = array(
			'algorithm'          => SimilarityFactory::DEFAULT_ALGORITHM,
			'threshold'          => DuplicateDetector::DEFAULT_THRESHOLD,
			'review_margin'      => DuplicateDetector::DEFAULT_REVIEW_MARGIN,
			'default_resolution' => DuplicateDetector::DEFAULT_RESOLUTION,
		);

		$saved = get_option( DuplicateDetector::OPTION_KEY, array() );

		return rest_ensure_response( wp_parse_args( is_array( $saved ) ? $saved : array(), $defaults ) );
	}

	/**
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function update( $request ) {

		$algorithm          = (string) $request->get_param( 'algorithm' );
		$default_resolution = (string) $request->get_param( 'default_resolution' );
		$threshold           = (float) $request->get_param( 'threshold' );
		$review_margin       = (float) $request->get_param( 'review_margin' );

		if ( ! in_array( $algorithm, self::ALLOWED_ALGORITHMS, true ) ) {
			return new \WP_Error( 'spa_invalid_algorithm', __( 'Invalid algorithm.', 'smart-post-aggregator' ), array( 'status' => 400 ) );
		}

		if ( ! in_array( $default_resolution, self::ALLOWED_RESOLUTIONS, true ) ) {
			return new \WP_Error( 'spa_invalid_resolution', __( 'Invalid default resolution.', 'smart-post-aggregator' ), array( 'status' => 400 ) );
		}

		$settings = array(
			'algorithm'          => $algorithm,
			'threshold'          => min( 100, max( 0, $threshold ) ),
			'review_margin'      => min( 100, max( 0, $review_margin ) ),
			'default_resolution' => $default_resolution,
		);

		update_option( DuplicateDetector::OPTION_KEY, $settings );

		return rest_ensure_response( $settings );
	}
}
