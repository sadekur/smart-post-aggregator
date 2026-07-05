<?php
namespace SmartPostAggregator\Services\Fetchers;

defined( 'ABSPATH' ) || exit;

use SmartPostAggregator\Interfaces\Fetchable;

/**
 * Maps a `spa_sources.type` string to its concrete Fetchable implementation,
 * mirroring how `spa_get_field_factory()` maps a field `type` string to its
 * `Helpers\Field\*` class.
 */
class FetcherFactory {

	/**
	 * @param string $type Source type ('rss'|'api').
	 * @return Fetchable|null
	 */
	public static function make( $type ) {

		switch ( $type ) {
			case 'rss':
				return new RssFetcher();
			case 'api':
				return new ApiFetcher();
		}

		return null;
	}
}
