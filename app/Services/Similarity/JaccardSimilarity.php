<?php
namespace SmartPostAggregator\Services\Similarity;

defined( 'ABSPATH' ) || exit;

use SmartPostAggregator\Interfaces\Similarity;
use SmartPostAggregator\Helpers\TextNormalizer;

/**
 * Jaccard similarity over word sets: |A ∩ B| / |A ∪ B|. Chosen over TF-IDF +
 * cosine for near-duplicate detection specifically — it needs only the two
 * documents being compared (no corpus-wide stats to maintain as the CPT
 * grows), and its 0–1 overlap ratio maps directly onto a "similarity
 * threshold %" a non-technical admin can reason about.
 */
class JaccardSimilarity implements Similarity {

	/**
	 * @param string $a First text to compare.
	 * @param string $b Second text to compare.
	 * @return float 0–1 similarity score.
	 */
	public function score( $a, $b ) {

		$set_a = array_unique( TextNormalizer::tokenize( $a ) );
		$set_b = array_unique( TextNormalizer::tokenize( $b ) );

		if ( empty( $set_a ) || empty( $set_b ) ) {
			return 0.0;
		}

		$intersection = count( array_intersect( $set_a, $set_b ) );
		$union        = count( array_unique( array_merge( $set_a, $set_b ) ) );

		return $union > 0 ? $intersection / $union : 0.0;
	}
}
