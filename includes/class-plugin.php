<?php
/**
 * The main plugin class.
 *
 * @package School_Management_System
 */

namespace School_Management_System;

/**
 * The main plugin class
 */
class Plugin {

	/**
	 * The unique identifier of the plugin.
	 *
	 * @var string
	 */
	protected $plugin_name = 'school-management-system';

	/**
	 * The current version of the plugin.
	 *
	 * @var string
	 */
	protected $version = SMS_VERSION;

	/**
	 * Initialize the plugin.
	 */
	public function __construct() {
		// Load required files.
		$this->load_dependencies();
	}

	/**
	 * Load required plugin dependencies.
	 */
	private function load_dependencies() {
		// Database handler.
		require_once SMS_PLUGIN_DIR . 'includes/class-database.php';

		// CRUD classes.
		require_once SMS_PLUGIN_DIR . 'includes/class-student.php';
		require_once SMS_PLUGIN_DIR . 'includes/class-teacher.php';
		require_once SMS_PLUGIN_DIR . 'includes/class-class.php';
		require_once SMS_PLUGIN_DIR . 'includes/class-subject.php';
		require_once SMS_PLUGIN_DIR . 'includes/class-enrollment.php';
		require_once SMS_PLUGIN_DIR . 'includes/class-attendance.php';
		require_once SMS_PLUGIN_DIR . 'includes/class-fee.php';
		require_once SMS_PLUGIN_DIR . 'includes/class-exam.php';
		require_once SMS_PLUGIN_DIR . 'includes/class-result.php';

		// Admin classes.
		require_once SMS_PLUGIN_DIR . 'includes/class-admin.php';
		require_once SMS_PLUGIN_DIR . 'includes/class-assets-loader.php';

		// Authentication and authorization.
		require_once SMS_PLUGIN_DIR . 'includes/class-auth.php';

		// Shortcodes.
		require_once SMS_PLUGIN_DIR . 'includes/class-shortcodes.php';

		// AJAX handlers.
		require_once SMS_PLUGIN_DIR . 'assets/ajax-handlers.php';
	}

	/**
	 * Run the plugin.
	 */
	public function run() {
		$this->define_hooks();
		$this->define_i18n();
		$this->check_database_update();
	}

	/**
	 * Define plugin hooks and filters.
	 */
	private function define_hooks() {
		// Enqueue admin styles and scripts.
		$assets_loader = new Assets_Loader();
		add_action( 'admin_enqueue_scripts', array( $assets_loader, 'enqueue_admin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $assets_loader, 'enqueue_frontend_scripts' ) );

		// Initialize admin.
		if ( is_admin() ) {
			$admin = new Admin();
			add_action( 'admin_menu', array( $admin, 'add_menu' ) );
			add_action( 'admin_init', array( $admin, 'handle_form_submission' ) );
		}

		// Initialize auth.
		$auth = new Auth();
		add_action( 'wp_loaded', array( $auth, 'init' ) );

		// Initialize shortcodes.
		$shortcodes = new Shortcodes();
		add_action( 'init', array( $shortcodes, 'register_shortcodes' ) );

		// AJAX hooks.
		add_action( 'wp_ajax_sms_submit_attendance', 'sms_ajax_submit_attendance' );
		add_action( 'wp_ajax_nopriv_sms_submit_attendance', 'sms_ajax_submit_attendance' );

		add_action( 'wp_ajax_sms_enroll_student', 'sms_ajax_enroll_student' );
		add_action( 'wp_ajax_nopriv_sms_enroll_student', 'sms_ajax_enroll_student' );

		add_action( 'wp_ajax_sms_search_data', 'sms_ajax_search_data' );
		add_action( 'wp_ajax_nopriv_sms_search_data', 'sms_ajax_search_data' );

		add_action( 'wp_ajax_sms_add_result', 'sms_ajax_add_result' );
		add_action( 'wp_ajax_nopriv_sms_add_result', 'sms_ajax_add_result' );

		add_action( 'wp_ajax_sms_upload_results', 'sms_ajax_upload_results' );
		add_action( 'wp_ajax_nopriv_sms_upload_results', 'sms_ajax_upload_results' );
	}

	/**
	 * Check for database updates.
	 */
	private function check_database_update() {
		$installed_version = get_option( 'sms_db_version' );

		if ( version_compare( $installed_version, $this->version, '<' ) ) {
			require_once SMS_PLUGIN_DIR . 'includes/class-activator.php';
			Activator::activate();
		}
	}

	/**
	 * Define text domain for translations.
	 */
	private function define_i18n() {
		load_plugin_textdomain(
			'school-management-system',
			false,
			dirname( plugin_basename( SMS_PLUGIN_FILE ) ) . '/languages/'
		);
	}
}
