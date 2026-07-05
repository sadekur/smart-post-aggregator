<?php
namespace SmartPostAggregator\Controllers\Front;

defined( 'ABSPATH' ) || exit;

use WP_Query;
use SmartPostAggregator\Traits\Hook;
use SmartPostAggregator\Helpers\Utility;
use SmartPostAggregator\Config\PostType;

/**
 * Registers `[spa_posts]`, the public-facing listing of aggregated content.
 * Only posts confirmed `unique` are shown — anything flagged `duplicate` or
 * still `pending_review` is excluded until a reviewer resolves it, so the
 * public never sees an unresolved near-duplicate.
 */
class Shortcode {

	use Hook;

	public function __construct() {
		$this->shortcode( 'spa_posts', array( $this, 'render' ) );
	}

	/**
	 * @param array $atts
	 * @return string
	 */
	public function render( $atts ) {

		$atts = shortcode_atts(
			array(
				'posts_per_page' => 10,
				'source_id'      => 0,
			),
			$atts,
			'spa_posts'
		);

		$meta_query = array(
			'relation' => 'AND',
			array(
				'key'     => '_spa_duplicate_status',
				'value'   => array( 'duplicate', 'pending_review' ),
				'compare' => 'NOT IN',
			),
		);

		if ( ! empty( $atts['source_id'] ) ) {
			$meta_query[] = array(
				'key'   => '_spa_source_id',
				'value' => (int) $atts['source_id'],
			);
		}

		$query = new WP_Query(
			array(
				'post_type'      => PostType::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => max( 1, (int) $atts['posts_per_page'] ),
				'meta_query'     => $meta_query,
			)
		);

		return Utility::get_template( 'shortcodes/posts', array( 'query' => $query ) );
	}
}
