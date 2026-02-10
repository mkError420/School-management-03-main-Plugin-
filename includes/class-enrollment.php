<?php
/**
 * Enrollment CRUD class.
 *
 * @package School_Management_System
 */

namespace School_Management_System;

/**
 * Enrollment CRUD class
 */
class Enrollment {

	/**
	 * Add a new enrollment.
	 *
	 * @param array $enrollment_data Enrollment data.
	 * @return int|false Enrollment ID on success, false on failure.
	 */
	public static function add( $enrollment_data ) {
		if ( empty( $enrollment_data['student_id'] ) || empty( $enrollment_data['class_id'] ) ) {
			return new \WP_Error( 'missing_fields', __( 'Student and Class are required.', 'school-management-system' ) );
		}

		if ( self::is_enrolled( $enrollment_data['student_id'], $enrollment_data['class_id'] ) ) {
			return new \WP_Error( 'duplicate_enrollment', __( 'This student is already enrolled in this class.', 'school-management-system' ) );
		}

		if ( empty( $enrollment_data['enrollment_date'] ) ) {
			$enrollment_data['enrollment_date'] = current_time( 'Y-m-d' );
		}

		if ( empty( $enrollment_data['status'] ) ) {
			$enrollment_data['status'] = 'active';
		}

		return Database::insert( 'enrollments', $enrollment_data );
	}

	/**
	 * Get enrollment by ID.
	 *
	 * @param int $enrollment_id Enrollment ID.
	 * @return object|null Enrollment object or null if not found.
	 */
	public static function get( $enrollment_id ) {
		return Database::get_row( 'enrollments', array( 'id' => $enrollment_id ) );
	}

	/**
	 * Get all enrollments.
	 *
	 * @param array $filters Filter parameters.
	 * @param int   $limit   Number of records per page.
	 * @param int   $offset  Number of records to skip.
	 * @return array Array of enrollment objects.
	 */
	public static function get_all( $filters = array(), $limit = 10, $offset = 0 ) {
		return Database::get_results( 'enrollments', $filters, 'id', 'DESC', $limit, $offset );
	}

	/**
	 * Update enrollment.
	 *
	 * @param int   $enrollment_id Enrollment ID.
	 * @param array $enrollment_data Updated enrollment data.
	 * @return int|false Number of rows updated or false on failure.
	 */
	public static function update( $enrollment_id, $enrollment_data ) {
		if ( empty( $enrollment_id ) ) {
			return false;
		}

		return Database::update( 'enrollments', $enrollment_data, array( 'id' => $enrollment_id ) );
	}

	/**
	 * Delete enrollment.
	 *
	 * @param int $enrollment_id Enrollment ID.
	 * @return int|false Number of rows deleted or false on failure.
	 */
	public static function delete( $enrollment_id ) {
		if ( empty( $enrollment_id ) ) {
			return false;
		}

		return Database::delete( 'enrollments', array( 'id' => $enrollment_id ) );
	}

	/**
	 * Count total enrollments.
	 *
	 * @param array $filters Filter parameters.
	 * @return int Total number of enrollments.
	 */
	public static function count( $filters = array() ) {
		return Database::count( 'enrollments', $filters );
	}

	/**
	 * Get enrollments for a student.
	 *
	 * @param int $student_id Student ID.
	 * @return array Array of enrollment objects.
	 */
	public static function get_student_enrollments( $student_id ) {
		return Database::get_results( 'enrollments', array( 'student_id' => $student_id ) );
	}

	/**
	 * Get enrollments for a class.
	 *
	 * @param int $class_id Class ID.
	 * @return array Array of enrollment objects.
	 */
	public static function get_class_enrollments( $class_id ) {
		return Database::get_results( 'enrollments', array( 'class_id' => $class_id, 'status' => 'active' ) );
	}

	/**
	 * Check if student is enrolled in class.
	 *
	 * @param int $student_id Student ID.
	 * @param int $class_id Class ID.
	 * @return bool True if enrolled, false otherwise.
	 */
	public static function is_enrolled( $student_id, $class_id ) {
		return Database::exists( 'enrollments', array( 'student_id' => $student_id, 'class_id' => $class_id ) );
	}

	/**
	 * Search enrollments.
	 *
	 * @param string $search_term Search term.
	 * @return array Array of matching enrollments.
	 */
	public static function search( $search_term ) {
		global $wpdb;

		$enrollments_table = $wpdb->prefix . 'sms_enrollments';
		$students_table    = $wpdb->prefix . 'sms_students';
		$classes_table     = $wpdb->prefix . 'sms_classes';
		$search_term       = '%' . $wpdb->esc_like( $search_term ) . '%';

		$sql = $wpdb->prepare(
			"SELECT e.* FROM $enrollments_table e
			LEFT JOIN $students_table s ON e.student_id = s.id
			LEFT JOIN $classes_table c ON e.class_id = c.id
			WHERE s.first_name LIKE %s OR s.last_name LIKE %s OR c.class_name LIKE %s
			ORDER BY e.id DESC",
			$search_term, $search_term, $search_term
		);

		return $wpdb->get_results( $sql );
	}

	/**
	 * Get enrollments with filters (including admission fee status).
	 *
	 * @param array $filters Filter parameters.
	 * @param int   $limit   Number of records.
	 * @param int   $offset  Offset.
	 * @return array Array of enrollment objects.
	 */
	public static function get_with_filters( $filters = array(), $limit = 50, $offset = 0 ) {
		global $wpdb;

		$enrollments_table = $wpdb->prefix . 'sms_enrollments';
		$students_table    = $wpdb->prefix . 'sms_students';
		$classes_table     = $wpdb->prefix . 'sms_classes';
		$fees_table        = $wpdb->prefix . 'sms_fees';

		$sql = "SELECT DISTINCT e.* FROM $enrollments_table e
				LEFT JOIN $students_table s ON e.student_id = s.id
				LEFT JOIN $classes_table c ON e.class_id = c.id";

		// Join fees if filtering by admission fee status
		if ( ! empty( $filters['fee_status'] ) ) {
			$sql .= " LEFT JOIN $fees_table f ON (e.student_id = f.student_id AND e.class_id = f.class_id AND f.fee_type = 'Admission Fee')";
		}

		$sql .= " WHERE 1=1";

		if ( ! empty( $filters['search'] ) ) {
			$search_term = '%' . $wpdb->esc_like( $filters['search'] ) . '%';
			$sql .= $wpdb->prepare( " AND (s.first_name LIKE %s OR s.last_name LIKE %s OR c.class_name LIKE %s OR s.roll_number LIKE %s)", $search_term, $search_term, $search_term, $search_term );
		}

		if ( ! empty( $filters['fee_status'] ) ) {
			if ( 'paid' === $filters['fee_status'] ) {
				$sql .= " AND f.status = 'paid'";
			} elseif ( 'pending' === $filters['fee_status'] ) {
				$sql .= " AND (f.status != 'paid' OR f.status IS NULL)";
			}
		}

		$sql .= " ORDER BY e.id DESC";

		if ( $limit > 0 ) {
			$sql .= $wpdb->prepare( " LIMIT %d OFFSET %d", $limit, $offset );
		}

		return $wpdb->get_results( $sql );
	}
}
