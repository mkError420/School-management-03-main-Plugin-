<?php
/**
 * Exam CRUD class.
 *
 * @package School_Management_System
 */

namespace School_Management_System;

/**
 * Exam CRUD class
 */
class Exam {

	/**
	 * Add a new exam.
	 *
	 * @param array $exam_data Exam data.
	 * @return int|false Exam ID on success, false on failure.
	 */
	public static function add( $exam_data ) {
		if ( empty( $exam_data['exam_name'] ) || empty( $exam_data['exam_code'] ) || empty( $exam_data['class_id'] ) || empty( $exam_data['exam_date'] ) ) {
			return false;
		}

		if ( Database::exists( 'exams', array( 'exam_code' => $exam_data['exam_code'] ) ) ) {
			return false;
		}

		if ( empty( $exam_data['status'] ) ) {
			$exam_data['status'] = 'scheduled';
		}

		if ( empty( $exam_data['total_marks'] ) ) {
			$exam_data['total_marks'] = 100;
		}

		if ( empty( $exam_data['passing_marks'] ) ) {
			$exam_data['passing_marks'] = 40;
		}

		return Database::insert( 'exams', $exam_data );
	}

	/**
	 * Get exam by ID.
	 *
	 * @param int $exam_id Exam ID.
	 * @return object|null Exam object or null if not found.
	 */
	public static function get( $exam_id ) {
		return Database::get_row( 'exams', array( 'id' => $exam_id ) );
	}

	/**
	 * Get all exams.
	 *
	 * @param array $filters Filter parameters.
	 * @param int   $limit   Number of records per page.
	 * @param int   $offset  Number of records to skip.
	 * @return array Array of exam objects.
	 */
	public static function get_all( $filters = array(), $limit = 10, $offset = 0 ) {
		return Database::get_results( 'exams', $filters, 'exam_date', 'DESC', $limit, $offset );
	}

	/**
	 * Update exam.
	 *
	 * @param int   $exam_id Exam ID.
	 * @param array $exam_data Updated exam data.
	 * @return int|false Number of rows updated or false on failure.
	 */
	public static function update( $exam_id, $exam_data ) {
		if ( empty( $exam_id ) ) {
			return false;
		}

		return Database::update( 'exams', $exam_data, array( 'id' => $exam_id ) );
	}

	/**
	 * Delete exam.
	 *
	 * @param int $exam_id Exam ID.
	 * @return int|false Number of rows deleted or false on failure.
	 */
	public static function delete( $exam_id ) {
		if ( empty( $exam_id ) ) {
			return false;
		}

		// Delete related results.
		Database::delete( 'results', array( 'exam_id' => $exam_id ) );

		return Database::delete( 'exams', array( 'id' => $exam_id ) );
	}

	/**
	 * Count total exams.
	 *
	 * @param array $filters Filter parameters.
	 * @return int Total number of exams.
	 */
	public static function count( $filters = array() ) {
		return Database::count( 'exams', $filters );
	}

	/**
	 * Get exams for a class.
	 *
	 * @param int $class_id Class ID.
	 * @return array Array of exam objects.
	 */
	public static function get_class_exams( $class_id ) {
		return Database::get_results( 'exams', array( 'class_id' => $class_id ), 'exam_date', 'DESC' );
	}

	/**
	 * Get upcoming exams.
	 *
	 * @param int $limit Number of records.
	 * @return array Array of exam objects.
	 */
	public static function get_upcoming_exams( $limit = 5 ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'sms_exams';
		$today = current_time( 'Y-m-d' );

		$sql = $wpdb->prepare(
			"SELECT * FROM $table_name 
			WHERE exam_date >= %s 
			ORDER BY exam_date ASC 
			LIMIT %d",
			$today,
			$limit
		);

		return $wpdb->get_results( $sql );
	}

	/**
	 * Search exams.
	 *
	 * @param string $search_term Search term.
	 * @return array Array of matching exams.
	 */
	public static function search( $search_term ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'sms_exams';
		$search_term = '%' . $wpdb->esc_like( $search_term ) . '%';

		$sql = $wpdb->prepare(
			"SELECT * FROM $table_name 
			WHERE exam_name LIKE %s 
			OR exam_code LIKE %s 
			ORDER BY exam_date DESC",
			$search_term,
			$search_term
		);

		return $wpdb->get_results( $sql );
	}
}
