<?php
namespace SmartPostAggregator\Services\Similarity;

defined( 'ABSPATH' ) || exit;

use SmartPostAggregator\Interfaces\Similarity;

/**
 * Maps an algorithm string to its concrete `Similarity` implementation,
 * mirroring how `spa_get_field_factory()` maps a field `type` string to its
 * `Helpers\Field\*` class. Adding a new algorithm (e.g. Cosine) later is a
 * one-class, one-line change here — `DuplicateDetector` never hardcodes a
 * concrete implementation.
 */
class SimilarityFactory {

	const DEFAULT_ALGORITHM = 'jaccard';

	/**
	 * @param string $algorithm Algorithm key (currently only 'jaccard').
	 * @return Similarity
	 */
	public static function make( $algorithm ) {

		switch ( $algorithm ) {
			case 'jaccard':
			default:
				return new JaccardSimilarity();
		}
	}
}
