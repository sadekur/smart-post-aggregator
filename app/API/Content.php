<?php
namespace SmartPostAggregator\API;

defined( 'ABSPATH' ) || exit;

use WP_Query;
use SmartPostAggregator\Traits\Rest;
use SmartPostAggregator\Config\PostType;
use SmartPostAggregator\Helpers\Utility;

class Content {

	use Rest;

	/**
	 * List aggregated content for the admin dashboard.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function list( $request ) {

		$paged    = max( 1, (int) $request->get_param( 'page' ) );
		$per_page = (int) $request->get_param( 'per_page' );
		$per_page = $per_page > 0 ? $per_page : 8;

		$query = new WP_Query(
			array(
				'post_type'      => PostType::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => $per_page,
				'paged'          => $paged,
			)
		);

		$items = array_map(
			function ( $post ) {
				return array(
					'id'        => $post->ID,
					'title'     => get_the_title( $post ),
					'link'      => get_permalink( $post ),
					'thumbnail' => Utility::get_thumbnail_url( $post->ID ),
				);
			},
			$query->posts
		);

		$response = rest_ensure_response( $items );
		$response->header( 'X-WP-Total', $query->found_posts );
		$response->header( 'X-WP-TotalPages', $query->max_num_pages );

		return $response;
	}
}
