<?php
namespace SmartPostAggregator\Config;

defined( 'ABSPATH' ) || exit;

use SmartPostAggregator\Traits\Hook;

/**
 * Registers the `spa_content` post type used to store aggregated content, and
 * the post meta fields the aggregation / duplicate-detection pipeline relies on.
 */
class PostType {

	use Hook;

	const POST_TYPE = 'spa_content';

	/**
	 * Feed/API items are hotlinked for their thumbnail rather than sideloaded
	 * into the media library (avoids adding a remote-file-download step, with
	 * its own SSRF/storage surface, to the cron sweep) — so this stores the
	 * source's own image URL directly instead of a native attachment ID.
	 */
	const THUMBNAIL_META_KEY = '_spa_thumbnail_url';

	public function __construct() {
		$this->action( 'init', array( $this, 'register' ) );
	}

	public function register() {

		register_post_type(
			self::POST_TYPE,
			array(
				'labels'       => array(
					'name'          => __( 'Aggregated Content', 'smart-post-aggregator' ),
					'singular_name' => __( 'Aggregated Content', 'smart-post-aggregator' ),
					'menu_name'     => __( 'Aggregated Content', 'smart-post-aggregator' ),
					'add_new_item'  => __( 'Add New Aggregated Content', 'smart-post-aggregator' ),
					'edit_item'     => __( 'Edit Aggregated Content', 'smart-post-aggregator' ),
					'view_item'     => __( 'View Aggregated Content', 'smart-post-aggregator' ),
					'search_items'  => __( 'Search Aggregated Content', 'smart-post-aggregator' ),
					'not_found'     => __( 'No aggregated content found', 'smart-post-aggregator' ),
				),
				'public'       => true,
				// No native wp-admin screens for this CPT — every admin screen is the
				// plugin's own React app. REST stays on so that app can read/write it.
				'show_ui'      => false,
				'show_in_menu' => false,
				'show_in_rest' => true,
				'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields' ),
				'has_archive'  => true,
				'rewrite'      => array( 'slug' => 'aggregated' ),
			)
		);

		$this->register_meta();
	}

	/**
	 * Registers post meta used by the aggregation / duplicate-detection pipeline.
	 *
	 * All of it is system-managed (written by the aggregator, not hand-edited by
	 * post authors), so REST edits are restricted to admins via auth_callback.
	 */
	protected function register_meta() {

		$common_args = array(
			'single'        => true,
			'show_in_rest'  => true,
			'auth_callback' => function () {
				return current_user_can( 'manage_options' );
			},
		);

		register_post_meta(
			self::POST_TYPE,
			'_spa_source_id',
			$common_args + array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			)
		);

		register_post_meta(
			self::POST_TYPE,
			'_spa_external_id',
			$common_args + array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		register_post_meta(
			self::POST_TYPE,
			'_spa_content_hash',
			$common_args + array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		register_post_meta(
			self::POST_TYPE,
			'_spa_duplicate_status',
			$common_args + array(
				'type'              => 'string',
				'default'           => 'unique',
				'sanitize_callback' => array( $this, 'sanitize_duplicate_status' ),
			)
		);

		register_post_meta(
			self::POST_TYPE,
			'_spa_duplicate_of',
			$common_args + array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
			)
		);

		register_post_meta(
			self::POST_TYPE,
			'_spa_similarity_score',
			$common_args + array(
				'type'              => 'number',
				// `floatval` is a PHP internal function; WP's sanitize_meta()
				// calls the callback with 4 args (value, key, object_type,
				// object_subtype), and since PHP 8 internal functions throw
				// ArgumentCountError when called with more args than declared.
				// Wrap it so only the value is forwarded.
				'sanitize_callback' => array( $this, 'sanitize_similarity_score' ),
			)
		);

		register_post_meta(
			self::POST_TYPE,
			'_spa_fetched_at',
			$common_args + array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		register_post_meta(
			self::POST_TYPE,
			self::THUMBNAIL_META_KEY,
			$common_args + array(
				'type'              => 'string',
				'sanitize_callback' => 'esc_url_raw',
			)
		);
	}

	/**
	 * Whitelists the allowed `_spa_duplicate_status` values.
	 *
	 * @param mixed $value Raw value being saved.
	 * @return string
	 */
	public function sanitize_duplicate_status( $value ) {
		$allowed = array( 'unique', 'pending_review', 'duplicate' );
		return in_array( $value, $allowed, true ) ? $value : 'unique';
	}

	/**
	 * @param mixed $value Raw value being saved.
	 * @return float
	 */
	public function sanitize_similarity_score( $value ) {
		return floatval( $value );
	}
}

new PostType();
