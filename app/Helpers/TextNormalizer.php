<?php
namespace SmartPostAggregator\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * Shared text-normalization + tokenization used by every `Similarity`
 * implementation, so each algorithm compares apples-to-apples input instead
 * of reimplementing its own cleanup.
 */
class TextNormalizer {

	/**
	 * Strips tags/entities, lowercases, and collapses whitespace/punctuation.
	 *
	 * @param string $text
	 * @return string
	 */
	public static function normalize( $text ) {

		$text = wp_strip_all_tags( (string) $text );
		$text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
		$text = strtolower( $text );
		$text = preg_replace( '/[^\p{L}\p{N}\s]/u', ' ', $text );
		$text = preg_replace( '/\s+/', ' ', $text );

		return trim( $text );
	}

	/**
	 * Normalizes and splits text into a word-token array.
	 *
	 * @param string $text
	 * @return string[]
	 */
	public static function tokenize( $text ) {

		$normalized = self::normalize( $text );

		return '' === $normalized ? array() : explode( ' ', $normalized );
	}
}
