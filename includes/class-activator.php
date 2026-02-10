<?php
/**
 * The file that defines the core plugin class used on activation.
 *
 * @package School_Management_System
 */

namespace School_Management_System;

/**
 * The Activator class
 *
 * Fired during plugin activation.
 * This class defines all code necessary to run during the plugin's activation.
 */
class Activator {

	/**
	 * Activate the plugin.
	 *
	 * Creates database tables and sets default options.
	 */
	public static function activate() {
		self::create_tables();
		self::set_default_options();
	}

	/**
	 * Create plugin database tables using dbDelta.
	 */
	private static function create_tables() {
		global $wpdb;

		        $charset_collate = $wpdb->get_charset_collate();
		
		        // Students table.
		        $students_table = $wpdb->prefix . 'sms_students';
		        $sql_students   = "CREATE TABLE $students_table (
		            id mediumint(9) NOT NULL AUTO_INCREMENT,
		            user_id bigint(20) NOT NULL,
		            roll_number varchar(50) NOT NULL UNIQUE,
		            first_name varchar(100) NOT NULL,
		            last_name varchar(100) NOT NULL,
		            email varchar(100) NOT NULL,
		            phone varchar(20),
		            dob date NOT NULL,
		            gender varchar(10),
		            address text,
		            city varchar(50),
		            state varchar(50),
		            zip_code varchar(10),
		            country varchar(50),
		            parent_name varchar(100),
		            parent_email varchar(100),
		            parent_phone varchar(20),
		            enrollment_date date,
		            status varchar(20) DEFAULT 'active',
		            created_at datetime DEFAULT CURRENT_TIMESTAMP,
		            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		            PRIMARY KEY  (id),
		            KEY user_id (user_id),
		            KEY status (status)
		        ) $charset_collate;";
		
