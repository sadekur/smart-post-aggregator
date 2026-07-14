<?php
namespace SmartPostAggregator\Services;

defined( 'ABSPATH' ) || exit;

use SmartPostAggregator\Models\DuplicateLog;
use SmartPostAggregator\Config\PostType;

/**
 * Applies a reviewer's decision to a pending `spa_duplicate_log` entry from
 * the Review inbox, then records who resolved it and how.
 */
class DuplicateResolver {

	/**
	 * @param int    $log_id      `spa_duplicate_log` row id.
	 * @param string $action      approve_unique|confirm_duplicate|merge|ignore.
	 * @param int    $resolved_by Current user ID.
	 * @return string|\WP_Error Resulting resolution value, or error.
	 */
	public function resolve( $log_id, $action, $resolved_by = 0 ) {

		$log_model = new DuplicateLog();
		$log       = $log_model->get_by_id( $log_id );

		if ( ! $log ) {
			return new \WP_Error( 'spa_log_not_found', __( 'Duplicate log entry not found.', 'smart-post-aggregator' ), array( 'status' => 404 ) );
		}

		switch ( $action ) {
			case 'approve_unique':
				$resolution = $this->approve_unique( $log );
				break;
			case 'confirm_duplicate':
				$resolution = $this->confirm_duplicate( $log );
				break;
			case 'merge':
				$resolution = $this->merge( $log );
				break;
			case 'ignore':
				$resolution = $this->ignore( $log );
				break;
			default:
				return new \WP_Error( 'spa_invalid_action', __( 'Invalid resolution action.', 'smart-post-aggregator' ), array( 'status' => 400 ) );
		}

		$log_model->update_row(
			$log_id,
			array(
				'resolution'  => $resolution,
				'resolved_by' => $resolved_by,
			)
		);

		return $resolution;
	}

	/**
	 * Reviewer says this is NOT a duplicate after all.
	 *
	 * @param object $log
	 * @return string
	 */
	protected function approve_unique( $log ) {
		update_post_meta( $log->new_post_id, '_spa_duplicate_status', 'unique' );
		delete_post_meta( $log->new_post_id, '_spa_duplicate_of' );

		return 'approved';
	}

	/**
	 * Reviewer confirms it's a duplicate; kept (for audit), just flagged.
	 *
	 * @param object $log
	 * @return string
	 */
	protected function confirm_duplicate( $log ) {
		update_post_meta( $log->new_post_id, '_spa_duplicate_status', 'duplicate' );

		return 'marked';
	}

	/**
	 * Folds the new post into the matched original: backfills a missing
	 * thumbnail on the original from the new post, then trashes the new post
	 * (recoverable, not permanently deleted).
	 *
	 * @param object $log
	 * @return string
	 */
	protected function merge( $log ) {

		if ( $log->matched_post_id && ! get_post_thumbnail_id( $log->matched_post_id ) ) {
			$new_thumbnail = get_post_thumbnail_id( $log->new_post_id );

			if ( $new_thumbnail ) {
				set_post_thumbnail( $log->matched_post_id, $new_thumbnail );
			} elseif ( ! get_post_meta( $log->matched_post_id, PostType::THUMBNAIL_META_KEY, true ) ) {
				$new_thumbnail_url = get_post_meta( $log->new_post_id, PostType::THUMBNAIL_META_KEY, true );

				if ( $new_thumbnail_url ) {
					update_post_meta( $log->matched_post_id, PostType::THUMBNAIL_META_KEY, $new_thumbnail_url );
				}
			}
		}

		update_post_meta( $log->new_post_id, '_spa_duplicate_status', 'duplicate' );
		wp_trash_post( $log->new_post_id );

		return 'merged';
	}

	/**
	 * Reviewer doesn't want this tracked either way; discard the new post.
	 *
	 * @param object $log
	 * @return string
	 */
	protected function ignore( $log ) {
		wp_trash_post( $log->new_post_id );

		return 'ignored';
	}
}
