<?php
/**
 * Class CRUD class.
 *
 * @package School_Management_System
 */

namespace School_Management_System;

/**
 * Class CRUD class
 */
class Classm {

	/**
	 * Add a new class.
	 *
	 * @param array $class_data Class data.
	 * @return int|false Class ID on success, false on failure.
	 */
	public static function add( $class_data ) {
		if ( empty( $class_data['class_name'] ) || empty( $class_data['class_code'] ) ) {
			return false;
		}

		if ( Database::exists( 'classes', array( 'class_code' => $class_data['class_code'] ) ) ) {
			return false;
		}

		if ( empty( $class_data['status'] ) ) {
			$class_data['status'] = 'active';
		}

		return Database::insert( 'classes', $class_data );
	}

	/**
	 * Get class by ID.
	 *
	 * @param int $class_id Class ID.
	 * @return object|null Class object or null if not found.
	 */
	public static function get( $class_id ) {
		return Database::get_row( 'classes', array( 'id' => $class_id ) );
	}

	/**
	 * Get all classes.
	 *
	 * @param array $filters Filter parameters.
	 * @param int   $limit   Number of records per page.
	 * @param int   $offset  Number of records to skip.
	 * @return array Array of class objects.
	 */
	public static function get_all( $filters = array(), $limit = 10, $offset = 0 ) {
		return Database::get_results( 'classes', $filters, 'id', 'DESC', $limit, $offset );
	}

	/**
	 * Update class.
	 *
	 * @param int   $class_id Class ID.
	 * @param array $class_data Updated class data.
	 * @return int|false Number of rows updated or false on failure.
	 */
	public static function update( $class_id, $class_data ) {
		if ( empty( $class_id ) ) {
			return false;
		}

		return Database::update( 'classes', $class_data, array( 'id' => $class_id ) );
	}

	/**
	 * Delete class.
	 *
	 * @param int $class_id Class ID.
	 * @return int|false Number of rows deleted or false on failure.
	 */
	public static function delete( $class_id ) {
		if ( empty( $class_id ) ) {
			return false;
		}

		// Delete related records.
		Database::delete( 'subjects', array( 'class_id' => $class_id ) );
		Database::delete( 'enrollments', array( 'class_id' => $class_id ) );
		Database::delete( 'attendance', array( 'class_id' => $class_id ) );
		Database::delete( 'fees', array( 'class_id' => $class_id ) );
		Database::delete( 'exams', array( 'class_id' => $class_id ) );
		Database::delete( 'timetable', array( 'class_id' => $class_id ) );

		return Database::delete( 'classes', array( 'id' => $class_id ) );
	}

	/**
	 * Count total classes.
	 *
	 * @param array $filters Filter parameters.
	 * @return int Total number of classes.
	 */
	public static function count( $filters = array() ) {
		return Database::count( 'classes', $filters );
	}

	/**
	 * Get students in a class.
	 *
	 * @param int $class_id Class ID.
	 * @return array Array of student objects.
	 */
	public static function get_students( $class_id ) {
		global $wpdb;

		$enrollments_table = $wpdb->prefix . 'sms_enrollments';
		$students_table    = $wpdb->prefix . 'sms_students';

		$sql = $wpdb->prepare(
			"SELECT s.* FROM $students_table s
			INNER JOIN $enrollments_table e ON s.id = e.student_id
			WHERE e.class_id = %d AND e.status = 'active'
			ORDER BY s.first_name ASC",
			$class_id
		);

		return $wpdb->get_results( $sql );
	}

	/**
	 * Search classes.
	 *
	 * @param string $search_term Search term.
	 * @return array Array of matching classes.
	 */
	public static function search( $search_term ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'sms_classes';
		$search_term = '%' . $wpdb->esc_like( $search_term ) . '%';

		$sql = $wpdb->prepare(
			"SELECT * FROM $table_name 
			WHERE class_name LIKE %s 
			OR class_code LIKE %s 
			ORDER BY class_name ASC",
			$search_term,
			$search_term
		);

		return $wpdb->get_results( $sql );
	}
}
