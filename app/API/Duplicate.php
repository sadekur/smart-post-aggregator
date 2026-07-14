<?php
namespace SmartPostAggregator\API;

defined( 'ABSPATH' ) || exit;

use SmartPostAggregator\Traits\Rest;
use SmartPostAggregator\Models\DuplicateLog;
use SmartPostAggregator\Services\DuplicateResolver;
use SmartPostAggregator\Helpers\Utility;

/**
 * Backs the Review inbox: lists posts still awaiting a manual decision and
 * applies whichever action the reviewer picks.
 */
class Duplicate {

	use Rest;

	const ALLOWED_ACTIONS = array( 'approve_unique', 'confirm_duplicate', 'merge', 'ignore' );

	/**
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function list( $request ) {

		$log_model = new DuplicateLog();
		$rows      = $log_model->get_rows( array( array( 'resolution' => 'queued' ) ), 50, 0, 'ASC' );

		$items = array_filter( array_map( array( $this, 'format_row' ), $rows ) );

		return rest_ensure_response( array_values( $items ) );
	}

	/**
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function resolve( $request ) {

		$id     = (int) $request->get_param( 'id' );
		$action = (string) $request->get_param( 'action' );

		if ( ! in_array( $action, self::ALLOWED_ACTIONS, true ) ) {
			return new \WP_Error( 'spa_invalid_action', __( 'Invalid resolution action.', 'smart-post-aggregator' ), array( 'status' => 400 ) );
		}

		$result = ( new DuplicateResolver() )->resolve( $id, $action, get_current_user_id() );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( array( 'resolution' => $result ) );
	}

	/**
	 * @param object $row `spa_duplicate_log` row.
	 * @return array|null
	 */
	protected function format_row( $row ) {

		$new_post = get_post( $row->new_post_id );

		if ( ! $new_post ) {
			return null;
		}

		$matched_post = $row->matched_post_id ? get_post( $row->matched_post_id ) : null;

		return array(
			'log_id'     => (int) $row->id,
			'score'      => (float) $row->score,
			'algorithm'  => $row->algorithm,
			'created_at' => $row->created_at,
			'new'        => $this->post_preview( $new_post ),
			'matched'    => $matched_post ? $this->post_preview( $matched_post ) : null,
		);
	}

	/**
	 * @param \WP_Post $post
	 * @return array
	 */
	protected function post_preview( $post ) {
		return array(
			'id'        => $post->ID,
			'title'     => get_the_title( $post ),
			'excerpt'   => wp_trim_words( wp_strip_all_tags( $post->post_content ), 30 ),
			'link'      => get_permalink( $post ),
			'thumbnail' => get_the_post_thumbnail_url( $post, 'thumbnail' ) ?: null,
		);
	}
}
