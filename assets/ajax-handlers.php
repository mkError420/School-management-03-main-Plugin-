<?php
/**
 * AJAX handlers for School Management System.
 *
 * @package School_Management_System
 */

use School_Management_System\Result;
use School_Management_System\Enrollment;
use School_Management_System\Student;
use School_Management_System\Classm;
use School_Management_System\Subject;
use School_Management_System\Exam;
use School_Management_System\Fee;
use School_Management_System\Payment;
use School_Management_System\Attendance;
use School_Management_System\Timetable;
use School_Management_System\Database;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Test AJAX handler to verify AJAX is working.
 */
function sms_ajax_test() {
	check_ajax_referer( 'sms_get_students_nonce', 'nonce' );
	wp_send_json_success( array( 'message' => 'AJAX is working!', 'test' => true ) );
}

add_action( 'wp_ajax_sms_test', 'sms_ajax_test' );

/**
 * Get students by class via AJAX.
 */
function sms_ajax_get_students_by_class() {
	check_ajax_referer( 'sms_get_students_nonce', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'Unauthorized', 'school-management-system' ) );
	}

	$class_id = intval( $_POST['class_id'] ?? 0 );

	// Debug logging
	error_log('AJAX: Get students for class ID: ' . $class_id);

	if ( empty( $class_id ) ) {
		error_log('AJAX: Class ID is empty');
		wp_send_json_error( __( 'Class ID is required', 'school-management-system' ) );
	}

	// Get enrollments for this class
	$class_enrollments = Enrollment::get_class_enrollments( $class_id );
	error_log('AJAX: Found enrollments: ' . count($class_enrollments));
	
	if ( empty( $class_enrollments ) ) {
		error_log('AJAX: No enrollments found for class ' . $class_id);
		wp_send_json_success( array() );
	}

	// Get student details
	$students = array();
	$all_students = Student::get_all( array(), 1000 );
	error_log('AJAX: Total students in database: ' . count($all_students));
	
	// Create student lookup
	$student_lookup = array();
	foreach ( $all_students as $student ) {
		$student_lookup[$student->id] = $student;
	}

	// Build students array for this class
	foreach ( $class_enrollments as $enrollment ) {
		if ( isset( $student_lookup[$enrollment->student_id] ) ) {
			$student = $student_lookup[$enrollment->student_id];
			$students[] = array(
				'id' => $student->id,
				'name' => $student->first_name . ' ' . $student->last_name
			);
			error_log('AJAX: Added student: ' . $student->first_name . ' ' . $student->last_name);
		} else {
			error_log('AJAX: Student not found for enrollment student_id: ' . $enrollment->student_id);
		}
	}

	error_log('AJAX: Final students array: ' . print_r($students, true));
	wp_send_json_success( $students );
}

add_action( 'wp_ajax_sms_get_students_by_class', 'sms_ajax_get_students_by_class' );
function sms_ajax_submit_attendance() {
	check_ajax_referer( 'sms_admin_nonce', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'Unauthorized', 'school-management-system' ) );
	}

	$student_id = intval( $_POST['student_id'] ?? 0 );
	$class_id = intval( $_POST['class_id'] ?? 0 );
	$attendance_date = sanitize_text_field( $_POST['attendance_date'] ?? '' );
	$status = sanitize_text_field( $_POST['status'] ?? 'present' );

	if ( empty( $student_id ) || empty( $class_id ) || empty( $attendance_date ) ) {
		wp_send_json_error( __( 'Missing required fields', 'school-management-system' ) );
	}

	$result = Attendance::mark_attendance( $student_id, $class_id, $attendance_date, $status );

	if ( $result ) {
		wp_send_json_success( __( 'Attendance marked successfully', 'school-management-system' ) );
	} else {
		wp_send_json_error( __( 'Failed to mark attendance', 'school-management-system' ) );
	}
}

/**
 * Enroll student via AJAX.
 */
