<?php

/**
 * Fired during plugin activation
 *
 * @link       https://wprepublic.com/
 * @since      1.1.0
 *
 * @package    Wp_Hide_Backed_Notices
 * @subpackage Wp_Hide_Backed_Notices/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wp_Hide_Backed_Notices
 * @subpackage Wp_Hide_Backed_Notices/includes
 * @author     WP Republic <help@wprepublic.com>
 */
class Wp_Hide_Backed_Notices_Activator {

	/**
	 * Activate the plugin.
	 * * Sets default options if they don't exist, ensuring a clean array structure.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		$default_settings = array(
			'Hide_Notices' => 'Hide Notices',
		);

		if ( false == get_option( 'manage_warnings_notice' ) ) {
			update_option( 'manage_warnings_notice', $default_settings );
		}
	}

}
