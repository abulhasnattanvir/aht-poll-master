<?php

/**
 * Uninstall AHT Poll Master
 *
 * @package AHT_Poll_Master
 */

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete the main option
delete_option('ahtpoma_polls');

// Delete user meta for all users
global $wpdb;

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->usermeta} WHERE meta_key = %s",
        'ahtpoma_votes'
    )
);
// phpcs:enable