function sms_ajax_enroll_student() {
	check_ajax_referer( 'sms_admin_nonce', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'Unauthorized', 'school-management-system' ) );
	}

	$enrollment_id = intval( $_POST['enrollment_id'] ?? 0 );
	$student_id = intval( $_POST['student_id'] ?? 0 );
	$class_id = isset( $_POST['class_id'] ) ? intval( $_POST['class_id'] ) : 0;
	$subject_id = intval( $_POST['subject_id'] ?? 0 );
	$enrollment_date = sanitize_text_field( $_POST['enrollment_date'] ?? '' );
	$status = sanitize_text_field( $_POST['status'] ?? '' );
	$admission_fee = isset( $_POST['admission_fee'] ) ? floatval( $_POST['admission_fee'] ) : 0;

	$student_data = array(
		'first_name'   => sanitize_text_field( $_POST['first_name'] ?? '' ),
		'last_name'    => sanitize_text_field( $_POST['last_name'] ?? '' ),
		'roll_number'  => sanitize_text_field( $_POST['roll_number'] ?? '' ),
		'email'        => sanitize_email( $_POST['email'] ?? '' ),
		'status'       => ! empty( $status ) ? $status : 'active',
		'address'      => sanitize_textarea_field( $_POST['address'] ?? '' ),
		'parent_name'  => sanitize_text_field( $_POST['parent_name'] ?? '' ),
		'parent_phone' => sanitize_text_field( $_POST['parent_phone'] ?? '' ),
	);

	if ( empty( $student_id ) ) {

		if ( empty( $student_data['first_name'] ) ) {
			$student_data['first_name'] = 'Student';
		}

		if ( empty( $student_data['roll_number'] ) ) {
			$student_data['roll_number'] = 'STU-' . date( 'Y' ) . '-' . str_pad( Student::count() + 1, 4, '0', STR_PAD_LEFT ) . '-' . rand( 100, 999 );
		}
		
		if ( empty( $student_data['email'] ) ) {
			$student_data['email'] = strtolower( preg_replace( '/[^a-z0-9]/i', '', $student_data['roll_number'] ) ) . '@school.local';
		}

		$student_data['dob'] = '2000-01-01';
		$student_data['gender'] = 'Male';
		if ( empty( $student_data['parent_name'] ) ) {
			$student_data['parent_name'] = 'Parent of ' . $student_data['first_name'];
		}
		if ( empty( $student_data['parent_phone'] ) ) {
			$student_data['parent_phone'] = '1234567890';
		}
		if ( empty( $student_data['address'] ) ) {
			$student_data['address'] = 'School Address';
		}

		$new_student_id = Student::add( $student_data );
		
		if ( is_wp_error( $new_student_id ) ) {
			wp_send_json_error( $new_student_id->get_error_message() );
		}

		if ( ! $new_student_id ) {
			wp_send_json_error( __( 'Failed to create student record. Please check database logs.', 'school-management-system' ) );
		}
		
		$student_id = $new_student_id;
	} else {
		// Update existing student data
		Student::update( $student_id, $student_data );
	}

	if ( empty( $class_id ) ) {
		wp_send_json_error( __( 'Class is required. Please select a class.', 'school-management-system' ) );
	}

	$enrollment_data = array(
		'student_id' => $student_id,
		'class_id'   => $class_id,
	);

	if ( ! empty( $subject_id ) ) {
		$enrollment_data['subject_id'] = $subject_id;
	}
	
	if ( ! empty( $enrollment_date ) ) {
		$enrollment_data['enrollment_date'] = $enrollment_date;
	}
	
	if ( ! empty( $status ) ) {
		$enrollment_data['status'] = $status;
	}

	if ( $enrollment_id > 0 ) {
		$result = Enrollment::update( $enrollment_id, $enrollment_data );
		$success_message = __( 'Enrollment updated successfully', 'school-management-system' );
	} else {
		$result = Enrollment::add( $enrollment_data );
		$success_message = __( 'Student enrolled successfully', 'school-management-system' );
	}

	if ( is_wp_error( $result ) ) {
		wp_send_json_error( $result->get_error_message() );
	} elseif ( $result !== false ) {
		// Handle Admission Fee (Create or Update)
		if ( isset( $_POST['admission_fee'] ) ) {
			// Check for existing admission fee
			$existing_fees = Fee::get_student_fees( $student_id );
			$admission_fee_id = 0;
			
			if ( ! empty( $existing_fees ) ) {
				foreach ( $existing_fees as $f ) {
					if ( 'Admission Fee' === $f->fee_type && $f->class_id == $class_id ) {
						$admission_fee_id = $f->id;
						break;
					}
				}
			}

			$fee_data = array(
				'student_id'   => $student_id,
				'class_id'     => $class_id,
				'fee_type'     => 'Admission Fee',
				'amount'       => $admission_fee,
				'status'       => $status === 'active' ? 'paid' : 'pending',
				'due_date'     => $enrollment_date,
				'paid_amount'  => $status === 'active' ? $admission_fee : 0,
				'payment_date' => $status === 'active' ? $enrollment_date : null,
			);

			if ( $admission_fee_id > 0 ) {
				Fee::update( $admission_fee_id, $fee_data );
			} elseif ( $admission_fee > 0 ) {
				Fee::add( $fee_data );
			}
		}

		wp_send_json_success( $success_message );
	} else {
		wp_send_json_error( __( 'Failed to enroll student', 'school-management-system' ) );
	}
}

/**
 * Search data via AJAX.
 */
function sms_ajax_search_data() {
	check_ajax_referer( 'sms_admin_nonce', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'Unauthorized', 'school-management-system' ) );
	}

	$search_term = sanitize_text_field( $_POST['search_term'] ?? '' );
	$type = sanitize_text_field( $_POST['type'] ?? 'students' );

	if ( empty( $search_term ) ) {
		wp_send_json_error( __( 'Search term is required', 'school-management-system' ) );
	}

	$results = array();

	switch ( $type ) {
		case 'students':
			$results = Student::search( $search_term );
			break;
		case 'teachers':
			$results = Teacher::search( $search_term );
			break;
		case 'classes':
			$results = Classm::search( $search_term );
			break;
		case 'subjects':
			$results = Subject::search( $search_term );
			break;
		case 'exams':
			$results = Exam::search( $search_term );
			break;
	}

	if ( ! empty( $results ) ) {
		wp_send_json_success( $results );
	} else {
		wp_send_json_error( __( 'No results found', 'school-management-system' ) );
	}
}

/**
 * Add/Update result via AJAX.
 */
function sms_ajax_add_result() {
	// Check nonce. Support both admin script nonce and form nonce.
	$nonce_verified = false;
	if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'sms_admin_nonce' ) ) {
		$nonce_verified = true;
	} elseif ( isset( $_POST['sms_nonce'] ) && wp_verify_nonce( $_POST['sms_nonce'], 'sms_nonce_form' ) ) {
		$nonce_verified = true;
	}

	if ( ! $nonce_verified ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed.', 'school-management-system' ) ) );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Unauthorized', 'school-management-system' ) ) );
	}

	$exam_id        = intval( $_POST['exam_id'] ?? 0 );
	$subject_id     = intval( $_POST['subject_id'] ?? 0 );
	$student_id     = intval( $_POST['student_id'] ?? 0 );
	$obtained_marks = isset( $_POST['obtained_marks'] ) ? floatval( $_POST['obtained_marks'] ) : '';

	if ( empty( $exam_id ) || empty( $subject_id ) || empty( $student_id ) || '' === $obtained_marks ) {
		wp_send_json_error( array( 'message' => __( 'Please fill in all required fields.', 'school-management-system' ) ) );
	}

	// Calculate percentage and grade
	$exam = Exam::get( $exam_id );
	if ( ! $exam ) {
		wp_send_json_error( array( 'message' => __( 'Exam not found.', 'school-management-system' ) ) );
	}

	$total_marks = floatval( $exam->total_marks );
	if ( $total_marks <= 0 ) {
		$total_marks = 100;
	}

	$percentage = ( $obtained_marks / $total_marks ) * 100;
	
	$grade = 'F';
	if ( $percentage >= 80 ) {
		$grade = 'A+';
	} elseif ( $percentage >= 70 ) {
		$grade = 'A';
	} elseif ( $percentage >= 60 ) {
		$grade = 'A-';
	} elseif ( $percentage >= 50 ) {
		$grade = 'B';
	} elseif ( $percentage >= 40 ) {
		$grade = 'C';
	} elseif ( $percentage >= 33 ) {
		$grade = 'D';
	}

	$result_data = array(
		'exam_id'        => $exam_id,
		'subject_id'     => $subject_id,
		'student_id'     => $student_id,
		'obtained_marks' => $obtained_marks,
		'percentage'     => $percentage,
		'grade'          => $grade,
		'status'         => 'published',
	);

	// Check if result already exists.
	global $wpdb;
	$table_name = $wpdb->prefix . 'sms_results';
	$existing_id = $wpdb->get_var( $wpdb->prepare(
		"SELECT id FROM $table_name WHERE student_id = %d AND exam_id = %d AND subject_id = %d",
		$student_id,
		$exam_id,
		$subject_id
	) );

	if ( $existing_id ) {
		$result = Result::update( $existing_id, $result_data );
		$message = __( 'Result updated successfully.', 'school-management-system' );
	} else {
		$result = Result::add( $result_data );
		
		// Self-healing: If add fails, it might be due to missing columns in DB.
		if ( false === $result ) {
			// Try to load Activator and update tables
			if ( defined( 'SMS_PLUGIN_DIR' ) && file_exists( SMS_PLUGIN_DIR . 'includes/class-activator.php' ) ) {
				require_once SMS_PLUGIN_DIR . 'includes/class-activator.php';
				\School_Management_System\Activator::activate();
				// Retry adding
				$result = Result::add( $result_data );
			}
		}
		
		$message = __( 'Result added successfully.', 'school-management-system' );
	}

	if ( is_wp_error( $result ) ) {
		wp_send_json_error( array( 'message' => $result->get_error_message() ) );
	} elseif ( false !== $result ) {
		wp_send_json_success( array( 'message' => $message ) );
	} else {
		$error_msg = ! empty( $wpdb->last_error ) ? $wpdb->last_error : __( 'Failed to save result.', 'school-management-system' );
		wp_send_json_error( array( 'message' => $error_msg ) );
	}
}
/**
 * Upload results from Excel/CSV file via AJAX.
 */
