<?php
namespace SmartPostAggregator\Controllers\Common;

defined( 'ABSPATH' ) || exit;

use SmartPostAggregator\Traits\Hook;
use SmartPostAggregator\Traits\Queue;
use SmartPostAggregator\Traits\Cache;
use SmartPostAggregator\Models\Source;
use SmartPostAggregator\Services\ContentAggregator;

/**
 * Runs the recurring sweep that pulls content in from every active source in
 * `spa_sources` and hands each one to `ContentAggregator`. One shared sweep
 * hook (rather than one cron event per source) keeps WP-Cron's "only fires on
 * page load" model easy to reason about, and a single `foreach` means one
 * dead feed never aborts the whole run.
 */
class Cron {

	use Hook;
	use Queue;

	const SWEEP_HOOK = 'spa_cron_fetch_sources';
	const INTERVAL    = 'spa_fifteen_minutes';

	/** Max sources processed per tick, to stay under the host's max_execution_time. */
	const BATCH_SIZE = 20;

	/** Retries before a source is flagged `error` and stops being retried. */
	const MAX_RETRIES = 5;

	public function __construct() {
		$this->filter( 'cron_schedules', array( self::class, 'register_interval' ) );
		$this->action( self::SWEEP_HOOK, array( $this, 'sweep' ) );

		$this->schedule_recurring( time(), self::INTERVAL, self::SWEEP_HOOK );
	}

	/**
	 * Registers the custom 15-minute cron interval.
	 *
	 * @param array $schedules
	 * @return array
	 */
	public static function register_interval( $schedules ) {

		$schedules[ self::INTERVAL ] = array(
			'interval' => 15 * MINUTE_IN_SECONDS,
			'display'  => __( 'Every 15 Minutes', 'smart-post-aggregator' ),
		);

		return $schedules;
	}

	/**
	 * The sweep itself: fetch every due, active source and aggregate its content.
	 */
	public function sweep() {

		$source_model = new Source();
		$sources      = $source_model->get_rows( array( array( 'status' => 'active' ) ), self::BATCH_SIZE * 3 );

		$processed = 0;

		foreach ( $sources as $source ) {

			if ( $processed >= self::BATCH_SIZE ) {
				break;
			}

			if ( ! $this->is_due( $source ) ) {
				continue;
			}

			$this->fetch_source( $source_model, $source );
			$processed++;
		}
	}

	/**
	 * Whether a source is due: past its backoff window (if any), and past its
	 * configured fetch interval since the last successful fetch.
	 *
	 * @param object $source
	 * @return bool
	 */
	protected function is_due( $source ) {

		if ( ! empty( $source->next_retry_at ) && strtotime( $source->next_retry_at ) > time() ) {
			return false;
		}

		if ( empty( $source->last_fetched_at ) ) {
			return true;
		}

		return ( strtotime( $source->last_fetched_at ) + (int) $source->fetch_interval ) <= time();
	}

	/**
	 * Fetches and aggregates a single source, recording success/failure on its row.
	 *
	 * @param Source $source_model
	 * @param object $source
	 */
	protected function fetch_source( Source $source_model, $source ) {

		$result = ( new ContentAggregator() )->aggregate_source( $source );

		if ( is_wp_error( $result ) ) {
			$this->handle_failure( $source_model, $source );
			return;
		}

		$source_model->update_row(
			$source->id,
			array(
				'last_fetched_at' => current_time( 'mysql' ),
				'retry_count'     => 0,
				'next_retry_at'   => null,
				'status'          => 'active',
			)
		);
	}

	/**
	 * Exponential backoff on failure; flips to `error` (and stops retrying)
	 * once MAX_RETRIES is exceeded, surfaced later on the Sources/Dashboard page.
	 *
	 * @param Source $source_model
	 * @param object $source
	 */
	protected function handle_failure( Source $source_model, $source ) {

		$retry_count = (int) $source->retry_count + 1;

		if ( $retry_count >= self::MAX_RETRIES ) {
			$source_model->update_row(
				$source->id,
				array(
					'status'      => 'error',
					'retry_count' => $retry_count,
				)
			);
			return;
		}

		$backoff = MINUTE_IN_SECONDS * pow( 2, $retry_count );

		$source_model->update_row(
			$source->id,
			array(
				'retry_count'   => $retry_count,
				'next_retry_at' => gmdate( 'Y-m-d H:i:s', time() + $backoff ),
			)
		);
	}
}
