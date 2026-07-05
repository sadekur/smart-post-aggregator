<?php
namespace SmartPostAggregator\Services;

defined( 'ABSPATH' ) || exit;

use SmartPostAggregator\Config\PostType;
use SmartPostAggregator\Services\Fetchers\FetcherFactory;

/**
 * Pulls a single source's content in: resolves its fetcher, fetches, and
 * creates a `spa_content` post for every item not already ingested (matched
 * on `_spa_external_id`, so re-running the sweep never re-inserts the same
 * item). No near-duplicate detection against other content yet — that's a
 * separate pass once `Services/Similarity/*` exists.
 */
class ContentAggregator {

	/**
	 * @param object $source Row from the `spa_sources` table.
	 * @return int|\WP_Error Number of posts created, or the fetch error.
	 */
	public function aggregate_source( $source ) {

		$fetcher = FetcherFactory::make( $source->type );

		if ( ! $fetcher ) {
			return new \WP_Error(
				'spa_unknown_source_type',
				sprintf( 'Unknown source type "%s".', $source->type )
			);
		}

		$items = $fetcher->fetch( $source );

		if ( is_wp_error( $items ) ) {
			return $items;
		}

		$created = 0;

		foreach ( $items as $item ) {

			if ( empty( $item['external_id'] ) || empty( $item['title'] ) ) {
				continue;
			}

			if ( $this->already_ingested( $item['external_id'] ) ) {
				continue;
			}

			$this->create_post( $source, $item );
			$created++;
		}

		return $created;
	}

	/**
	 * @param string $external_id
	 * @return bool
	 */
	protected function already_ingested( $external_id ) {

		$existing = get_posts(
			array(
				'post_type'      => PostType::POST_TYPE,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => '_spa_external_id',
				'meta_value'     => $external_id,
			)
		);

		return ! empty( $existing );
	}

	/**
	 * @param object $source
	 * @param array  $item
	 */
	protected function create_post( $source, array $item ) {

		// Remote feed/API content is untrusted input — strip to the post-content
		// safelist before it's ever stored or rendered.
		$content = wp_kses_post( $item['content'] ?? '' );

		$post_id = wp_insert_post(
			array(
				'post_type'    => PostType::POST_TYPE,
				'post_title'   => sanitize_text_field( $item['title'] ),
				'post_content' => $content,
				'post_status'  => 'publish',
				'post_date'    => ! empty( $item['published_at'] ) ? $item['published_at'] : current_time( 'mysql' ),
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return;
		}

		update_post_meta( $post_id, '_spa_source_id', (int) $source->id );
		update_post_meta( $post_id, '_spa_external_id', sanitize_text_field( $item['external_id'] ) );
		update_post_meta( $post_id, '_spa_content_hash', hash( 'sha256', wp_strip_all_tags( $content ) ) );
		update_post_meta( $post_id, '_spa_fetched_at', current_time( 'mysql' ) );
	}
}