function sms_ajax_upload_results() {
	// Enable error logging for debugging
	error_log('SMS Upload Results AJAX: Started');
	
	if ( ! defined( 'DOING_AJAX' ) ) {
		define( 'DOING_AJAX', true );
	}
	
	// Check nonce
	if ( ! isset( $_POST['sms_nonce'] ) || ! wp_verify_nonce( $_POST['sms_nonce'], 'sms_nonce_form' ) ) {
		error_log('SMS Upload Results AJAX: Nonce verification failed');
		wp_send_json_error( __( 'Security check failed.', 'school-management-system' ) );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		error_log('SMS Upload Results AJAX: Unauthorized access');
		wp_send_json_error( __( 'Unauthorized', 'school-management-system' ) );
	}

	$exam_id = intval( $_POST['exam_id'] ?? 0 );
	$subject_id = intval( $_POST['subject_id'] ?? 0 );

	error_log('SMS Upload Results AJAX: Exam: ' . $exam_id . ', Subject: ' . $subject_id);

	// Validate required fields
	if ( empty( $exam_id ) || empty( $subject_id ) ) {
		error_log('SMS Upload Results AJAX: Missing exam or subject');
		wp_send_json_error( __( 'Please select Exam and Subject.', 'school-management-system' ) );
	}

	// Check if file was uploaded
	if ( ! isset( $_FILES['result_file'] ) || $_FILES['result_file']['error'] !== UPLOAD_ERR_OK ) {
		error_log('SMS Upload Results AJAX: No file uploaded or upload error');
		wp_send_json_error( __( 'No file uploaded or upload error occurred.', 'school-management-system' ) );
	}

	$file = $_FILES['result_file'];
	
	// Validate file type
	$allowed_types = array(
		'application/vnd.ms-excel',
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'text/csv',
		'application/csv'
	);
	
	$allowed_extensions = array('xlsx', 'xls', 'csv');
	$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
	
	if ( ! in_array($file['type'], $allowed_types) && ! in_array($file_extension, $allowed_extensions) ) {
		error_log('SMS Upload Results AJAX: Invalid file type: ' . $file['type']);
		wp_send_json_error( __( 'Invalid file type. Please upload Excel (.xlsx, .xls) or CSV files only.', 'school-management-system' ) );
	}

	// Validate file size (5MB)
	$max_size = 5 * 1024 * 1024; // 5MB
	if ( $file['size'] > $max_size ) {
		error_log('SMS Upload Results AJAX: File too large: ' . $file['size']);
		wp_send_json_error( __( 'File size too large. Maximum file size is 5MB.', 'school-management-system' ) );
	}

	try {
		// Process the file
		$results = process_result_file($file, $exam_id, $subject_id);
		
		error_log('SMS Upload Results AJAX: Processing completed');
		
		wp_send_json_success( array(
			'message' => __( 'File processed successfully!', 'school-management-system' ),
			'total' => $results['total'],
			'successful' => $results['successful'],
			'failed' => $results['failed'],
			'duplicates' => $results['duplicates'],
			'details' => $results['details']
		) );
		
	} catch ( Exception $e ) {
		error_log('SMS Upload Results AJAX: Exception caught: ' . $e->getMessage());
		wp_send_json_error( __( 'An error occurred while processing the file: ', 'school-management-system' ) . $e->getMessage() );
	}
}