		        // Teachers table.
		        $teachers_table = $wpdb->prefix . 'sms_teachers';
		        $sql_teachers   = "CREATE TABLE $teachers_table (
		            id mediumint(9) NOT NULL AUTO_INCREMENT,
		            user_id bigint(20) NOT NULL,
		            employee_id varchar(50) NOT NULL UNIQUE,
		            first_name varchar(100) NOT NULL,
		            last_name varchar(100) NOT NULL,
		            email varchar(100) NOT NULL,
		            phone varchar(20),
		            qualification varchar(255),
		            specialization varchar(100),
		            joining_date date,
		            address text,
		            city varchar(50),
		            state varchar(50),
		            zip_code varchar(10),
		            country varchar(50),
		            status varchar(20) DEFAULT 'active',
		            created_at datetime DEFAULT CURRENT_TIMESTAMP,
		            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		            PRIMARY KEY  (id),
		            KEY user_id (user_id),
		            KEY status (status)
		        ) $charset_collate;";
		
		        // Classes table.
		        $classes_table = $wpdb->prefix . 'sms_classes';
		        $sql_classes   = "CREATE TABLE $classes_table (
		            id mediumint(9) NOT NULL AUTO_INCREMENT,
		            class_name varchar(100) NOT NULL,
		            class_code varchar(50) NOT NULL UNIQUE,
		            description text,
		            capacity int(11),
		            teacher_id mediumint(9),
		            status varchar(20) DEFAULT 'active',
		            created_at datetime DEFAULT CURRENT_TIMESTAMP,
		            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		            PRIMARY KEY  (id),
		            KEY teacher_id (teacher_id),
		            KEY status (status)
		        ) $charset_collate;";
		
		        // Subjects table.
		        $subjects_table = $wpdb->prefix . 'sms_subjects';
		        $sql_subjects   = "CREATE TABLE $subjects_table (
		            id mediumint(9) NOT NULL AUTO_INCREMENT,
		            subject_name varchar(100) NOT NULL,
		            subject_code varchar(50) NOT NULL UNIQUE,
		            description text,
		            teacher_id mediumint(9),
		            class_id mediumint(9),
		            credit_hours int(11),
		            status varchar(20) DEFAULT 'active',
		            created_at datetime DEFAULT CURRENT_TIMESTAMP,
		            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		            PRIMARY KEY  (id),
		            KEY teacher_id (teacher_id),
		            KEY class_id (class_id),
		            KEY status (status)
		        ) $charset_collate;";
		
		        // Enrollments table.
		        $enrollments_table = $wpdb->prefix . 'sms_enrollments';
		        $sql_enrollments   = "CREATE TABLE $enrollments_table (
		            id mediumint(9) NOT NULL AUTO_INCREMENT,
		            student_id mediumint(9) NOT NULL,
		            class_id mediumint(9) NOT NULL,
		            subject_id mediumint(9),
		            enrollment_date date NOT NULL,
		            status varchar(20) DEFAULT 'enrolled',
		            created_at datetime DEFAULT CURRENT_TIMESTAMP,
		            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		            PRIMARY KEY  (id),
		            KEY student_id (student_id),
		            KEY class_id (class_id),
		            KEY subject_id (subject_id),
		            UNIQUE KEY unique_enrollment (student_id, class_id, subject_id)
		        ) $charset_collate;";
		
		        // Notice table.
		        $attendance_table = $wpdb->prefix . 'sms_attendance';
		        $sql_attendance   = "CREATE TABLE $attendance_table (
		            id mediumint(9) NOT NULL AUTO_INCREMENT,
		            student_id mediumint(9) NOT NULL,
		            class_id mediumint(9) NOT NULL,
		            subject_id mediumint(9),
		            attendance_date date NOT NULL,
		            status varchar(20) DEFAULT 'present',
		            remarks text,
		            created_at datetime DEFAULT CURRENT_TIMESTAMP,
		            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		            PRIMARY KEY  (id),
		            KEY student_id (student_id),
		            KEY class_id (class_id),
		            KEY subject_id (subject_id),
		            KEY attendance_date (attendance_date),
		            UNIQUE KEY unique_attendance (student_id, class_id, subject_id, attendance_date)
		        ) $charset_collate;";
		
		        // Fees table.
		        $fees_table = $wpdb->prefix . 'sms_fees';
		        $sql_fees   = "CREATE TABLE $fees_table (
		            id mediumint(9) NOT NULL AUTO_INCREMENT,
		            student_id mediumint(9) NOT NULL,
		            class_id mediumint(9) NOT NULL,
		            fee_type varchar(100) NOT NULL,
		            amount decimal(10, 2) NOT NULL,
		            paid_amount decimal(10, 2) DEFAULT 0,
		            due_date date,
		            payment_date date,
		            status varchar(20) DEFAULT 'pending',
		            remarks text,
		            created_at datetime DEFAULT CURRENT_TIMESTAMP,
		            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		            PRIMARY KEY  (id),
		            KEY student_id (student_id),
		            KEY class_id (class_id),
		            KEY status (status)
		        ) $charset_collate;";
		
		        // Exams table.
		        $exams_table = $wpdb->prefix . 'sms_exams';
		        $sql_exams   = "CREATE TABLE $exams_table (
		            id mediumint(9) NOT NULL AUTO_INCREMENT,
		            exam_name varchar(100) NOT NULL,
		            exam_code varchar(50) NOT NULL UNIQUE,
		            class_id mediumint(9) NOT NULL,
		            subject_id mediumint(9),
		            exam_date date NOT NULL,
		            exam_time time,
		            duration int(11),
		            total_marks decimal(5, 2) DEFAULT 100,
		            passing_marks decimal(5, 2) DEFAULT 40,
		            status varchar(20) DEFAULT 'scheduled',
		            created_at datetime DEFAULT CURRENT_TIMESTAMP,
		            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		            PRIMARY KEY  (id),
		            KEY class_id (class_id),
		            KEY subject_id (subject_id),
		            KEY exam_date (exam_date),
		            KEY status (status)
		        ) $charset_collate;";
		
		        // Results table.
		        $results_table = $wpdb->prefix . 'sms_results';
		        $sql_results   = "CREATE TABLE $results_table (
		            id mediumint(9) NOT NULL AUTO_INCREMENT,
		            student_id mediumint(9) NOT NULL,
		            exam_id mediumint(9) NOT NULL,
		            subject_id mediumint(9),
		            obtained_marks decimal(5, 2),
		            percentage decimal(5, 2),
		            grade varchar(5),
		            remarks text,
		            status varchar(20) DEFAULT 'published',
		            created_at datetime DEFAULT CURRENT_TIMESTAMP,
		            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		            PRIMARY KEY  (id),
		            KEY student_id (student_id),
		            KEY exam_id (exam_id),
		            KEY subject_id (subject_id),
		            UNIQUE KEY unique_result (student_id, exam_id, subject_id)
		        ) $charset_collate;";
		
		        // Result History table.
		        $result_history_table = $wpdb->prefix . 'sms_result_history';
		        $sql_result_history   = "CREATE TABLE $result_history_table (
		            id bigint(20) NOT NULL AUTO_INCREMENT,
		            result_id mediumint(9) NOT NULL,
		            user_id bigint(20) NOT NULL,
		            changed_at datetime NOT NULL,
		            old_marks decimal(5, 2),
		            new_marks decimal(5, 2),
		            old_remarks text,
		            new_remarks text,
		            PRIMARY KEY  (id),
		            KEY result_id (result_id)
		        ) $charset_collate;";

		        // Timetable table.
		        $timetable_table = $wpdb->prefix . 'sms_timetable';
		        $sql_timetable   = "CREATE TABLE $timetable_table (
		            id mediumint(9) NOT NULL AUTO_INCREMENT,
		            class_id mediumint(9) NOT NULL,
		            subject_id mediumint(9) NOT NULL,
		            teacher_id mediumint(9),
		            day_of_week varchar(20) NOT NULL,
		            start_time time NOT NULL,
		            end_time time NOT NULL,
		            room_number varchar(50),
		            created_at datetime DEFAULT CURRENT_TIMESTAMP,
		            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		            PRIMARY KEY  (id),
		            KEY class_id (class_id),
		            KEY subject_id (subject_id),
		            KEY teacher_id (teacher_id)
		        ) $charset_collate;";
		// Execute SQL queries using dbDelta.
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( $sql_students );
		dbDelta( $sql_teachers );
		dbDelta( $sql_classes );
		dbDelta( $sql_subjects );
		dbDelta( $sql_enrollments );
		dbDelta( $sql_attendance );
		dbDelta( $sql_fees );
		dbDelta( $sql_exams );
		dbDelta( $sql_results );
		dbDelta( $sql_result_history );
		dbDelta( $sql_timetable );

		// Post-update: Ensure existing paid fees have paid_amount set.
		$fees_table = $wpdb->prefix . 'sms_fees';
		$wpdb->query( "UPDATE $fees_table SET paid_amount = amount WHERE status = 'paid' AND paid_amount = 0" );

		// Save database version.
		update_option( 'sms_db_version', SMS_VERSION );
	}

	/**
	 * Set default plugin options.
	 */
	private static function set_default_options() {
		if ( ! get_option( 'sms_settings' ) ) {
			$settings = array(
				'academic_year'   => date( 'Y' ),
				'currency'        => '৳',
				'school_name'     => 'My School',
				'school_logo'     => '',
				'school_address'  => '',
				'school_email'    => get_option( 'admin_email' ),
				'school_phone'    => '',
				'passing_marks'   => 40,
			);
			add_option( 'sms_settings', $settings );
		}

		// Add WordPress custom roles.
		self::add_custom_roles();
	}

	/**
	 * Add custom WordPress roles.
	 */
	private static function add_custom_roles() {
		// Teacher role.
		add_role(
			'sms_teacher',
			__( 'Teacher', 'school-management-system' ),
			array(
				'read'          => true,
				'edit_posts'    => true,
				'delete_posts'  => true,
				'manage_sms'    => true,
			)
		);

		// Student role.
		add_role(
			'sms_student',
			__( 'Student', 'school-management-system' ),
			array(
				'read'       => true,
				'manage_sms' => true,
			)
		);

		// Parent role.
		add_role(
			'sms_parent',
			__( 'Parent', 'school-management-system' ),
			array(
				'read'       => true,
				'manage_sms' => true,
			)
		);
	}
}
