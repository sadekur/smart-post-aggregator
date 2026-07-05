<?php
namespace SmartPostAggregator\Services\Fetchers;

defined( 'ABSPATH' ) || exit;

use SmartPostAggregator\Interfaces\Fetchable;
use SmartPostAggregator\Traits\Cache;

/**
 * Fetches a `spa_sources` row of type `rss` via WP core's SimplePie wrapper.
 */
class RssFetcher implements Fetchable {

	use Cache;

	/**
	 * @param object $source Row from the `spa_sources` table.
	 * @return array[]|\WP_Error
	 */
	public function fetch( $source ) {

		$cache_key = 'spa_feed_' . $source->id;
		$cached    = $this->get_cache( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		require_once ABSPATH . WPINC . '/feed.php';

		// We maintain our own transient cache above (keyed per source, TTL =
		// fetch_interval) — disable SimplePie's own cache so there's one
		// consistent cache story instead of two overlapping layers.
		add_filter( 'wp_feed_cache_transient_lifetime', '__return_zero' );
		$feed = fetch_feed( $source->url );
		remove_filter( 'wp_feed_cache_transient_lifetime', '__return_zero' );

		if ( is_wp_error( $feed ) ) {
			return $feed;
		}

		$items = array();

		foreach ( $feed->get_items() as $entry ) {
			$items[] = array(
				'external_id'  => $entry->get_id() ?: $entry->get_permalink(),
				'title'        => $entry->get_title(),
				'content'      => $entry->get_content() ?: $entry->get_description(),
				'link'         => $entry->get_permalink(),
				'published_at' => $entry->get_date( 'Y-m-d H:i:s' ) ?: null,
			);
		}

		$this->set_cache( $cache_key, $items, (int) $source->fetch_interval );

		return $items;
	}
}
