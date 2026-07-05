<?php

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$deletable_options = [ 'smart-post-aggregator_activated', 'smart-post-aggregator_db_version' ];
foreach ( $deletable_options as $option ) {
    delete_option( $option );
}