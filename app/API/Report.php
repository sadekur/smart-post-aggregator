<?php
namespace SmartPostAggregator\API;

defined( 'ABSPATH' ) || exit;

use SmartPostAggregator\Traits\Rest;
use SmartPostAggregator\Config\PostType;
use SmartPostAggregator\Models\DuplicateLog;
use SmartPostAggregator\Models\Source;

/**
 * Backs the Dashboard stat tiles + recent-activity feed, and the full Logs
 * audit trail (every duplicate-detection event, resolved or not).
 */
class Report {

	use Rest;

	/**
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function stats( $request ) {

		global $wpdb;

		$post_type = PostType::POST_TYPE;
		$log_table = ( new DuplicateLog() )->get_table();
		$src_table = ( new Source() )->get_table();

		$today = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' AND DATE(post_date) = CURDATE()",
				$post_type
			)
		);

		$week = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' AND post_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
				$post_type
			)
		);

		$total = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish'",
				$post_type
			)
		);

		// "Auto-resolved" = the system already reached a verdict at ingest time
		// (hash match, or a high-confidence Jaccard match past the review
		// margin) with no human reviewer involved yet.
		$auto_resolved    = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$log_table} WHERE resolution != 'queued' AND resolved_by IS NULL" );
		$pending_review   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$log_table} WHERE resolution = 'queued'" );
		$sources_in_error = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$src_table} WHERE status = 'error'" );

		$recent = $wpdb->get_results( "SELECT * FROM {$log_table} ORDER BY created_at DESC LIMIT 10" );

		return rest_ensure_response(
			array(
				'aggregated_today' => $today,
				'aggregated_week'  => $week,
				'aggregated_total' => $total,
				'auto_resolved'    => $auto_resolved,
				'pending_review'   => $pending_review,
				'sources_in_error' => $sources_in_error,
				'recent_activity'  => array_map( array( $this, 'format_activity' ), $recent ),
			)
		);
	}

	/**
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function logs( $request ) {

		global $wpdb;

		$log_table = ( new DuplicateLog() )->get_table();

		$page     = max( 1, (int) $request->get_param( 'page' ) );
		$per_page = (int) $request->get_param( 'per_page' );
		$per_page = $per_page > 0 ? min( 100, $per_page ) : 20;

		$where  = array( '1=1' );
		$values = array();

		$resolution = (string) $request->get_param( 'resolution' );
		if ( '' !== $resolution ) {
			$where[]  = 'resolution = %s';
			$values[] = $resolution;
		}

		$date_from = (string) $request->get_param( 'date_from' );
		if ( '' !== $date_from ) {
			$where[]  = 'created_at >= %s';
			$values[] = $date_from . ' 00:00:00';
		}

		$date_to = (string) $request->get_param( 'date_to' );
		if ( '' !== $date_to ) {
			$where[]  = 'created_at <= %s';
			$values[] = $date_to . ' 23:59:59';
		}

		$source_id = (int) $request->get_param( 'source_id' );
		if ( $source_id > 0 ) {
			$where[]  = "new_post_id IN ( SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_spa_source_id' AND meta_value = %d )";
			$values[] = $source_id;
		}

		$where_sql = implode( ' AND ', $where );

		$total = (int) ( $values
			? $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$log_table} WHERE {$where_sql}", ...$values ) )
			: $wpdb->get_var( "SELECT COUNT(*) FROM {$log_table} WHERE {$where_sql}" )
		);

		$offset = ( $page - 1 ) * $per_page;
		$query  = "SELECT * FROM {$log_table} WHERE {$where_sql} ORDER BY created_at DESC LIMIT %d OFFSET %d";
		$rows   = $wpdb->get_results( $wpdb->prepare( $query, ...array_merge( $values, array( $per_page, $offset ) ) ) );

		$response = rest_ensure_response( array_map( array( $this, 'format_log_row' ), $rows ) );
		$response->header( 'X-WP-Total', $total );
		$response->header( 'X-WP-TotalPages', (int) ceil( $total / $per_page ) );

		return $response;
	}

	/**
	 * @param object $row `spa_duplicate_log` row.
	 * @return array
	 */
	protected function format_activity( $row ) {
		return array(
			'log_id'        => (int) $row->id,
			'new_title'     => get_the_title( $row->new_post_id ),
			'matched_title' => $row->matched_post_id ? get_the_title( $row->matched_post_id ) : null,
			'score'         => (float) $row->score,
			'resolution'    => $row->resolution,
			'created_at'    => $row->created_at,
		);
	}

	/**
	 * @param object $row `spa_duplicate_log` row.
	 * @return array
	 */
	protected function format_log_row( $row ) {

		$resolved_by = null;

		if ( ! empty( $row->resolved_by ) ) {
			$user        = get_userdata( $row->resolved_by );
			$resolved_by = $user ? $user->display_name : null;
		}

		return array(
			'log_id'            => (int) $row->id,
			'new_post'          => array(
				'id'    => (int) $row->new_post_id,
				'title' => get_the_title( $row->new_post_id ),
				'link'  => get_permalink( $row->new_post_id ),
			),
			'matched_post'      => $row->matched_post_id ? array(
				'id'    => (int) $row->matched_post_id,
				'title' => get_the_title( $row->matched_post_id ),
				'link'  => get_permalink( $row->matched_post_id ),
			) : null,
			'algorithm'         => $row->algorithm,
			'score'             => (float) $row->score,
			'threshold_at_time' => (float) $row->threshold_at_time,
			'resolution'        => $row->resolution,
			'resolved_by'       => $resolved_by,
			'created_at'        => $row->created_at,
		);
	}
}
