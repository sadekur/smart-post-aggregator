<?php
namespace SmartPostAggregator\Services\Fetchers;

defined( 'ABSPATH' ) || exit;

use SmartPostAggregator\Interfaces\Fetchable;
use SmartPostAggregator\Traits\Request;
use SmartPostAggregator\Traits\Cache;

/**
 * Fetches a `spa_sources` row of type `api` and normalizes an arbitrary JSON
 * response shape using the field-mapping stored in the source's `config`
 * column (JSON: `items_path`, `id_field`, `title_field`, `content_field`,
 * `link_field`, `published_at_field`, `image_field`).
 */
class ApiFetcher implements Fetchable {

	use Request;
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

		// Cron-triggered fetches must never stall the whole sweep on one slow
		// host — much shorter than the trait's 300s default.
		$response = $this->get( $source->url, array(), array(), array( 'timeout' => 20 ) );

		if ( ! $response['success'] ) {
			return new \WP_Error(
				'spa_fetch_failed',
				$response['error'] ? $response['error'] : __( 'Unknown fetch error.', 'smart-post-aggregator' )
			);
		}

		$config = json_decode( (string) $source->config, true );
		$config = is_array( $config ) ? $config : array();

		$rows  = $this->extract_rows( $response['content'], $config['items_path'] ?? '' );
		$items = array();

		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$items[] = array(
				'external_id'  => (string) $this->field( $row, $config['id_field'] ?? 'id' ),
				'title'        => (string) $this->field( $row, $config['title_field'] ?? 'title' ),
				'content'      => (string) $this->field( $row, $config['content_field'] ?? 'content' ),
				'link'         => (string) $this->field( $row, $config['link_field'] ?? 'link' ),
				'published_at' => $this->field( $row, $config['published_at_field'] ?? 'published_at' ) ?: null,
				'image'        => (string) $this->field( $row, $config['image_field'] ?? 'image' ) ?: null,
			);
		}

		$this->set_cache( $cache_key, $items, (int) $source->fetch_interval );

		return $items;
	}

	/**
	 * Walks a dot-notation path (e.g. `data.results`) into the decoded JSON
	 * response to find the list of items to normalize.
	 *
	 * @param mixed  $content Decoded JSON response body.
	 * @param string $path    Dot-notation path, or '' if the response body is itself the list.
	 * @return array
	 */
	protected function extract_rows( $content, $path ) {

		if ( '' === $path ) {
			return is_array( $content ) ? $content : array();
		}

		$data = $content;

		foreach ( explode( '.', $path ) as $segment ) {
			if ( ! is_array( $data ) || ! isset( $data[ $segment ] ) ) {
				return array();
			}
			$data = $data[ $segment ];
		}

		return is_array( $data ) ? $data : array();
	}

	/**
	 * @param array  $row
	 * @param string $key
	 * @return mixed
	 */
	protected function field( array $row, $key ) {
		return $row[ $key ] ?? '';
	}
}
