<?php
namespace SmartPostAggregator\Interfaces;

defined( 'ABSPATH' ) || exit;

/**
 * Contract for a near-duplicate similarity algorithm. Implementations take
 * two plain-text strings (already normalized by the caller) and return a
 * 0–1 overlap ratio — 0 meaning no overlap, 1 meaning identical.
 */
interface Similarity {

	/**
	 * @param string $a First text to compare.
	 * @param string $b Second text to compare.
	 * @return float 0–1 similarity score.
	 */
	public function score( $a, $b );
}
