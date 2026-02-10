<?php
/**
 * Result CRUD class.
 *
 * @package School_Management_System
 */

namespace School_Management_System;

/**
 * Result CRUD class
 */
class Result {

	/**
	 * Add a new result.
	 *
	 * @param array $result_data Result data.
	 * @return int|false Result ID on success, false on failure.
	 */
	public static function add( $result_data ) {
		if ( empty( $result_data['student_id'] ) || empty( $result_data['exam_id'] ) || ! isset( $result_data['obtained_marks'] ) ) {
			return false;
		}

		if ( empty( $result_data['status'] ) ) {
			$result_data['status'] = 'published';
		}

		// Calculate percentage and grade.
		$exam = Exam::get( $result_data['exam_id'] );
		if ( ! $exam ) {
			return false;
		}

		$percentage = ( $result_data['obtained_marks'] / $exam->total_marks ) * 100;
		$grade = self::calculate_grade( $percentage, $exam->passing_marks );

		$result_data['percentage'] = $percentage;
		$result_data['grade'] = $grade;

		return Database::insert( 'results', $result_data );
	}

	/**
	 * Get result by ID.
	 *
	 * @param int $result_id Result ID.
	 * @return object|null Result object or null if not found.
	 */
	public static function get( $result_id ) {
		return Database::get_row( 'results', array( 'id' => $result_id ) );
	}

	/**
	 * Get all results.
	 *
	 * @param array $filters Filter parameters.
	 * @param int   $limit   Number of records per page.
	 * @param int   $offset  Number of records to skip.
	 * @return array Array of result objects.
	 */
	public static function get_all( $filters = array(), $limit = 10, $offset = 0 ) {
		return Database::get_results( 'results', $filters, 'id', 'DESC', $limit, $offset );
	}

	/**
	 * Update result.
	 *
	 * @param int   $result_id Result ID.
	 * @param array $result_data Updated result data.
	 * @return int|false Number of rows updated or false on failure.
	 */
	public static function update( $result_id, $result_data ) {
		if ( empty( $result_id ) ) {
			return false;
		}

		// Get old data for history log.
		$old_result = self::get( $result_id );

		// Recalculate percentage and grade if marks changed.
		if ( ! empty( $result_data['obtained_marks'] ) ) {
			$result = self::get( $result_id );
			if ( $result ) {
				$exam = Exam::get( $result->exam_id );
				if ( $exam ) {
					$percentage = ( $result_data['obtained_marks'] / $exam->total_marks ) * 100;
					$grade = self::calculate_grade( $percentage, $exam->passing_marks );
					$result_data['percentage'] = $percentage;
					$result_data['grade'] = $grade;
				}
			}
		}

		$result = Database::update( 'results', $result_data, array( 'id' => $result_id ) );

		// Log the change if successful and data has changed.
		if ( false !== $result && $old_result ) {
			$history_data = array(
				'result_id'  => $result_id,
				'user_id'    => get_current_user_id(),
				'changed_at' => current_time( 'mysql' ),
			);
			$has_changed = false;

			if ( isset( $result_data['obtained_marks'] ) && (float) $result_data['obtained_marks'] !== (float) $old_result->obtained_marks ) {
				$history_data['old_marks'] = $old_result->obtained_marks;
				$history_data['new_marks'] = $result_data['obtained_marks'];
				$has_changed = true;
			}

			if ( isset( $result_data['remarks'] ) && $result_data['remarks'] !== $old_result->remarks ) {
				$history_data['old_remarks'] = $old_result->remarks;
				$history_data['new_remarks'] = $result_data['remarks'];
				$has_changed = true;
			}

			if ( $has_changed ) {
				Database::insert( 'result_history', $history_data );
			}
		}

		return $result;
	}

	/**
	 * Delete result.
	 *
	 * @param int $result_id Result ID.
	 * @return int|false Number of rows deleted or false on failure.
	 */
	public static function delete( $result_id ) {
		if ( empty( $result_id ) ) {
			return false;
		}

		return Database::delete( 'results', array( 'id' => $result_id ) );
	}

	/**
	 * Count total results.
	 *
	 * @param array $filters Filter parameters.
	 * @return int Total number of results.
	 */
	public static function count( $filters = array() ) {
		return Database::count( 'results', $filters );
	}

	/**
	 * Get results for a student.
	 *
	 * @param int $student_id Student ID.
	 * @return array Array of result objects.
	 */
	public static function get_student_results( $student_id ) {
		return Database::get_results( 'results', array( 'student_id' => $student_id ), 'id', 'DESC' );
	}

	/**
	 * Get results for an exam.
	 *
	 * @param int $exam_id Exam ID.
	 * @return array Array of result objects.
	 */
	public static function get_exam_results( $exam_id ) {
		return Database::get_results( 'results', array( 'exam_id' => $exam_id ), 'obtained_marks', 'DESC' );
	}

	/**
	 * Calculate grade based on percentage.
	 *
	 * @param float $percentage Percentage.
	 * @param float $passing_marks Passing marks.
	 * @return string Grade.
	 */
	public static function calculate_grade( $percentage, $passing_marks ) {
		if ( $percentage < $passing_marks ) {
			return 'F';
		} elseif ( $percentage >= 80 ) {
			return 'A+';
		} elseif ( $percentage >= 70 ) {
			return 'A';
		} elseif ( $percentage >= 60 ) {
			return 'B';
		} elseif ( $percentage >= 50 ) {
			return 'C';
		} else {
			return 'D';
		}
	}

