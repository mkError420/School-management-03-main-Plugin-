<?php
/**
 * Teacher CRUD class.
 *
 * @package School_Management_System
 */

namespace School_Management_System;

/**
 * Teacher CRUD class
 */
class Teacher {

	/**
	 * Add a new teacher.
	 *
	 * @param array $teacher_data Teacher data.
	 * @return int|false|WP_Error Teacher ID on success, false on failure, or WP_Error with details.
	 */
	public static function add( $teacher_data ) {
		if ( empty( $teacher_data['employee_id'] ) || empty( $teacher_data['first_name'] ) || empty( $teacher_data['email'] ) ) {
			return new \WP_Error( 'missing_fields', 'Missing required fields: employee_id, first_name, and email are required.' );
		}

		if ( Database::exists( 'teachers', array( 'employee_id' => $teacher_data['employee_id'] ) ) ) {
			return new \WP_Error( 'duplicate_employee_id', 'A teacher with this employee ID already exists.' );
		}

		if ( empty( $teacher_data['user_id'] ) ) {
			$user_id = self::create_user( $teacher_data );
			if ( is_wp_error( $user_id ) ) {
				return $user_id;
			}
			if ( ! $user_id ) {
				return new \WP_Error( 'user_creation_failed', 'Failed to create WordPress user for teacher. Email may already exist.' );
			}
			$teacher_data['user_id'] = $user_id;
		}

		if ( empty( $teacher_data['status'] ) ) {
			$teacher_data['status'] = 'active';
		}

		$result = Database::insert( 'teachers', $teacher_data );
		if ( ! $result ) {
			global $wpdb;

			// Self-healing: If table doesn't exist, try to create it.
			if ( strpos( $wpdb->last_error, "doesn't exist" ) !== false ) {
				if ( ! class_exists( 'School_Management_System\\Activator' ) ) {
					require_once SMS_PLUGIN_DIR . 'includes/class-activator.php';
				}
				Activator::activate();
				$result = Database::insert( 'teachers', $teacher_data );
			}

			if ( ! $result ) {
				return new \WP_Error( 'database_insert_failed', 'Failed to insert teacher record into database. ' . $wpdb->last_error );
			}
		}

		return $result;
	}

	/**
	 * Get teacher by ID.
	 *
	 * @param int $teacher_id Teacher ID.
	 * @return object|null Teacher object or null if not found.
	 */
	public static function get( $teacher_id ) {
		return Database::get_row( 'teachers', array( 'id' => $teacher_id ) );
	}

	/**
	 * Get teacher by user ID.
	 *
	 * @param int $user_id WordPress user ID.
	 * @return object|null Teacher object or null if not found.
	 */
	public static function get_by_user_id( $user_id ) {
		return Database::get_row( 'teachers', array( 'user_id' => $user_id ) );
	}

	/**
	 * Get all teachers.
	 *
	 * @param array $filters Filter parameters.
	 * @param int   $limit   Number of records per page.
	 * @param int   $offset  Number of records to skip.
	 * @return array Array of teacher objects.
	 */
	public static function get_all( $filters = array(), $limit = 10, $offset = 0 ) {
		return Database::get_results( 'teachers', $filters, 'id', 'DESC', $limit, $offset );
	}

	/**
	 * Update teacher.
	 *
	 * @param int   $teacher_id Teacher ID.
	 * @param array $teacher_data Updated teacher data.
	 * @return int|false Number of rows updated or false on failure.
	 */
	public static function update( $teacher_id, $teacher_data ) {
		if ( empty( $teacher_id ) ) {
			return false;
		}

		return Database::update( 'teachers', $teacher_data, array( 'id' => $teacher_id ) );
	}

	/**
	 * Delete teacher.
	 *
	 * @param int $teacher_id Teacher ID.
	 * @return int|false Number of rows deleted or false on failure.
	 */
	public static function delete( $teacher_id ) {
		if ( empty( $teacher_id ) ) {
			return false;
		}

		// Delete related records.
		Database::delete( 'classes', array( 'teacher_id' => $teacher_id ) );
		Database::delete( 'subjects', array( 'teacher_id' => $teacher_id ) );
		Database::delete( 'timetable', array( 'teacher_id' => $teacher_id ) );

		return Database::delete( 'teachers', array( 'id' => $teacher_id ) );
	}

	/**
	 * Count total teachers.
	 *
	 * @param array $filters Filter parameters.
	 * @return int Total number of teachers.
	 */
	public static function count( $filters = array() ) {
		return Database::count( 'teachers', $filters );
	}

	/**
	 * Create WordPress user for teacher.
	 *
	 * @param array $teacher_data Teacher data.
	 * @return int|WP_Error User ID on success, WP_Error on failure.
	 */
	private static function create_user( $teacher_data ) {
		$email = sanitize_email( $teacher_data['email'] );

		if ( email_exists( $email ) ) {
			return new \WP_Error( 'email_exists', 'Email already exists in WordPress.' );
		}

		$username = sanitize_user( strtolower( $teacher_data['first_name'] . '.' . $teacher_data['last_name'] ) );

		$counter = 1;
		$original_username = $username;
		while ( username_exists( $username ) ) {
			$username = $original_username . $counter;
			$counter++;
		}

		$password = wp_generate_password();

		$user_data = array(
			'user_login' => $username,
			'user_email' => $email,
			'user_pass'  => $password,
			'first_name' => $teacher_data['first_name'],
			'last_name'  => $teacher_data['last_name'],
		);

		$user_id = wp_insert_user( $user_data );

		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		$user = new \WP_User( $user_id );
		$user->set_role( 'sms_teacher' );

		update_user_meta( $user_id, 'sms_temp_password', $password );

		return $user_id;
	}

	/**
	 * Search teachers.
	 *
	 * @param string $search_term Search term.
	 * @return array Array of matching teachers.
	 */
	public static function search( $search_term ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'sms_teachers';
		$search_term = '%' . $wpdb->esc_like( $search_term ) . '%';

		$sql = $wpdb->prepare(
			"SELECT * FROM $table_name 
			WHERE first_name LIKE %s 
			OR last_name LIKE %s 
			OR email LIKE %s 
			OR employee_id LIKE %s 
			ORDER BY first_name ASC",
			$search_term,
			$search_term,
			$search_term,
			$search_term
		);

		return $wpdb->get_results( $sql );
	}
}