function process_result_file($file, $exam_id, $subject_id) {
	global $wpdb;
	
	$results = array(
		'total' => 0,
		'successful' => 0,
		'failed' => 0,
		'duplicates' => 0,
		'details' => array()
	);
	
	// Get exam details for grade calculation
	$exam_table = $wpdb->prefix . 'sms_exams';
	$exam = $wpdb->get_row($wpdb->prepare("SELECT total_marks, passing_marks FROM $exam_table WHERE id = %d", $exam_id));
	
	if (!$exam) {
		throw new Exception(__('Exam not found.', 'school-management-system'));
	}
	
	// Read file based on type
	$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
	$data = array();
	
	if ($file_extension === 'csv') {
		$data = read_csv_file($file['tmp_name']);
	} else {
		// For Excel files, we'll use a simple approach - convert to CSV or use PHPExcel if available
		$data = read_excel_file($file['tmp_name'], $file_extension);
	}
	
	if (empty($data)) {
		throw new Exception(__('No data found in file or invalid file format.', 'school-management-system'));
	}
	
	$results['total'] = count($data);
	
	// Process each row
	foreach ($data as $row_index => $row) {
		try {
			// Skip header row if it contains text instead of roll numbers
			if ($row_index === 0 && !is_numeric($row[0])) {
				continue;
			}
			
			$roll_number = trim($row[0] ?? '');
			$student_name = trim($row[1] ?? '');
			$obtained_marks = floatval($row[2] ?? 0);
			$remarks = trim($row[3] ?? '');
			
			if (empty($roll_number) || $obtained_marks < 0) {
				$results['failed']++;
				$results['details'][] = array(
					'status' => 'error',
					'message' => sprintf(__('Row %d: Missing roll number or invalid marks', 'school-management-system'), $row_index + 1)
				);
				continue;
			}
			
			// Find student by roll number
			$student_table = $wpdb->prefix . 'sms_students';
			$student = $wpdb->get_row($wpdb->prepare(
				"SELECT id FROM $student_table WHERE roll_number = %s LIMIT 1",
				$roll_number
			));
			
			if (!$student) {
				$results['failed']++;
				$results['details'][] = array(
					'status' => 'error',
					'message' => sprintf(__('Row %d: Student with roll number %s not found', 'school-management-system'), $row_index + 1, $roll_number)
				);
				continue;
			}
			
			$student_id = $student->id;
			
			// Check for duplicate result
			$results_table = $wpdb->prefix . 'sms_results';
			$existing = $wpdb->get_row($wpdb->prepare(
				"SELECT id FROM $results_table WHERE exam_id = %d AND subject_id = %d AND student_id = %d LIMIT 1",
				$exam_id, $subject_id, $student_id
			));
			
			if ($existing) {
				$results['duplicates']++;
				$results['details'][] = array(
					'status' => 'warning',
					'message' => sprintf(__('Row %d: Result already exists for %s', 'school-management-system'), $row_index + 1, $roll_number)
				);
				continue;
			}
			
			// Calculate percentage and grade
			$percentage = ($obtained_marks / $exam->total_marks) * 100;
			
			if ($percentage >= 90) $grade = 'A+';
			elseif ($percentage >= 80) $grade = 'A';
			elseif ($percentage >= 70) $grade = 'B';
			elseif ($percentage >= 60) $grade = 'C';
			elseif ($percentage >= 50) $grade = 'D';
			else $grade = 'F';
			
			// Insert result
			$insert_result = $wpdb->insert(
				$results_table,
				array(
					'exam_id' => $exam_id,
					'subject_id' => $subject_id,
					'student_id' => $student_id,
					'obtained_marks' => $obtained_marks,
					'percentage' => $percentage,
					'grade' => $grade,
					'status' => 'published',
					'created_at' => current_time('mysql'),
					'updated_at' => current_time('mysql')
				),
				array('%d', '%d', '%d', '%f', '%f', '%s', '%s', '%s')
			);
			
			if ($insert_result === false) {
				$results['failed']++;
				$results['details'][] = array(
					'status' => 'error',
					'message' => sprintf(__('Row %d: Database error for %s', 'school-management-system'), $row_index + 1, $roll_number)
				);
			} else {
				$results['successful']++;
				$results['details'][] = array(
					'status' => 'success',
					'message' => sprintf(__('Row %d: Successfully imported result for %s', 'school-management-system'), $row_index + 1, $roll_number)
				);
			}
			
		} catch (Exception $e) {
			$results['failed']++;
			$results['details'][] = array(
				'status' => 'error',
				'message' => sprintf(__('Row %d: %s', 'school-management-system'), $row_index + 1, $e->getMessage())
			);
		}
	}
	
	return $results;
}

function read_csv_file($file_path) {
	$data = array();
	
	if (($handle = fopen($file_path, 'r')) !== FALSE) {
		while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
			if (!empty(array_filter($row))) {
				$data[] = $row;
			}
		}
		fclose($handle);
	}
	
	return $data;
}

function read_excel_file($file_path, $extension) {
	// Simple Excel reader - for basic functionality
	// In a production environment, you might want to use PHPExcel or similar library
	
	$data = array();
	
	try {
		// Try to read as CSV first (some Excel files can be read as CSV)
		if (($handle = fopen($file_path, 'r')) !== FALSE) {
			while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
				if (!empty(array_filter($row))) {
					$data[] = $row;
				}
			}
			fclose($handle);
		}
	} catch (Exception $e) {
		throw new Exception(__('Unable to read Excel file. Please save as CSV format.', 'school-management-system'));
	}
	
	return $data;
}

