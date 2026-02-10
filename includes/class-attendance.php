<?php
/**
 * Attendance CRUD class.
 *
 * @package School_Management_System
 */

namespace School_Management_System;

/**
 * Attendance CRUD class
 */
class Attendance {

	/**
	 * Add a new attendance record.
	 *
	 * @param array $attendance_data Attendance data.
	 * @return int|false Attendance ID on success, false on failure.
	 */
	public static function add( $attendance_data ) {
		if ( empty( $attendance_data['student_id'] ) || empty( $attendance_data['class_id'] ) || empty( $attendance_data['attendance_date'] ) ) {
			return false;
		}

		if ( empty( $attendance_data['status'] ) ) {
			$attendance_data['status'] = 'present';
		}

		return Database::insert( 'attendance', $attendance_data );
	}

	/**
	 * Get attendance record by ID.
	 *
	 * @param int $attendance_id Attendance ID.
	 * @return object|null Attendance object or null if not found.
	 */
	public static function get( $attendance_id ) {
		return Database::get_row( 'attendance', array( 'id' => $attendance_id ) );
	}

	/**
	 * Get all attendance records.
	 *
	 * @param array $filters Filter parameters.
	 * @param int   $limit   Number of records per page.
	 * @param int   $offset  Number of records to skip.
	 * @return array Array of attendance objects.
	 */
	public static function get_all( $filters = array(), $limit = 10, $offset = 0 ) {
		return Database::get_results( 'attendance', $filters, 'attendance_date', 'DESC', $limit, $offset );
	}

	/**
	 * Update attendance record.
	 *
	 * @param int   $attendance_id Attendance ID.
	 * @param array $attendance_data Updated attendance data.
	 * @return int|false Number of rows updated or false on failure.
	 */
	public static function update( $attendance_id, $attendance_data ) {
		if ( empty( $attendance_id ) ) {
			return false;
		}

		return Database::update( 'attendance', $attendance_data, array( 'id' => $attendance_id ) );
	}

	/**
	 * Delete attendance record.
	 *
	 * @param int $attendance_id Attendance ID.
	 * @return int|false Number of rows deleted or false on failure.
	 */
	public static function delete( $attendance_id ) {
		if ( empty( $attendance_id ) ) {
			return false;
		}

		return Database::delete( 'attendance', array( 'id' => $attendance_id ) );
	}

	/**
	 * Count total attendance records.
	 *
	 * @param array $filters Filter parameters.
	 * @return int Total number of records.
	 */
	public static function count( $filters = array() ) {
		return Database::count( 'attendance', $filters );
	}

	/**
	 * Get attendance for a student.
	 *
	 * @param int $student_id Student ID.
	 * @param int $class_id Class ID.
	 * @return array Array of attendance records.
	 */
	public static function get_student_attendance( $student_id, $class_id = null ) {
		$filters = array( 'student_id' => $student_id );
		if ( $class_id ) {
			$filters['class_id'] = $class_id;
		}

		return Database::get_results( 'attendance', $filters, 'attendance_date', 'DESC' );
	}

	/**
	 * Get attendance percentage for a student.
	 *
	 * @param int $student_id Student ID.
	 * @param int $class_id Class ID.
	 * @return float Attendance percentage.
	 */
	public static function get_attendance_percentage( $student_id, $class_id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'sms_attendance';

		$total = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $table_name WHERE student_id = %d AND class_id = %d",
				$student_id,
				$class_id
			)
		);

		if ( $total === 0 ) {
			return 0;
		}

		$present = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $table_name WHERE student_id = %d AND class_id = %d AND status = 'present'",
				$student_id,
				$class_id
			)
		);

		return ( $present / $total ) * 100;
	}

	/**
	 * Mark attendance for a date.
	 *
	 * @param int    $student_id Student ID.
	 * @param int    $class_id Class ID.
	 * @param string $attendance_date Attendance date.
	 * @param string $status Attendance status.
	 * @return int|false Attendance ID or false on failure.
	 */
	public static function mark_attendance( $student_id, $class_id, $attendance_date, $status = 'present' ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'sms_attendance';

		// Check if record already exists.
		$existing = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $table_name WHERE student_id = %d AND class_id = %d AND attendance_date = %s",
				$student_id,
				$class_id,
				$attendance_date
			)
		);

		if ( $existing ) {
			// Update existing record.
			return self::update( $existing->id, array( 'status' => $status ) );
		} else {
			// Create new record.
			return self::add( array(
				'student_id'     => $student_id,
				'class_id'       => $class_id,
				'attendance_date' => $attendance_date,
				'status'         => $status,
			) );
		}
	}

	/**
	 * Get monthly attendance report for a class.
	 *
	 * @param int $class_id Class ID.
	 * @param int $year     Year.
	 * @param int $month    Month.
	 * @return array Report data, structured by student_id and day.
	 */
	public static function get_monthly_class_attendance_report( $class_id, $year, $month ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'sms_attendance';
		$start_date = date( 'Y-m-d', mktime( 0, 0, 0, $month, 1, $year ) );
		$end_date   = date( 'Y-m-t', strtotime( $start_date ) );

		$sql = $wpdb->prepare(
			"SELECT student_id, DAY(attendance_date) as day, status 
			FROM $table_name 
			WHERE class_id = %d 
			AND attendance_date BETWEEN %s AND %s",
			$class_id,
			$start_date,
			$end_date
		);

		$results = $wpdb->get_results( $sql );

		$report = array();
		if ( ! empty( $results ) ) {
			foreach ( $results as $record ) {
				if ( ! isset( $report[ $record->student_id ] ) ) {
					$report[ $record->student_id ] = array();
				}
				$report[ $record->student_id ][ (int) $record->day ] = $record->status;
			}
		}

		return $report;
	}
}
