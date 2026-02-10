<?php
/**
 * The file that defines the core plugin class used on deactivation.
 *
 * @package School_Management_System
 */

namespace School_Management_System;

/**
 * The Deactivator class
 *
 * Fired during plugin deactivation.
 * This class defines all code necessary to run during the plugin's deactivation.
 */
class Deactivator {

	/**
	 * Deactivate the plugin.
	 *
	 * Performs cleanup actions during deactivation.
	 */
	public static function deactivate() {
		self::remove_custom_roles();
		self::clear_scheduled_events();
	}

	/**
	 * Remove custom WordPress roles.
	 */
	private static function remove_custom_roles() {
		remove_role( 'sms_teacher' );
		remove_role( 'sms_student' );
		remove_role( 'sms_parent' );
	}

	/**
	 * Clear any scheduled cron events.
	 */
	private static function clear_scheduled_events() {
		wp_clear_scheduled_hook( 'sms_daily_attendance_report' );
		wp_clear_scheduled_hook( 'sms_monthly_fee_reminder' );
	}
}
