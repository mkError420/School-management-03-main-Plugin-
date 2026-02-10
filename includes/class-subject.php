<?php
/**
 * Subject CRUD class.
 *
 * @package School_Management_System
 */

namespace School_Management_System;

/**
 * Subject CRUD class
 */
class Subject {

	/**
	 * Add a new subject.
	 *
	 * @param array $subject_data Subject data.
	 * @return int|false Subject ID on success, false on failure.
	 */
	public static function add( $subject_data ) {
		if ( empty( $subject_data['subject_name'] ) || empty( $subject_data['subject_code'] ) ) {
			return false;
		}

		if ( Database::exists( 'subjects', array( 'subject_code' => $subject_data['subject_code'] ) ) ) {
			return false;
		}

		if ( empty( $subject_data['status'] ) ) {
			$subject_data['status'] = 'active';
		}

		return Database::insert( 'subjects', $subject_data );
	}

	/**
	 * Get subject by ID.
	 *
	 * @param int $subject_id Subject ID.
	 * @return object|null Subject object or null if not found.
	 */
	public static function get( $subject_id ) {
		return Database::get_row( 'subjects', array( 'id' => $subject_id ) );
	}

	/**
	 * Get all subjects.
	 *
	 * @param array $filters Filter parameters.
	 * @param int   $limit   Number of records per page.
	 * @param int   $offset  Number of records to skip.
	 * @return array Array of subject objects.
	 */
	public static function get_all( $filters = array(), $limit = 10, $offset = 0 ) {
		return Database::get_results( 'subjects', $filters, 'id', 'DESC', $limit, $offset );
	}

	/**
	 * Update subject.
	 *
	 * @param int   $subject_id Subject ID.
	 * @param array $subject_data Updated subject data.
	 * @return int|false Number of rows updated or false on failure.
	 */
	public static function update( $subject_id, $subject_data ) {
		if ( empty( $subject_id ) ) {
			return false;
		}

		return Database::update( 'subjects', $subject_data, array( 'id' => $subject_id ) );
	}

	/**
	 * Delete subject.
	 *
	 * @param int $subject_id Subject ID.
	 * @return int|false Number of rows deleted or false on failure.
	 */
	public static function delete( $subject_id ) {
		if ( empty( $subject_id ) ) {
			return false;
		}

		// Delete related records.
		Database::delete( 'enrollments', array( 'subject_id' => $subject_id ) );
		Database::delete( 'attendance', array( 'subject_id' => $subject_id ) );
		Database::delete( 'exams', array( 'subject_id' => $subject_id ) );
		Database::delete( 'results', array( 'subject_id' => $subject_id ) );
		Database::delete( 'timetable', array( 'subject_id' => $subject_id ) );

		return Database::delete( 'subjects', array( 'id' => $subject_id ) );
	}

	/**
	 * Count total subjects.
	 *
	 * @param array $filters Filter parameters.
	 * @return int Total number of subjects.
	 */
	public static function count( $filters = array() ) {
		return Database::count( 'subjects', $filters );
	}

	/**
	 * Search subjects.
	 *
	 * @param string $search_term Search term.
	 * @return array Array of matching subjects.
	 */
	public static function search( $search_term ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'sms_subjects';
		$search_term = '%' . $wpdb->esc_like( $search_term ) . '%';

		$sql = $wpdb->prepare(
			"SELECT * FROM $table_name 
			WHERE subject_name LIKE %s 
			OR subject_code LIKE %s 
			ORDER BY subject_name ASC",
			$search_term,
			$search_term
		);

		return $wpdb->get_results( $sql );
	}
}