	/**
	 * Calculate GPA based on percentage.
	 *
	 * @param float $percentage Percentage.
	 * @return float GPA.
	 */
	public static function calculate_gpa( $percentage ) {
		if ( $percentage >= 80 ) return 4.0;
		if ( $percentage >= 70 ) return 3.5;
		if ( $percentage >= 60 ) return 3.0;
		if ( $percentage >= 50 ) return 2.0;
		if ( $percentage >= 40 ) return 1.0;
		return 0.0;
	}

	/**
	 * Get a student's rank in a specific exam based on total marks.
	 *
	 * @param int $student_id Student ID.
	 * @param int $exam_id    Exam ID.
	 * @return int The student's rank.
	 */
	public static function get_student_rank_in_exam( $student_id, $exam_id ) {
		global $wpdb;
		$results_table = $wpdb->prefix . 'sms_results';

		// Get total marks for all students in the exam
		$all_students_marks = $wpdb->get_results( $wpdb->prepare(
			"SELECT student_id, SUM(obtained_marks) as total_marks 
			 FROM {$results_table} 
			 WHERE exam_id = %d 
			 GROUP BY student_id 
			 ORDER BY total_marks DESC",
			$exam_id
		) );

		if ( empty( $all_students_marks ) ) {
			return 0;
		}

		$rank = 0;
		$current_rank = 0;
		$last_marks = -1;
		foreach ( $all_students_marks as $student_result ) {
			$current_rank++;
			if ( $student_result->total_marks !== $last_marks ) {
				$rank = $current_rank;
				$last_marks = $student_result->total_marks;
			}
			if ( (int) $student_result->student_id === (int) $student_id ) {
				return $rank;
			}
		}

		return 0; // Student not found in results
	}

	/**
	 * Get student's average marks in an exam.
	 *
	 * @param int $exam_id Exam ID.
	 * @return float Average marks.
	 */
	public static function get_exam_average( $exam_id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'sms_results';

		$average = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT AVG(obtained_marks) FROM $table_name WHERE exam_id = %d",
				$exam_id
			)
		);

		return floatval( $average ?? 0 );
	}

	/**
	 * Get results with detailed information and filters.
	 *
	 * @param array $filters Filter parameters.
	 * @return array Array of result objects with details.
	 */
	public static function get_by_filters( $filters = array() ) {
		global $wpdb;

		$results_table = $wpdb->prefix . 'sms_results';
		$exams_table   = $wpdb->prefix . 'sms_exams';
		$students_table = $wpdb->prefix . 'sms_students';
		$classes_table = $wpdb->prefix . 'sms_classes';
		$subjects_table = $wpdb->prefix . 'sms_subjects';

		$sql = "SELECT r.*, 
				s.first_name, s.last_name, s.roll_number, 
					e.exam_name, e.total_marks, e.passing_marks, e.exam_date,
				c.class_name, 
				sub.subject_name 
				FROM $results_table r
				LEFT JOIN $students_table s ON r.student_id = s.id
				LEFT JOIN $exams_table e ON r.exam_id = e.id
				LEFT JOIN $classes_table c ON e.class_id = c.id
				LEFT JOIN $subjects_table sub ON r.subject_id = sub.id
				WHERE 1=1";

		if ( ! empty( $filters['class_id'] ) ) {
			$sql .= $wpdb->prepare( " AND e.class_id = %d", $filters['class_id'] );
		}

		if ( ! empty( $filters['exam_id'] ) ) {
			$sql .= $wpdb->prepare( " AND r.exam_id = %d", $filters['exam_id'] );
		}

		if ( ! empty( $filters['subject_id'] ) ) {
			$sql .= $wpdb->prepare( " AND r.subject_id = %d", $filters['subject_id'] );
		}

		if ( ! empty( $filters['student_id'] ) ) {
			$sql .= $wpdb->prepare( " AND r.student_id = %d", $filters['student_id'] );
		}

		if ( ! empty( $filters['student_name'] ) ) {
			$name_search = '%' . $wpdb->esc_like( $filters['student_name'] ) . '%';
			$sql .= $wpdb->prepare( " AND (s.first_name LIKE %s OR s.last_name LIKE %s)", $name_search, $name_search );
		}

		$sql .= " ORDER BY r.id DESC";

		return $wpdb->get_results( $sql );
	}

	/**
	 * Get overall average percentage across all results.
	 *
	 * @return float Overall average percentage.
	 */
	public static function get_overall_average() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'sms_results';

		$average = $wpdb->get_var(
			"SELECT AVG(percentage) FROM $table_name WHERE percentage IS NOT NULL"
		);

		return floatval( $average ?? 0 );
	}

	/**
	 * Get grade distribution statistics.
	 *
	 * @return array Grade counts by grade.
	 */
	public static function get_grade_distribution() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'sms_results';

		$results = $wpdb->get_results(
			"SELECT grade, COUNT(*) as count 
			 FROM $table_name 
			 WHERE grade IS NOT NULL 
			 GROUP BY grade 
			 ORDER BY grade"
		);

		$distribution = array();
		foreach ( $results as $result ) {
			$distribution[ $result->grade ] = intval( $result->count );
		}

		return $distribution;
	}
}
