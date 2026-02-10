<?php
/**
 * Fee CRUD class.
 *
 * @package School_Management_System
 */

namespace School_Management_System;

/**
 * Fee CRUD class
 */
class Fee {

	/**
	 * Add a new fee record.
	 *
	 * @param array $fee_data Fee data.
	 * @return int|false Fee ID on success, false on failure.
	 */
	public static function add( $fee_data ) {
		if ( empty( $fee_data['student_id'] ) || empty( $fee_data['class_id'] ) || empty( $fee_data['fee_type'] ) || ! isset( $fee_data['amount'] ) || ! is_numeric( $fee_data['amount'] ) ) {
			return new \WP_Error( 'missing_fields', __( 'Missing required fields or invalid amount.', 'school-management-system' ) );
		}

		if ( empty( $fee_data['status'] ) ) {
			$fee_data['status'] = 'pending';
		}

		$result = Database::insert( 'fees', $fee_data );
		if ( false === $result ) {
			global $wpdb;
			return new \WP_Error( 'db_error', $wpdb->last_error );
		}
		return $result;
	}

	/**
	 * Get fee record by ID.
	 *
	 * @param int $fee_id Fee ID.
	 * @return object|null Fee object or null if not found.
	 */
	public static function get( $fee_id ) {
		return Database::get_row( 'fees', array( 'id' => $fee_id ) );
	}

	/**
	 * Get all fee records.
	 *
	 * @param array $filters Filter parameters.
	 * @param int   $limit   Number of records per page.
	 * @param int   $offset  Number of records to skip.
	 * @return array Array of fee objects.
	 */
	public static function get_all( $filters = array(), $limit = 10, $offset = 0 ) {
		return Database::get_results( 'fees', $filters, 'id', 'DESC', $limit, $offset );
	}

	/**
	 * Update fee record.
	 *
	 * @param int   $fee_id Fee ID.
	 * @param array $fee_data Updated fee data.
	 * @return int|false Number of rows updated or false on failure.
	 */
	public static function update( $fee_id, $fee_data ) {
		if ( empty( $fee_id ) ) {
			return false;
		}

		$result = Database::update( 'fees', $fee_data, array( 'id' => $fee_id ) );
		if ( false === $result ) {
			global $wpdb;
			return new \WP_Error( 'db_error', $wpdb->last_error );
		}
		return $result;
	}

	/**
	 * Delete fee record.
	 *
	 * @param int $fee_id Fee ID.
	 * @return int|false Number of rows deleted or false on failure.
	 */
	public static function delete( $fee_id ) {
		if ( empty( $fee_id ) ) {
			return false;
		}

		return Database::delete( 'fees', array( 'id' => $fee_id ) );
	}

	/**
	 * Count total fee records.
	 *
	 * @param array $filters Filter parameters.
	 * @return int Total number of records.
	 */
	public static function count( $filters = array() ) {
		return Database::count( 'fees', $filters );
	}

	/**
	 * Get fees for a student.
	 *
	 * @param int $student_id Student ID.
	 * @return array Array of fee records.
	 */
	public static function get_student_fees( $student_id ) {
		return Database::get_results( 'fees', array( 'student_id' => $student_id ), 'id', 'DESC' );
	}

	/**
	 * Get pending fees for a student.
	 *
	 * @param int $student_id Student ID.
	 * @return array Array of pending fee records.
	 */
	public static function get_pending_fees( $student_id ) {
		return Database::get_results( 'fees', array( 'student_id' => $student_id, 'status' => 'pending' ), 'due_date', 'ASC' );
	}