function sms_ajax_generate_voucher() {
	// Enable error reporting for debugging
	error_reporting(E_ALL);
	ini_set('display_errors', 0); // Don't display errors, but log them
	
	// Log the start of the process
	error_log('Voucher Generation Started: ' . date('Y-m-d H:i:s'));
	error_log('POST Data: ' . print_r($_POST, true));
	
	try {
		check_ajax_referer( 'sms_generate_voucher_nonce', 'nonce' );
		error_log('Nonce verification passed');

		if ( ! current_user_can( 'manage_options' ) ) {
			error_log('Permission denied - user lacks manage_options capability');
			wp_send_json_error( __( 'Unauthorized', 'school-management-system' ) );
		}

		$fee_id = intval( $_POST['fee_id'] ?? 0 );
		error_log('Fee ID: ' . $fee_id);

		if ( empty( $fee_id ) ) {
			error_log('Empty fee ID provided');
			wp_send_json_error( __( 'Missing fee ID', 'school-management-system' ) );
		}

		// Get fee details
		error_log('Attempting to get fee details...');
		$fee = Fee::get( $fee_id );
		if ( ! $fee ) {
			error_log('Fee not found for ID: ' . $fee_id);
			wp_send_json_error( __( 'Fee record not found', 'school-management-system' ) );
		}
		error_log('Fee details retrieved successfully');

		// Get student and class details
		error_log('Getting student details...');
		$student = Student::get( $fee->student_id );
		$class = Classm::get( $fee->class_id );

		if ( ! $student || ! $class ) {
			error_log('Student or class information not found');
			wp_send_json_error( __( 'Student or class information not found', 'school-management-system' ) );
		}
		error_log('Student and class details retrieved successfully');

		// Generate voucher HTML
		error_log('Generating voucher HTML...');
		$voucher_html = generate_voucher_html( $fee, $student, $class );
		error_log('Voucher HTML generated successfully');

		// Create temporary file
		$upload_dir = wp_upload_dir();
		$vouchers_dir = $upload_dir['basedir'] . '/school-vouchers/';
		error_log('Vouchers directory: ' . $vouchers_dir);
		
		if ( ! file_exists( $vouchers_dir ) ) {
			error_log('Creating vouchers directory...');
			if ( ! wp_mkdir_p( $vouchers_dir ) ) {
				error_log('Failed to create vouchers directory');
				wp_send_json_error( __( 'Failed to create vouchers directory', 'school-management-system' ) );
			}
			error_log('Vouchers directory created successfully');
		}

		// Check if directory is writable
		if ( ! is_writable( $vouchers_dir ) ) {
			error_log('Vouchers directory is not writable');
			wp_send_json_error( __( 'Vouchers directory is not writable', 'school-management-system' ) );
		}
		error_log('Vouchers directory is writable');

		$filename = 'voucher_' . $fee->id . '_' . time() . '.html'; // Changed to .html by default
		$filepath = $vouchers_dir . $filename;
		error_log('File path: ' . $filepath);

		// Generate HTML voucher (simplified approach)
		error_log('Creating HTML voucher...');
		$html_result = create_simple_html_voucher( $voucher_html, $filepath );

		if ( $html_result && file_exists( $filepath ) ) {
			error_log('HTML voucher created successfully');
			$html_url = $upload_dir['baseurl'] . '/school-vouchers/' . $filename;
			
			wp_send_json_success( array(
				'url' => $html_url,
				'filename' => 'Payment_Voucher_' . $student->roll_number . '_' . date( 'Y-m-d' ) . '.html',
				'type' => 'html',
				'message' => __( 'Voucher downloaded as HTML file. Press Ctrl+P (Windows/Linux) or Cmd+P (Mac) to save as PDF.', 'school-management-system' )
			) );
		} else {
			error_log('Failed to create HTML voucher');
			wp_send_json_error( __( 'Failed to generate voucher file', 'school-management-system' ) );
		}
		
	} catch ( Exception $e ) {
		error_log( 'Voucher Generation Exception: ' . $e->getMessage() );
		error_log( 'Exception Trace: ' . $e->getTraceAsString() );
		wp_send_json_error( __( 'An error occurred while generating the voucher: ', 'school-management-system' ) . $e->getMessage() );
	} catch ( Error $e ) {
		error_log( 'Voucher Generation Fatal Error: ' . $e->getMessage() );
		error_log( 'Fatal Error Trace: ' . $e->getTraceAsString() );
		wp_send_json_error( __( 'A fatal error occurred while generating the voucher: ', 'school-management-system' ) . $e->getMessage() );
	}
}

/**
 * Create a simple HTML voucher (fallback method).
 */
function create_simple_html_voucher( $voucher_html, $filepath ) {
	try {
		error_log('Creating simple HTML voucher...');

		$result = file_put_contents( $filepath, $voucher_html );
		error_log('File write result: ' . ($result ? 'success' : 'failed'));
		
		return $result !== false;
		
	} catch ( Exception $e ) {
		error_log( 'Simple HTML Voucher Error: ' . $e->getMessage() );
		return false;
	}
}

/**
 * Generate voucher HTML content.
 */
