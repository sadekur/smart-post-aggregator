<?php
namespace SmartPostAggregator\Services;

defined( 'ABSPATH' ) || exit;

use SmartPostAggregator\Config\PostType;
use SmartPostAggregator\Models\DuplicateLog;
use SmartPostAggregator\Services\Similarity\SimilarityFactory;

/**
 * Runs on every newly-aggregated post: a cheap exact-hash pre-filter first
 * (catches re-fetches of the exact same item), then — only if that misses —
 * a similarity pass against a bounded, recent candidate window (never the
 * whole corpus, to avoid O(n²) blowup as content accumulates). Every
 * non-unique verdict is written to `spa_duplicate_log` for the Review/Logs
 * screens; the boring "nothing similar" case isn't logged.
 */
class DuplicateDetector {

	/** Recent-post window the candidate set is bounded to. */
	const CANDIDATE_WINDOW_DAYS = 30;

	/** Max candidates compared per post, to bound worst-case cost per ingest. */
	const CANDIDATE_LIMIT = 200;

	const DEFAULT_THRESHOLD      = 50; // percent
	const DEFAULT_REVIEW_MARGIN  = 10; // percent

	/**
	 * @param int $post_id A `spa_content` post that was just created.
	 */
	public function detect( $post_id ) {

		$post = get_post( $post_id );

		if ( ! $post || PostType::POST_TYPE !== $post->post_type ) {
			return;
		}

		$hash        = get_post_meta( $post_id, '_spa_content_hash', true );
		$exact_match = $hash ? $this->find_exact_hash_match( $post_id, $hash ) : 0;

		if ( $exact_match ) {
			$this->record( $post_id, $exact_match, 100.0, 100.0, 'hash', 'duplicate' );
			return;
		}

		$candidates = $this->get_candidates( $post_id );

		if ( empty( $candidates ) ) {
			$this->record( $post_id, 0, 0.0, 0.0, 'none', 'unique' );
			return;
		}

		$algorithm  = apply_filters( 'spa_duplicate_algorithm', SimilarityFactory::DEFAULT_ALGORITHM );
		$similarity = SimilarityFactory::make( $algorithm );
		$text       = $this->comparable_text( $post );

		$best_score = 0.0;
		$best_match = 0;

		foreach ( $candidates as $candidate ) {
			$score = $similarity->score( $text, $this->comparable_text( $candidate ) );

			if ( $score > $best_score ) {
				$best_score = $score;
				$best_match = $candidate->ID;
			}
		}

		$threshold     = (float) apply_filters( 'spa_duplicate_threshold', self::DEFAULT_THRESHOLD );
		$review_margin = (float) apply_filters( 'spa_duplicate_review_margin', self::DEFAULT_REVIEW_MARGIN );
		$score_percent = $best_score * 100;

		if ( $score_percent < $threshold ) {
			$status = 'unique';
		} elseif ( $score_percent < ( $threshold + $review_margin ) ) {
			$status = 'pending_review';
		} else {
			$status = 'duplicate';
		}

		$this->record( $post_id, 'unique' === $status ? 0 : $best_match, $score_percent, $threshold, $algorithm, $status );
	}

	/**
	 * @param int    $post_id
	 * @param string $hash
	 * @return int Matched post ID, or 0.
	 */
	protected function find_exact_hash_match( $post_id, $hash ) {

		$matches = get_posts(
			array(
				'post_type'      => PostType::POST_TYPE,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'post__not_in'   => array( $post_id ),
				'fields'         => 'ids',
				'meta_key'       => '_spa_content_hash',
				'meta_value'     => $hash,
			)
		);

		return ! empty( $matches ) ? (int) $matches[0] : 0;
	}

	/**
	 * Bounded candidate set: recent published posts only, never the whole corpus.
	 *
	 * @param int $post_id
	 * @return \WP_Post[]
	 */
	protected function get_candidates( $post_id ) {

		return get_posts(
			array(
				'post_type'      => PostType::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => self::CANDIDATE_LIMIT,
				'post__not_in'   => array( $post_id ),
				'date_query'     => array(
					array( 'after' => self::CANDIDATE_WINDOW_DAYS . ' days ago' ),
				),
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);
	}

	/**
	 * @param \WP_Post $post
	 * @return string
	 */
	protected function comparable_text( $post ) {
		return $post->post_title . ' ' . wp_strip_all_tags( $post->post_content );
	}

	/**
	 * @param int    $post_id
	 * @param int    $matched_id
	 * @param float  $score_percent
	 * @param float  $threshold
	 * @param string $algorithm
	 * @param string $status
	 */
	protected function record( $post_id, $matched_id, $score_percent, $threshold, $algorithm, $status ) {

		update_post_meta( $post_id, '_spa_duplicate_status', $status );
		update_post_meta( $post_id, '_spa_similarity_score', round( $score_percent, 2 ) );

		if ( $matched_id ) {
			update_post_meta( $post_id, '_spa_duplicate_of', $matched_id );
		}

		if ( 'unique' === $status ) {
			return;
		}

		( new DuplicateLog() )->insert_row(
			array(
				'new_post_id'       => $post_id,
				'matched_post_id'   => $matched_id ?: null,
				'algorithm'         => $algorithm,
				'score'             => round( $score_percent, 2 ),
				'threshold_at_time' => $threshold,
				// "Mark" over "Ignore" as the safe default: ignoring is destructive
				// and hard to reverse, so borderline/high-confidence matches are
				// kept (flagged) rather than silently dropped.
				'resolution'        => 'duplicate' === $status ? 'marked' : 'queued',
			)
		);
	}
}