	/**
	 * Calculate total fees for a student.
	 *
	 * @param int $student_id Student ID.
	 * @return float Total fees.
	 */
	public static function get_total_fees( $student_id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'sms_fees';

		$total = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(amount) FROM $table_name WHERE student_id = %d",
				$student_id
			)
		);

		return floatval( $total ?? 0 );
	}

	/**
	 * Calculate paid fees for a student.
	 *
	 * @param int $student_id Student ID.
	 * @return float Paid fees.
	 */
	public static function get_paid_fees( $student_id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'sms_fees';

		$total = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(paid_amount) FROM $table_name WHERE student_id = %d",
				$student_id
			)
		);

		return floatval( $total ?? 0 );
	}

	/**
	 * Mark fee as paid.
	 *
	 * @param int    $fee_id Fee ID.
	 * @param string $payment_date Payment date.
	 * @return int|false Number of rows updated or false on failure.
	 */
	public static function mark_paid( $fee_id, $payment_date = null ) {
		if ( ! $payment_date ) {
			$payment_date = current_time( 'Y-m-d' );
		}

		// When marking as paid, we assume full payment.
		$fee = self::get( $fee_id );
		return self::update( $fee_id, array( 'status' => 'paid', 'payment_date' => $payment_date, 'paid_amount' => $fee->amount ) );
	}

	/**
	 * Get total amount by status.
	 *
	 * @param string $status Fee status.
	 * @return float Total amount.
	 */
	public static function get_total_amount_by_status( $status ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'sms_fees';

		$total = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(amount) FROM $table_name WHERE status = %s",
				$status
			)
		);

		return floatval( $total ?? 0 );
	}

	/**
	 * Get total collected amount (sum of paid_amount).
	 *
	 * @param array $filters Filter parameters.
	 * @return float Total collected.
	 */
	public static function get_total_collected( $filters = array() ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'sms_fees';
		
		$where = '1=1';
		if ( ! empty( $filters['exclude_fee_type'] ) ) {
			$where .= $wpdb->prepare( " AND fee_type != %s", $filters['exclude_fee_type'] );
		}
		if ( ! empty( $filters['fee_type'] ) ) {
			$where .= $wpdb->prepare( " AND fee_type = %s", $filters['fee_type'] );
		}

		$total = $wpdb->get_var( "SELECT SUM(paid_amount) FROM $table_name WHERE $where" );
		return floatval( $total ?? 0 );
	}

	/**
	 * Get total pending amount (sum of amount - paid_amount).
	 *
	 * @param array $filters Filter parameters.
	 * @return float Total pending.
	 */
	public static function get_total_pending( $filters = array() ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'sms_fees';
		
		$where = '1=1';
		if ( ! empty( $filters['exclude_fee_type'] ) ) {
			$where .= $wpdb->prepare( " AND fee_type != %s", $filters['exclude_fee_type'] );
		}
		if ( ! empty( $filters['fee_type'] ) ) {
			$where .= $wpdb->prepare( " AND fee_type = %s", $filters['fee_type'] );
		}

		// Calculate total amount minus total paid amount.
		// We filter out records where amount is 0 to avoid issues, though not strictly necessary.
		$total_amount = $wpdb->get_var( "SELECT SUM(amount) FROM $table_name WHERE $where" );
		$total_paid   = $wpdb->get_var( "SELECT SUM(paid_amount) FROM $table_name WHERE $where" );
		
		return floatval( ($total_amount ?? 0) - ($total_paid ?? 0) );
	}

	/**
	 * Get upcoming due fees.
	 *
	 * @param int $limit Number of records.
	 * @return array Array of fee objects.
	 */
	public static function get_upcoming_due_fees( $limit = 5 ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'sms_fees';
		$today = current_time( 'Y-m-d' );

		$sql = $wpdb->prepare( "SELECT * FROM $table_name WHERE status = 'pending' AND due_date >= %s ORDER BY due_date ASC LIMIT %d", $today, $limit );

		return $wpdb->get_results( $sql );
	}

	/**
	 * Get recent payments.
	 *
	 * @param int $limit Number of records.
	 * @param array $filters Filter parameters.
	 * @return array Array of fee objects.
	 */
	public static function get_recent_payments( $limit = 5, $filters = array() ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'sms_fees';

		$where = "status IN ('paid', 'partially_paid') AND payment_date IS NOT NULL";
		if ( ! empty( $filters['exclude_fee_type'] ) ) {
			$where .= $wpdb->prepare( " AND fee_type != %s", $filters['exclude_fee_type'] );
		}
		if ( ! empty( $filters['fee_type'] ) ) {
			$where .= $wpdb->prepare( " AND fee_type = %s", $filters['fee_type'] );
		}

		$sql = $wpdb->prepare( "SELECT * FROM $table_name WHERE $where ORDER BY payment_date DESC, id DESC LIMIT %d", $limit );

		return $wpdb->get_results( $sql );
	}

	/**
	 * Get collection summary (Class, Month, Year).
	 *
	 * @param array $filters Filter parameters.
	 * @return array Collection summary data.
	 */
	public static function get_collection_summary( $filters = array() ) {
		global $wpdb;
		$fees_table = $wpdb->prefix . 'sms_fees';
		$classes_table = $wpdb->prefix . 'sms_classes';

		$where = "f.paid_amount > 0";
		if ( ! empty( $filters['exclude_fee_type'] ) ) {
			$where .= $wpdb->prepare( " AND f.fee_type != %s", $filters['exclude_fee_type'] );
		}
		if ( ! empty( $filters['fee_type'] ) ) {
			$where .= $wpdb->prepare( " AND f.fee_type = %s", $filters['fee_type'] );
		}

		// Class wise
		$class_data = $wpdb->get_results(
			"SELECT c.class_name, SUM(f.paid_amount) as total 
			FROM $fees_table f 
			LEFT JOIN $classes_table c ON f.class_id = c.id 
			WHERE $where 
			GROUP BY f.class_id 
			ORDER BY total DESC LIMIT 5"
		);

		// Month wise (Current Year)
		$current_year = current_time( 'Y' );
		$month_data = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT MONTH(payment_date) as month, SUM(paid_amount) as total 
				FROM $fees_table f
				WHERE $where AND YEAR(payment_date) = %d 
				GROUP BY MONTH(payment_date) 
				ORDER BY month ASC",
				$current_year
			)
		);

		// Year wise
		$year_data = $wpdb->get_results(
			"SELECT YEAR(payment_date) as year, SUM(paid_amount) as total 
			FROM $fees_table f
			WHERE $where 
			GROUP BY YEAR(payment_date) 
			ORDER BY year DESC LIMIT 5"
		);

		return array(
			'class_wise' => $class_data,
			'month_wise' => $month_data,
			'year_wise'  => $year_data,
		);
	}

	/**
	 * Get fees report with detailed information and filters.
	 *
	 * @param array $filters Filter parameters.
	 * @return array Array of fee objects with details.
	 */
	public static function get_fees_report( $filters = array() ) {
		global $wpdb;

		$fees_table     = $wpdb->prefix . 'sms_fees';
		$students_table = $wpdb->prefix . 'sms_students';
		$classes_table  = $wpdb->prefix . 'sms_classes';

		$sql = "SELECT f.*, 
				s.first_name, s.last_name, s.roll_number, 
				c.class_name 
				FROM $fees_table f
				LEFT JOIN $students_table s ON f.student_id = s.id
				LEFT JOIN $classes_table c ON f.class_id = c.id
				WHERE 1=1";

		if ( ! empty( $filters['class_id'] ) ) {
			$sql .= $wpdb->prepare( " AND f.class_id = %d", $filters['class_id'] );
		}

		if ( ! empty( $filters['status'] ) ) {
			$sql .= $wpdb->prepare( " AND f.status = %s", $filters['status'] );
		}

		if ( ! empty( $filters['start_date'] ) ) {
			$sql .= $wpdb->prepare( " AND f.due_date >= %s", $filters['start_date'] );
		}

		if ( ! empty( $filters['end_date'] ) ) {
			$sql .= $wpdb->prepare( " AND f.due_date <= %s", $filters['end_date'] );
		}

		if ( ! empty( $filters['exclude_fee_type'] ) ) {
			$sql .= $wpdb->prepare( " AND f.fee_type != %s", $filters['exclude_fee_type'] );
		}

		$sql .= " ORDER BY f.due_date DESC, f.id DESC";

		return $wpdb->get_results( $sql );
	}
}