function generate_voucher_html( $fee, $student, $class ) {
	$settings = get_option( 'sms_settings' );
	$school_name = $settings['school_name'] ?? 'School Management System';
	$school_logo = $settings['school_logo'] ?? '';
	$school_address = $settings['school_address'] ?? '';
	$school_phone = $settings['school_phone'] ?? '';
	$currency = $settings['currency'] ?? '৳';

	$due_amount = $fee->amount - $fee->paid_amount;
	
	ob_start();
	?>
	<!DOCTYPE html>
	<html>
	<head>
		<meta charset="utf-8">
		<title>Payment Voucher</title>
		<style>
			@page { 
				size: A4; 
				margin: 10mm; 
			}
			body { 
				font-family: 'Georgia', serif; 
				margin: 0; 
				padding: 0; 
				background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
				font-size: 12px;
			}
			.voucher-container { 
				max-width: 100%; 
				margin: 0 auto; 
				background: white; 
				border-radius: 10px; 
				overflow: hidden;
				box-shadow: 0 10px 20px rgba(0,0,0,0.1);
				position: relative;
			}
			.voucher-header { 
				background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
				color: white; 
				padding: 20px 15px; 
				text-align: center; 
				position: relative;
				overflow: hidden;
			}
			.voucher-header .header-inner {
				display: flex;
				align-items: center;
				justify-content: center;
				gap: 12px;
			}
			.voucher-logo {
				max-height: 36px;
				max-width: 36px;
				border-radius: 6px;
				background: rgba(255,255,255,0.18);
				padding: 4px;
			}
			.voucher-header::before {
				content: '';
				position: absolute;
				top: -50%;
				right: -50%;
				width: 200%;
				height: 200%;
				background: repeating-linear-gradient(
					45deg,
					transparent,
					transparent 10px,
					rgba(255,255,255,0.05) 10px,
					rgba(255,255,255,0.05) 20px
				);
				animation: slide 20s linear infinite;
			}
			@keyframes slide {
				0% { transform: translate(0, 0); }
				100% { transform: translate(50px, 50px); }
			}
			.voucher-header h1 { 
				margin: 0; 
				font-size: 24px; 
				font-weight: 700; 
				text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
				position: relative;
				z-index: 2;
			}
			.voucher-header p { 
				margin: 5px 0 0 0; 
				opacity: 0.95; 
				font-size: 12px;
				position: relative;
				z-index: 2;
			}
			.voucher-body { 
				padding: 20px 15px; 
				background: white;
				position: relative;
			}
			.voucher-number { 
				background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 50%, #fecfef 100%);
				color: #d63384; 
				padding: 8px 15px; 
				border-radius: 20px; 
				font-weight: 700; 
				display: inline-block; 
				margin-bottom: 15px;
				font-size: 12px;
				box-shadow: 0 2px 8px rgba(214, 51, 132, 0.2);
				border: 1px solid #fff;
			}
			.voucher-info { 
				display: grid; 
				grid-template-columns: 1fr 1fr; 
				gap: 20px; 
				margin-bottom: 20px;
			}
			.info-section { 
				background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
				padding: 15px; 
				border-radius: 8px;
				border-left: 3px solid #667eea;
				box-shadow: 0 2px 8px rgba(0,0,0,0.05);
			}
			.info-section h3 { 
				margin: 0 0 10px 0; 
				color: #2c3e50; 
				font-size: 14px;
				border-bottom: 1px solid #667eea; 
				padding-bottom: 5px;
				position: relative;
			}
			.info-section h3::after {
				content: '';
				position: absolute;
				bottom: -1px;
				left: 0;
				width: 30px;
				height: 1px;
				background: #764ba2;
			}
			.info-row { 
				display: flex; 
				margin-bottom: 8px;
				align-items: center;
			}
			.info-label { 
				font-weight: 600; 
				color: #495057; 
				min-width: 100px;
				font-size: 11px;
			}
			.info-value { 
				color: #212529; 
				font-weight: 500;
				font-size: 11px;
			}
			.payment-details { 
				background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
				padding: 15px; 
				border-radius: 8px; 
				margin-bottom: 15px;
				border: 1px solid #e9ecef;
				position: relative;
			}
			.payment-details::before {
				content: '💰';
				position: absolute;
				top: -10px;
				left: 15px;
				background: white;
				padding: 2px 8px;
				border-radius: 10px;
				font-size: 14px;
			}
			.payment-details h3 { 
				margin: 0 0 10px 0; 
				color: #2c3e50;
				text-align: center;
				font-size: 14px;
			}
			.payment-row { 
				display: flex; 
				justify-content: space-between; 
				margin-bottom: 8px; 
				padding: 6px 10px;
				background: rgba(102, 126, 234, 0.05);
				border-radius: 5px;
				font-size: 11px;
			}
			.payment-row.total { 
				background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
				color: white;
				font-weight: 700; 
				font-size: 12px;
				margin-top: 10px;
				box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
			}
			.voucher-footer { 
				text-align: center; 
				padding: 15px; 
				background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
				color: white;
				position: relative;
			}
			.voucher-footer p {
				margin: 2px 0;
				font-size: 10px;
			}
			.watermark { 
				position: absolute; 
				top: 50%; 
				left: 50%; 
				transform: translate(-50%, -50%) rotate(-45deg); 
				font-size: 100px; 
				font-weight: 700; 
				z-index: 1;
				pointer-events: none;
			}
			.watermark.paid { color: rgba(40, 167, 69, 0.08); }
			.watermark.partially-paid { color: rgba(255, 193, 7, 0.08); }
			.watermark.pending { color: rgba(108, 117, 125, 0.08); }
			.status-paid { 
				color: #28a745; 
				font-weight: 700; 
				background: rgba(40, 167, 69, 0.1);
				padding: 2px 8px;
				border-radius: 10px;
				border: 1px solid #28a745;
				font-size: 10px;
			}
			.status-partial { 
				color: #ffc107; 
				font-weight: 700;
				background: rgba(255, 193, 7, 0.1);
				padding: 2px 8px;
				border-radius: 10px;
				border: 1px solid #ffc107;
				font-size: 10px;
			}
			.signature-section { 
				margin-top: 20px; 
				padding: 15px; 
				background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
				border-radius: 8px;
				border-top: 2px solid #667eea;
			}
			.signature-row { 
				display: flex; 
				justify-content: space-between; 
				gap: 30px; 
			}
			.signature-box { 
				flex: 1; 
				text-align: center; 
				position: relative;
			}
			.signature-line { 
				border-bottom: 1px solid #495057; 
				height: 30px; 
				margin-bottom: 8px;
				position: relative;
			}
			.signature-line::before {
				content: '';
				position: absolute;
				top: 50%;
				left: 50%;
				transform: translate(-50%, -50%);
				width: 20px;
				height: 20px;
				border: 2px dashed #ccc;
				border-radius: 50%;
				opacity: 0.3;
			}
			.signature-label { 
				margin: 0; 
				font-size: 10px; 
				color: #495057; 
				font-weight: 600;
				text-transform: uppercase;
				letter-spacing: 0.5px;
			}
			.remarks-section {
				background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
				padding: 10px;
				border-radius: 8px;
				margin-bottom: 15px;
				border-left: 3px solid #ffc107;
			}
			.remarks-section h3 {
				margin: 0 0 5px 0;
				color: #856404;
				font-size: 12px;
			}
			.remarks-section p {
				margin: 0;
				font-size: 10px;
			}
			@media print {
				body { background: white; font-size: 10px; }
				.voucher-container { box-shadow: none; margin: 0; }
				.voucher-header::before { display: none; }
				.voucher-header { padding: 15px 10px; }
				.voucher-header h1 { font-size: 20px; }
				.voucher-body { padding: 15px 10px; }
				.voucher-info { gap: 15px; }
				.info-section { padding: 10px; }
				.payment-details { padding: 10px; }
				.signature-section { padding: 10px; margin-top: 15px; }
				.signature-line { height: 25px; }
				.voucher-footer { padding: 10px; }
			}
		</style>
	</head>
	<body>
		<?php
		$watermark_text = 'paid' === $fee->status ? 'PAID' : ( 'partially_paid' === $fee->status ? 'PARTIALLY PAID' : 'PENDING' );
		$watermark_class = 'paid' === $fee->status ? 'paid' : ( 'partially_paid' === $fee->status ? 'partially-paid' : 'pending' );
		?>
		<div class="voucher-container">
			<div class="watermark <?php echo esc_attr( $watermark_class ); ?>"><?php echo esc_html( $watermark_text ); ?></div>
			<div class="voucher-header">
				<div class="header-inner">
					<?php if ( ! empty( $school_logo ) ) : ?>
						<img class="voucher-logo" src="<?php echo esc_url( $school_logo ); ?>" alt="<?php echo esc_attr( $school_name ); ?>">
					<?php endif; ?>
					<h1><?php echo esc_html( $school_name ); ?></h1>
				</div>
			</div>
			
			<div class="voucher-body">
				<div class="voucher-number">
					<?php printf( esc_html__( 'Voucher No: %s', 'school-management-system' ), 'FEE-' . str_pad( $fee->id, 6, '0', STR_PAD_LEFT ) ); ?>
				</div>

				<div class="voucher-info">
					<div class="info-section">
						<h3><?php esc_html_e( 'Student Information', 'school-management-system' ); ?></h3>
						<div class="info-row">
							<span class="info-label"><?php esc_html_e( 'Name:', 'school-management-system' ); ?></span>
							<span class="info-value"><?php echo esc_html( $student->first_name . ' ' . $student->last_name ); ?></span>
						</div>
						<div class="info-row">
							<span class="info-label"><?php esc_html_e( 'Roll Number:', 'school-management-system' ); ?></span>
							<span class="info-value"><?php echo esc_html( $student->roll_number ); ?></span>
						</div>
						<div class="info-row">
							<span class="info-label"><?php esc_html_e( 'Class:', 'school-management-system' ); ?></span>
							<span class="info-value"><?php echo esc_html( $class->class_name ); ?></span>
						</div>
					</div>

					<div class="info-section">
						<h3><?php esc_html_e( 'Payment Information', 'school-management-system' ); ?></h3>
						<div class="info-row">
							<span class="info-label"><?php esc_html_e( 'Fee Type:', 'school-management-system' ); ?></span>
							<span class="info-value"><?php echo esc_html( $fee->fee_type ); ?></span>
						</div>
						<?php if ( 'Admission Fee' !== $fee->fee_type ) : ?>
						<div class="info-row">
							<span class="info-label"><?php esc_html_e( 'Due Date:', 'school-management-system' ); ?></span>
							<span class="info-value"><?php echo esc_html( $fee->due_date ); ?></span>
						</div>
						<?php endif; ?>
						<div class="info-row">
							<span class="info-label"><?php esc_html_e( 'Payment Date:', 'school-management-system' ); ?></span>
							<span class="info-value"><?php echo esc_html( $fee->payment_date ); ?></span>
						</div>
						<div class="info-row">
							<span class="info-label"><?php esc_html_e( 'Status:', 'school-management-system' ); ?></span>
							<span class="info-value <?php echo 'paid' === $fee->status ? 'status-paid' : 'status-partial'; ?>">
								<?php echo esc_html( ucfirst( str_replace( '_', ' ', $fee->status ) ) ); ?>
							</span>
						</div>
					</div>
				</div>

				<div class="payment-details">
					<h3><?php esc_html_e( 'Payment Breakdown', 'school-management-system' ); ?></h3>
					<div class="payment-row">
						<span><?php esc_html_e( 'Total Amount:', 'school-management-system' ); ?></span>
						<span><?php echo esc_html( $currency . ' ' . number_format( $fee->amount, 2 ) ); ?></span>
					</div>
					<div class="payment-row">
						<span><?php esc_html_e( 'Amount Paid:', 'school-management-system' ); ?></span>
						<span><?php echo esc_html( $currency . ' ' . number_format( $fee->paid_amount, 2 ) ); ?></span>
					</div>
					<?php if ( $due_amount > 0 ) : ?>
					<div class="payment-row">
						<span><?php esc_html_e( 'Due Amount:', 'school-management-system' ); ?></span>
						<span><?php echo esc_html( $currency . ' ' . number_format( $due_amount, 2 ) ); ?></span>
					</div>
					<?php endif; ?>
					<div class="payment-row total">
						<span><?php esc_html_e( 'Total Received:', 'school-management-system' ); ?></span>
						<span><?php echo esc_html( $currency . ' ' . number_format( $fee->paid_amount, 2 ) ); ?></span>
					</div>
				</div>

				<?php if ( ! empty( $fee->remarks ) ) : ?>
				<div class="remarks-section">
					<h3>📝 <?php esc_html_e( 'Remarks', 'school-management-system' ); ?></h3>
					<p><?php echo esc_html( $fee->remarks ); ?></p>
				</div>
				<?php endif; ?>
			</div>

			<div class="voucher-footer">
				<p><strong><?php echo esc_html( $school_name ); ?></strong></p>
				<?php if ( ! empty( $school_address ) ) : ?>
					<p><?php echo esc_html( $school_address ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $school_phone ) ) : ?>
					<p><?php esc_html_e( 'Phone:', 'school-management-system' ); ?> <?php echo esc_html( $school_phone ); ?></p>
				<?php endif; ?>
				<p><small><?php printf( esc_html__( 'Generated on: %s', 'school-management-system' ), date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ); ?></small></p>
			</div>
			
			<div class="signature-section">
				<div class="signature-row">
					<div class="signature-box">
						<div class="signature-line"></div>
						<p class="signature-label"><?php esc_html_e( 'Authorized sign', 'school-management-system' ); ?></p>
					</div>
					<div class="signature-box">
						<div class="signature-line"></div>
						<p class="signature-label"><?php esc_html_e( 'Student sign', 'school-management-system' ); ?></p>
					</div>
				</div>
			</div>
		</div>
	</body>
	</html>
	<?php
	return ob_get_clean();
}

