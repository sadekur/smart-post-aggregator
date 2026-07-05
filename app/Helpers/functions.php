<?php

/**
 * Legacy PHP-rendered settings schema. The Settings tab now uses a plain
 * option (`API\Settings`, `DuplicateDetector::OPTION_KEY`) via the React
 * admin app instead of this schema-driven field renderer.
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
