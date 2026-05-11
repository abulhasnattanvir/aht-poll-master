<?php

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('mpp_polls');

global $wpdb;

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->usermeta} WHERE meta_key = %s",
        'mpp_votes'
    )
);
// phpcs:enable