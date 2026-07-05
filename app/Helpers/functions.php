<?php

/**
 * Returns the home URL of the WordPress site.
 *
 * @param string $path    Optional. Path relative to the home URL.
 * @param int    $blog_id Optional. ID of the blog in a multisite installation.
 *
 * @return string Home URL with optional path appended.
 */
function spa_home_url( $path = '', $blog_id = null ) {
	return get_home_url( $blog_id, $path );
}

/**
 * Settings schema consumed by views/settings/layout.php.
 *
 * @todo Populate real menus/sections/fields (feed sources, duplicate-detection
 *       threshold, etc.) as each feature is designed and built.
 */
function spa_settings_menus() {

	return apply_filters(
		'spa_settings_menus',
		array(
			'general' => array(
				'label'    => __( 'General', 'smart-post-aggregator' ),
				'desc'     => __( 'General settings', 'smart-post-aggregator' ),
				'icon'     => '',
				'submenus' => array(
					'general' => array(
						'label'    => __( 'General', 'smart-post-aggregator' ),
						'desc'     => __( 'General Settings', 'smart-post-aggregator' ),
						'sections' => array(
							'general' => array(
								'label'  => __( 'General', 'smart-post-aggregator' ),
								'desc'   => __( 'Settings will be added here as each feature is built.', 'smart-post-aggregator' ),
								'fields' => array(),
							),
						),
					),
				),
			),
		)
	);
}

function spa_get_field_factory( $type ) {

	if ( $type == 'switch' ) {
		$type = 'switcher';
	} elseif ( $type == 'wysiwyg' ) {
		$type = 'WYSIWYG';
	}

	return '\\SmartPostAggregator\\Helpers\\Field\\' . ucfirst( $type );
}
