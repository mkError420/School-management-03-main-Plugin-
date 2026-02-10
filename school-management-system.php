<?php
/**
 * Plugin Name: School Management System
 * Plugin URI: https://example.com/school-management-system
 * Description: A complete school management system for WordPress with student, teacher, class, and exam management.
 * Version: 3.0.1
 * Author: MK.Rabbani
 * Author URI: https://github.com/mkError420/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: school-management-system
 * Domain Path: /languages
 *
 * @package School_Management_System
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define plugin constants.
define( 'SMS_VERSION', '1.0.1' );
define( 'SMS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SMS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SMS_PLUGIN_FILE', __FILE__ );

// Include autoloader and required files.
require_once SMS_PLUGIN_DIR . 'includes/class-activator.php';
require_once SMS_PLUGIN_DIR . 'includes/class-deactivator.php';
require_once SMS_PLUGIN_DIR . 'includes/class-plugin.php';

/**
 * The code that runs during plugin activation.
 */
function sms_activate_plugin() {
	School_Management_System\Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function sms_deactivate_plugin() {
	School_Management_System\Deactivator::deactivate();
}

register_activation_hook( SMS_PLUGIN_FILE, 'sms_activate_plugin' );
register_deactivation_hook( SMS_PLUGIN_FILE, 'sms_deactivate_plugin' );

/**
 * Begins execution of the plugin.
 */
function sms_run() {
	$plugin = new School_Management_System\Plugin();
	$plugin->run();
}

sms_run();