/**
 * Create voucher PDF file.
 */
function create_voucher_pdf( $html, $filepath ) {
	try {
		// For now, let's create a reliable HTML file instead of trying to generate PDF
		// This avoids the complex PDF generation issues
		$html_filepath = str_replace( '.pdf', '.html', $filepath );
		
		// Create a clean, print-friendly HTML file
		$print_html = create_printable_voucher_html( $html );
		
		// Save the HTML file
		$result = file_put_contents( $html_filepath, $print_html );
		
		if ( $result ) {
			// Try to create a simple PDF if possible, but don't fail if we can't
			$pdf_result = create_simple_pdf_fallback( $print_html, $filepath );
			
			// Return true if either HTML was created successfully
			return true;
		}
		
		return false;
		
	} catch ( Exception $e ) {
		error_log( 'Voucher PDF Creation Error: ' . $e->getMessage() );
		// Fallback: try to save as HTML
		$html_filepath = str_replace( '.pdf', '.html', $filepath );
		return file_put_contents( $html_filepath, $html ) !== false;
	}
}

/**
 * Create a printable HTML voucher.
 */
function create_printable_voucher_html( $html ) {
	// Extract the body content from the original HTML
	$dom = new DOMDocument();
	@$dom->loadHTML($html);
	$body = $dom->getElementsByTagName('body')->item(0);
	$body_content = $dom->saveHTML($body);
	
	// Create clean HTML for printing
	$body_content = preg_replace('/<body[^>]*>/', '', $body_content);
	$body_content = preg_replace('/<\/body>/', '', $body_content);
	
	// Build print-friendly HTML with simplified CSS
	$print_html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Voucher</title>
    <style>
        @page { 
            size: A4; 
            margin: 15mm; 
        }
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: white; 
            color: black;
        }
        .voucher-container { 
            max-width: 100%; 
            margin: 0 auto; 
            background: white; 
            border: 2px solid #333; 
            padding: 20px; 
            box-sizing: border-box;
        }
        .voucher-header { 
            text-align: center; 
            border-bottom: 3px double #333; 
            padding-bottom: 20px; 
            margin-bottom: 20px; 
        }
        .voucher-header h1 { 
            margin: 0; 
            font-size: 24px; 
            font-weight: bold; 
            color: #333; 
        }
        .voucher-header p { 
            margin: 5px 0 0 0; 
            font-size: 14px; 
            color: #666; 
        }
        .voucher-number { 
            background: #f0f0f0; 
            padding: 10px; 
            text-align: center; 
            font-weight: bold; 
            margin-bottom: 20px; 
            border: 1px solid #ccc; 
            font-size: 14px;
        }
        .voucher-info { 
            display: table; 
            width: 100%; 
            margin-bottom: 20px; 
            border-collapse: collapse;
        }
        .info-row { 
            display: table-row; 
        }
        .info-label, .info-value { 
            display: table-cell; 
            padding: 8px 5px; 
            border: 1px solid #ddd; 
            font-size: 12px;
            vertical-align: top;
        }
        .info-label { 
            font-weight: bold; 
            width: 30%; 
            background: #f9f9f9; 
        }
        .info-value { 
            width: 70%; 
        }
        .payment-details { 
            border: 2px solid #333; 
            padding: 15px; 
            margin-bottom: 20px; 
        }
        .payment-details h3 { 
            margin: 0 0 10px 0; 
            font-size: 16px; 
            font-weight: bold; 
            text-align: center; 
        }
        .payment-row { 
            display: flex; 
            justify-content: space-between; 
            margin-bottom: 5px; 
            padding: 5px 0; 
            font-size: 12px; 
            border-bottom: 1px solid #eee;
        }
        .payment-row:last-child {
            border-bottom: none;
        }
        .payment-row.total { 
            border-top: 2px solid #333; 
            font-weight: bold; 
            font-size: 14px; 
            padding-top: 10px; 
            margin-bottom: 0;
        }
        .voucher-footer { 
            text-align: center; 
            border-top: 1px solid #ccc; 
            padding-top: 15px; 
            margin-top: 20px; 
            font-size: 11px; 
            color: #666; 
        }
        .watermark { 
            position: fixed; 
            top: 50%; 
            left: 50%; 
            transform: translate(-50%, -50%) rotate(-45deg); 
            font-size: 120px; 
            color: rgba(0,0,0,0.1); 
            font-weight: bold; 
            z-index: -1; 
        }
        .status-paid { color: #006600; font-weight: bold; }
        .status-partial { color: #cc6600; font-weight: bold; }
        .print-instructions {
            background: #f0f8ff;
            border: 1px solid #b0d4f1;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
            text-align: center;
        }
        @media print {
            body { margin: 0; }
            .voucher-container { border: none; }
            .print-instructions { display: none; }
        }
    </style>
</head>
<body>
    <div class="watermark">PAID</div>
    <div class="voucher-container">
        ' . $body_content . '
        <div class="print-instructions">
            <h3>' . __( 'Print Instructions', 'school-management-system' ) . '</h3>
            <p>' . __( 'Press Ctrl+P (Windows/Linux) or Cmd+P (Mac) to print this voucher as PDF', 'school-management-system' ) . '</p>
        </div>
    </div>
</body>
</html>';

	return $print_html;
}

/**
 * Create a simple PDF fallback (minimal implementation).
 */
function create_simple_pdf_fallback( $html, $filepath ) {
	try {
		// For now, just return false to indicate we couldn't create PDF
		// The HTML file will be used instead
		return false;
	} catch ( Exception $e ) {
		return false;
	}
}

// Register AJAX hooks.
add_action( 'wp_ajax_sms_submit_attendance', __NAMESPACE__ . '\sms_ajax_submit_attendance' );
add_action( 'wp_ajax_nopriv_sms_submit_attendance', __NAMESPACE__ . '\sms_ajax_submit_attendance' );

add_action( 'wp_ajax_sms_enroll_student', __NAMESPACE__ . '\sms_ajax_enroll_student' );
add_action( 'wp_ajax_nopriv_sms_enroll_student', __NAMESPACE__ . '\sms_ajax_enroll_student' );

add_action( 'wp_ajax_sms_search_data', __NAMESPACE__ . '\sms_ajax_search_data' );
add_action( 'wp_ajax_nopriv_sms_search_data', __NAMESPACE__ . '\sms_ajax_search_data' );

add_action( 'wp_ajax_sms_generate_voucher', __NAMESPACE__ . '\sms_ajax_generate_voucher' );
add_action( 'wp_ajax_nopriv_sms_generate_voucher', __NAMESPACE__ . '\sms_ajax_generate_voucher' );

// Add Result hooks
add_action( 'wp_ajax_sms_add_result', __NAMESPACE__ . '\sms_ajax_add_result' );
add_action( 'wp_ajax_nopriv_sms_add_result', __NAMESPACE__ . '\sms_ajax_add_result' );

// Add a test endpoint for debugging
add_action( 'wp_ajax_sms_test_voucher', __NAMESPACE__ . '\sms_ajax_test_voucher' );
function sms_ajax_test_voucher() {
	try {
		// Test basic functionality
		$test_data = array(
			'timestamp' => current_time( 'mysql' ),
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'upload_dir' => wp_upload_dir(),
			'php_version' => PHP_VERSION,
			'wp_version' => get_bloginfo( 'version' ),
			'memory_limit' => ini_get( 'memory_limit' ),
			'max_execution_time' => ini_get( 'max_execution_time' ),
			'fileinfo' => extension_loaded( 'fileinfo' ),
			'vouchers_dir' => wp_upload_dir()['basedir'] . '/school-vouchers/',
			'vouchers_dir_exists' => file_exists( wp_upload_dir()['basedir'] . '/school-vouchers/' ),
			'vouchers_dir_writable' => is_writable( wp_upload_dir()['basedir'] . '/school-vouchers/' ) ?: 'Directory not found'
		);
		
		wp_send_json_success( $test_data );
	} catch ( Exception $e ) {
		wp_send_json_error( 'Test failed: ' . $e->getMessage() );
	}
}
