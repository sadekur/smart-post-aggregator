<?php

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$deletable_options = [ 'smart-post-aggregantor_activated', 'smart-post-aggregantor_db_version' ];
foreach ( $deletable_options as $option ) {
    delete_option( $option );
}