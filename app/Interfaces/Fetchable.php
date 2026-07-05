<?php
namespace SmartPostAggregator\Interfaces;

defined( 'ABSPATH' ) || exit;

/**
 * Contract for pulling raw content in from a `spa_sources` row and normalizing
 * it into a common shape, regardless of whether the source is RSS or an
 * arbitrary JSON API.
 */
interface Fetchable {

	/**
	 * @param object $source Row from the `spa_sources` table.
	 * @return array[]|\WP_Error Array of normalized items — each with keys
	 *                           `external_id`, `title`, `content`, `link`,
	 *                           `published_at` — or a WP_Error on failure.
	 */
	public function fetch( $source );
}
