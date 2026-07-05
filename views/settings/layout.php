<?php
use SmartPostAggregator\Helpers\Utility;

$menus = spa_settings_menus();

$active_menu_id = isset( $_GET['menu'] ) && array_key_exists( $_GET['menu'], $menus ) ? sanitize_key( $_GET['menu'] ) : array_key_first( $menus );
$active_menu    = $menus[ $active_menu_id ];

$submenus          = isset( $active_menu['submenus'] ) && is_array( $active_menu['submenus'] ) ? $active_menu['submenus'] : array();
$active_submenu_id = isset( $_GET['submenu'] ) && array_key_exists( $_GET['submenu'], $submenus ) ? sanitize_key( $_GET['submenu'] ) : array_key_first( $submenus );

$admin_menu   = admin_url( 'admin.php' );
$option_key   = "smart-post-aggregator-{$active_menu_id}-{$active_submenu_id}";
$saved_option = get_option( $option_key );

?>
<div id="smart-post-aggregator-settings-wrap">
	
	<div id="smart-post-aggregator-settings-header">
		<h2><?php esc_html_e( 'Smart Post Aggregator', 'smart-post-aggregator' ); ?></h2>
	</div>

	<div id="smart-post-aggregator-settings-body">

		<div id="smart-post-aggregator-settings-sidebar">
			<div id="smart-post-aggregator-settings-menus">
				<ul id="smart-post-aggregator-settings-menus-list">
				<?php
				foreach ( $menus as $menu_id => $menu ) {
					printf(
						'<li id="%1$s" class="%2$s"><a href="%3$s">%4$s</a></li>',
						esc_attr( $menu_id ),
						esc_attr( $active_menu_id == $menu_id ? 'active' : '' ),
						esc_url(
							add_query_arg(
								array(
									'page' => 'smart-post-aggregator-settings',
									'menu' => $menu_id,
								),
								$admin_menu
							)
						),
						esc_html( $menu['label'] )
					);
				}
				?>
				</ul>
			</div>
		</div>

		<div id="smart-post-aggregator-settings-content">
			<form class="smart-post-aggregator-settings-form" data-option_key="<?php echo esc_attr( $option_key ); ?>" id="" method="post">

				<div id="smart-post-aggregator-settings-content-header">
					<div id="smart-post-aggregator-settings-content-label">
						<h3><?php echo esc_html( $active_menu['label'] ); ?></h3>
					</div>

					<div id="smart-post-aggregator-settings-content-actions">
						<input type="reset" class="button" value="<?php esc_attr_e( 'Reset Settings', 'smart-post-aggregator' ); ?>">
						<input type="submit" class="button" value="<?php esc_attr_e( 'Save Settings', 'smart-post-aggregator' ); ?>">
					</div>
				</div>

				<?php if ( count( $submenus ) > 1 ) : ?>
				<div id="smart-post-aggregator-settings-submenus">
					<ul id="smart-post-aggregator-settings-submenus-list">
					<?php
					foreach ( $submenus as $submenu_id => $submenu ) {
						printf(
							'<li id="%1$s" class="%2$s"><a href="%3$s">%4$s</a></li>',
							esc_attr( $submenu_id ),
							esc_attr( $active_submenu_id == $submenu_id ? 'active' : '' ),
							esc_url(
								add_query_arg(
									array(
										'page'    => 'smart-post-aggregator-settings',
										'menu'    => $active_menu_id,
										'submenu' => $submenu_id,
									),
									$admin_menu
								)
							),
							esc_html( $submenu['label'] )
						);
					}
					?>
					</ul>
				</div>
				<?php endif; ?>

				<div id="smart-post-aggregator-settings-sections">
					<?php
					$sections = $menus[ $active_menu_id ]['submenus'][ $active_submenu_id ]['sections'] ?? array();

					foreach ( $sections as $section_id => $section ) {
						printf( '<div class="smart-post-aggregator-settings-section" id="smart-post-aggregator-settings-section-%1$s">', esc_attr( $section_id ) );

						if ( ! empty( $section['label'] ) ) {
							printf( '<h2 class="smart-post-aggregator-settings-section-heading">%1$s</h2>', esc_html( $section['label'] ) );
						}

						if ( ! empty( $section['desc'] ) ) {
							printf( '<p class="smart-post-aggregator-settings-section-desc">%1$s</p>', esc_html( $section['desc'] ) );
						}

						foreach ( $section['fields'] as $field ) {

							if ( class_exists( $field_factory = spa_get_field_factory( $field['type'] ) ) ) {

								if ( isset( $saved_option[ $field['id'] ] ) ) {
									$field['value'] = $saved_option[ $field['id'] ];
								}

								$field_obj = new $field_factory( $field );
								echo $field_obj->render();
							}
						}

						printf( '<input type="submit" class="button" value="%1$s">', esc_attr( __( 'Save Settings', 'smart-post-aggregator' ) ) );

						printf( '</div><!-- #%1$s -->', esc_attr( $section_id ) );
					}
					?>
				</div>

			</form>
		</div>
	</div>
</div>