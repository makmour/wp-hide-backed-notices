<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://wprepublic.com/
 * @since      1.4.0
 *
 * @package    Wp_Hide_Backed_Notices
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Clean up option from the database
delete_option( 'manage_warnings_notice' );
