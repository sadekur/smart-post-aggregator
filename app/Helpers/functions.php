<?php
use SmartPostAggregator\Helpers\Utility;

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

function spa_settings_menus() {

	$pages = Utility::get_posts( array( 'post_type' => 'page' ) );

	return apply_filters(
		'spa_settings_menus',
		array(
			'general' => array(
				'label'    => __( 'General', 'smart-post-aggregator' ),
				'desc'     => __( 'General settings', 'smart-post-aggregator' ),
				'icon'     => '',
				'submenus' => array(
					'pages' => array(
						'label'    => __( 'Pages', 'smart-post-aggregator' ),
						'desc'     => __( 'Page Settings', 'smart-post-aggregator' ),
						'sections' => array(
							'main_pages' => array(
								'label'  => __( 'Main Pages', 'smart-post-aggregator' ),
								'desc'   => __( 'Main Pages Settings', 'smart-post-aggregator' ),
								'fields' => array(
									array(
										'id'      => 'homepage',
										'type'    => 'select',
										'label'   => __( 'Homepage', 'smart-post-aggregator' ),
										'options' => $pages,
									),
									array(
										'id'      => 'landing_page',
										'type'    => 'select',
										'label'   => __( 'Landing Page', 'smart-post-aggregator' ),
										'options' => $pages,
									),
								),
							),
						),
					),
				),
			),
			'email'   => array(
				'label'    => __( 'Email', 'smart-post-aggregator' ),
				'desc'     => __( 'Email settings', 'smart-post-aggregator' ),
				'icon'     => '',
				'submenus' => array(
					'new_ticket'    => array(
						'label'    => __( 'New Ticket', 'smart-post-aggregator' ),
						'desc'     => __( 'New Ticket Notification', 'smart-post-aggregator' ),
						'sections' => array(
							'agent_email'  => array(
								'label'  => __( 'Agent Email', 'smart-post-aggregator' ),
								'desc'   => __( 'Email to an Agent', 'smart-post-aggregator' ),
								'fields' => array(
									array(
										'id'    => 'agent_header',
										'type'  => 'text',
										'label' => __( 'Header', 'smart-post-aggregator' ),
									),
									array(
										'id'    => 'agent_subject',
										'type'  => 'text',
										'label' => __( 'Subject', 'smart-post-aggregator' ),
									),
									array(
										'id'    => 'agent_body',
										'type'  => 'wysiwyg',
										'label' => __( 'Body', 'smart-post-aggregator' ),
									),
								),
							),
							'client_email' => array(
								'label'  => __( 'Client Email', 'smart-post-aggregator' ),
								'desc'   => __( 'Email to a Client', 'smart-post-aggregator' ),
								'fields' => array(
									array(
										'id'    => 'client_header',
										'type'  => 'text',
										'label' => __( 'Header', 'smart-post-aggregator' ),
									),
									array(
										'id'    => 'client_subject',
										'type'  => 'text',
										'label' => __( 'Subject', 'smart-post-aggregator' ),
									),
									array(
										'id'    => 'client_body',
										'type'  => 'wysiwyg',
										'label' => __( 'Body', 'smart-post-aggregator' ),
									),
								),
							),
						),
					),
					'agent_replied' => array(
						'label'    => __( 'Agent Reply', 'smart-post-aggregator' ),
						'desc'     => __( 'Agent Reply Notification', 'smart-post-aggregator' ),
						'sections' => array(
							'agent_email_reply' => array(
								'label'  => __( 'Agent Reply Email', 'smart-post-aggregator' ),
								'desc'   => __( 'Email to a Client', 'smart-post-aggregator' ),
								'fields' => array(
									array(
										'id'    => 'client_header',
										'type'  => 'text',
										'label' => __( 'Header', 'smart-post-aggregator' ),
									),
									array(
										'id'    => 'client_subject',
										'type'  => 'text',
										'label' => __( 'Subject', 'smart-post-aggregator' ),
									),
									array(
										'id'    => 'client_body',
										'type'  => 'wysiwyg',
										'label' => __( 'Body', 'smart-post-aggregator' ),
									),
								),
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
