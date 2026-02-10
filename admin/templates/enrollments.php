<?php
/**
 * Enrollments admin template.
 *
 * @package School_Management_System
 */

use School_Management_System\Enrollment;
use School_Management_System\Student;
use School_Management_System\Classm;
use School_Management_System\Fee;

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'Unauthorized', 'school-management-system' ) );
}

$active_tab = sanitize_text_field( $_GET['tab'] ?? 'enroll_existing' );
$message_class = 'notice-success';
$error_message = '';
$message = '';
if ( isset( $_GET['sms_message'] ) ) {
	$sms_message = sanitize_text_field( $_GET['sms_message'] );
	if ( 'enrollment_added' === $sms_message ) {
		$message = __( 'Enrollment added successfully.', 'school-management-system' );
	} elseif ( 'enrollment_deleted' === $sms_message ) {
		$message = __( 'Enrollment deleted successfully.', 'school-management-system' );
	} elseif ( 'enrollments_bulk_deleted' === $sms_message ) {
		$count = intval( $_GET['count'] ?? 0 );
		$message = sprintf( __( '%d enrollments deleted successfully.', 'school-management-system' ), $count );
	} elseif ( 'student_created_and_enrolled' === $sms_message ) {
		$message = __( 'New student created and enrolled successfully.', 'school-management-system' );
	} elseif ( 'student_updated_and_enrolled' === $sms_message ) {
		$message = __( 'Existing student details updated and enrolled successfully.', 'school-management-system' );
	} elseif ( 'student_already_enrolled' === $sms_message ) {
		$message = __( 'Student is already enrolled in this class.', 'school-management-system' );
		$message_class = 'notice-warning';
	}
}
if ( isset( $_GET['sms_error'] ) ) {
	$error_message = sanitize_text_field( urldecode( $_GET['sms_error'] ) );
}

$total_enrollments = Enrollment::count();
$active_enrollments = Enrollment::count( array( 'status' => 'active' ) );
$pending_enrollments = Enrollment::count( array( 'status' => 'pending' ) );
$inactive_enrollments = Enrollment::count( array( 'status' => 'inactive' ) );
$students_count = Student::count();

// Handle Edit Mode
$enrollment_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
$edit_enrollment = null;
$edit_student = null;
$edit_fee = null;

if ( 'edit' === $action && $enrollment_id ) {
	$edit_enrollment = Enrollment::get( $enrollment_id );
	if ( $edit_enrollment ) {
		$edit_student = Student::get( $edit_enrollment->student_id );
		// Try to find associated admission fee
		$fees = Fee::get_student_fees( $edit_enrollment->student_id );
		if ( ! empty( $fees ) ) {
			foreach ( $fees as $f ) {
				if ( 'Admission Fee' === $f->fee_type && $f->class_id == $edit_enrollment->class_id ) {
					$edit_fee = $f;
					break;
				}
			}
		}
	}
}

?>
<style>
 .sms-enrollments-page { max-width: 100%; }
 .sms-enrollments-header {
 	display: flex;
 	justify-content: space-between;
 	align-items: flex-start;
 	gap: 16px;
 	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
 	color: #fff;
 	padding: 22px;
 	border-radius: 16px;
 	box-shadow: 0 10px 30px rgba(102, 126, 234, 0.22);
 	margin: 10px 0 18px;
 }
 .sms-enrollments-title h1 { margin: 0; color: #fff; font-size: 22px; line-height: 1.2; }
 .sms-enrollments-subtitle { margin: 6px 0 0; opacity: 0.92; font-size: 13px; }
 .sms-enrollments-header-actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }
 .sms-cta-btn {
 	background: rgba(255,255,255,0.16);
 	border: 1px solid rgba(255,255,255,0.26);
 	color: #fff;
 	padding: 10px 14px;
 	border-radius: 10px;
 	font-weight: 700;
 	text-decoration: none;
 	display: inline-flex;
 	align-items: center;
 	gap: 8px;
 	cursor: pointer;
 }
 .sms-cta-btn:hover { background: rgba(255,255,255,0.24); color: #fff; }

 .sms-enrollment-stats {
 	display: grid;
 	grid-template-columns: repeat(5, minmax(0, 1fr));
 	gap: 16px;
 	margin-bottom: 18px;
 }
 .sms-enrollment-stat {
 	background: #fff;
 	border-radius: 16px;
 	padding: 18px;
 	box-shadow: 0 8px 22px rgba(0,0,0,0.08);
 	border: 1px solid #eef1f5;
 	display: flex;
 	align-items: center;
 	gap: 14px;
 }
 .sms-enrollment-stat-icon {
 	width: 44px;
 	height: 44px;
 	border-radius: 12px;
 	display: flex;
 	align-items: center;
 	justify-content: center;
 	color: #fff;
 }
 .sms-enrollment-stat-icon.total { background: linear-gradient(135deg, #667eea 0%, #a8b8f8 100%); }
 .sms-enrollment-stat-icon.active { background: linear-gradient(135deg, #00d2d3 0%, #54a0ff 100%); }
 .sms-enrollment-stat-icon.pending { background: linear-gradient(135deg, #feca57 0%, #ff9ff3 100%); }
 .sms-enrollment-stat-icon.students { background: linear-gradient(135deg, #48dbfb 0%, #0abde3 100%); }
 .sms-enrollment-stat-icon .dashicons { font-size: 20px; width: 20px; height: 20px; }
 .sms-enrollment-stat-number { font-size: 20px; font-weight: 800; color: #2c3e50; line-height: 1.1; }
 .sms-enrollment-stat-label { font-size: 12px; color: #6c757d; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; }

 .sms-panel {
 	background: #fff;
 	border: 1px solid #e9ecef;
 	border-radius: 16px;
 	box-shadow: 0 8px 22px rgba(0,0,0,0.06);
 	overflow: hidden;
 	margin-bottom: 18px;
 }
 .sms-panel-header {
 	background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
 	color: #fff;
 	padding: 14px 18px;
 	display: flex;
 	justify-content: space-between;
 	align-items: center;
 	gap: 12px;
 }
 .sms-panel-header h2 { margin: 0; font-size: 15px; font-weight: 800; color: #fff; }
 .sms-panel-body { padding: 18px; }

 .sms-enrollments-search {
 	display: flex;
 	gap: 10px;
 	flex-wrap: wrap;
 	justify-content: flex-end;
 	margin: 0;
 }
 .sms-enrollments-search input[type="search"] { min-width: 260px; padding: 10px 12px; border: 1px solid #dee2e6; border-radius: 10px; }
 .sms-search-btn {
 	background: #2c3e50;
 	color: #fff;
 	border: none;
 	padding: 10px 20px;
 	border-radius: 8px;
 	font-weight: 600;
 	cursor: pointer;
 	transition: all 0.2s;
 	box-shadow: 0 2px 5px rgba(0,0,0,0.1);
 }
 .sms-search-btn:hover {
 	background: #1a252f;
 	transform: translateY(-1px);
 	box-shadow: 0 4px 8px rgba(0,0,0,0.15);
 }
 .sms-reset-btn {
 	background: #fff;
 	color: #e74c3c;
 	border: 1px solid #e74c3c;
 	padding: 9px 19px;
 	border-radius: 8px;
 	font-weight: 600;
 	text-decoration: none;
 	transition: all 0.2s;
 }
 .sms-reset-btn:hover {
 	background: #e74c3c;
 	color: #fff;
 }
 .sms-enrollments-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }

 .sms-status-pill {
 	display: inline-flex;
 	align-items: center;
 	gap: 6px;
 	padding: 6px 10px;
 	border-radius: 999px;
 	font-size: 11px;
 	font-weight: 800;
 	text-transform: uppercase;
 	letter-spacing: 0.4px;
 	border: 1px solid;
 }
 .sms-status-pill.active { background: rgba(40, 167, 69, 0.12); color: #155724; border-color: rgba(40, 167, 69, 0.28); }
 .sms-status-pill.pending { background: rgba(255, 193, 7, 0.12); color: #856404; border-color: rgba(255, 193, 7, 0.28); }
 .sms-status-pill.inactive { background: rgba(220, 53, 69, 0.12); color: #721c24; border-color: rgba(220, 53, 69, 0.28); }

 .sms-row-actions { display: inline-flex; gap: 8px; flex-wrap: wrap; }
 .sms-row-action-btn {
 	background: #fff;
 	border: 1px solid #dee2e6;
 	border-radius: 10px;
 	padding: 7px 10px;
 	text-decoration: none;
 	font-weight: 700;
 	font-size: 12px;
 	color: #2c3e50;
 	display: inline-flex;
 	align-items: center;
 	gap: 6px;
 }
 .sms-row-action-btn:hover { box-shadow: 0 6px 16px rgba(0,0,0,0.08); transform: translateY(-1px); }
 .sms-row-action-btn.delete { border-color: rgba(220, 53, 69, 0.35); color: #dc3545; }

 .sms-form-grid {
 	display: grid;
 	grid-template-columns: repeat(2, 1fr);
 	gap: 20px;
 }
 .sms-form-field { display: flex; flex-direction: column; }
 .sms-form-label {
 	font-weight: 700;
 	color: #2c3e50;
 	margin-bottom: 8px;
 	font-size: 14px;
 }
 .sms-form-control input,
 .sms-form-control select {
 	padding: 12px;
 	border: 1px solid #dee2e6;
 	border-radius: 10px;
 	font-size: 14px;
 	transition: all 0.3s ease;
 }
 .sms-form-control input:focus,
 .sms-form-control select:focus {
 	outline: none;
 	border-color: #667eea;
 	box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
 }
 .sms-form-actions {
 	display: flex;
 	gap: 12px;
 	margin-top: 24px;
 }
 .sms-btn {
 	padding: 12px 24px;
 	border-radius: 10px;
 	font-weight: 700;
 	border: none;
 	cursor: pointer;
 	transition: all 0.3s ease;
 }
 .sms-btn-primary {
 	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
 	color: #fff;
 }
 .sms-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3); }
 .sms-btn-secondary {
 	background: #6c757d;
 	color: #fff;
 }
 .sms-btn-secondary:hover { background: #5a6268; }

 @media (max-width: 782px) {
 	.sms-enrollments-header { flex-direction: column; align-items: flex-start; }
 	.sms-enrollments-header-actions { width: 100%; justify-content: flex-start; }
 	.sms-enrollment-stats { grid-template-columns: 1fr; }
 	.sms-enrollments-search { justify-content: flex-start; }
 	.sms-enrollments-search input[type="search"] { min-width: 0; width: 100%; }
 	.sms-form-grid { grid-template-columns: 1fr; }
 }
</style>
<div class="wrap">
	<div class="sms-enrollments-page">
		<div class="sms-enrollments-header">
			<div class="sms-enrollments-title">
				<h1><?php esc_html_e( 'Enrollments', 'school-management-system' ); ?></h1>
				<div class="sms-enrollments-subtitle"><?php esc_html_e( 'Enroll new students and manage class assignments. Students are automatically added to the Students list.', 'school-management-system' ); ?></div>
			</div>
			<div class="sms-enrollments-header-actions">
				<a class="sms-cta-btn" href="#sms-enrollment-form">
					<span class="dashicons dashicons-plus-alt"></span>
					<?php esc_html_e( 'Enroll New Student', 'school-management-system' ); ?>
				</a>
			</div>
		</div>

	<?php if ( ! empty( $message ) ) : ?>
		<div class="notice <?php echo esc_attr( $message_class ); ?> is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
	<?php endif; ?>
	<?php if ( ! empty( $error_message ) ) : ?>
		<div class="notice notice-error is-dismissible"><p><?php echo esc_html( $error_message ); ?></p></div>
	<?php endif; ?>

		<!-- Admission Fee Dashboard -->
		<div class="sms-dashboard-wrapper" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; margin-bottom: 30px;">
			<?php
			$currency = get_option( 'sms_settings' )['currency'] ?? '৳';
			?>
			<div class="sms-stat-card" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); border-radius: 12px; padding: 25px; color: #fff; position: relative; overflow: hidden;">
				<span class="dashicons dashicons-money-alt" style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); font-size: 80px; width: 80px; height: 80px; opacity: 0.15;"></span>
				<h3 style="margin: 0 0 10px; font-size: 15px; font-weight: 600; text-transform: uppercase; color: rgba(255,255,255,0.9);"><?php esc_html_e( 'Admission Fees Collected', 'school-management-system' ); ?></h3>
				<p class="value" style="font-size: 36px; font-weight: 700; margin: 0; line-height: 1.2;">
					<?php echo esc_html( $currency ) . ' ' . number_format( Fee::get_total_collected( array( 'fee_type' => 'Admission Fee' ) ), 2 ); ?>
				</p>
			</div>
			<div class="sms-stat-card" style="background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%); border-radius: 12px; padding: 25px; color: #fff; position: relative; overflow: hidden;">
				<span class="dashicons dashicons-warning" style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%); font-size: 80px; width: 80px; height: 80px; opacity: 0.15;"></span>
				<h3 style="margin: 0 0 10px; font-size: 15px; font-weight: 600; text-transform: uppercase; color: rgba(255,255,255,0.9);"><?php esc_html_e( 'Pending Admission Fees', 'school-management-system' ); ?></h3>
				<p class="value" style="font-size: 36px; font-weight: 700; margin: 0; line-height: 1.2;">
					<?php echo esc_html( $currency ) . ' ' . number_format( Fee::get_total_pending( array( 'fee_type' => 'Admission Fee' ) ), 2 ); ?>
				</p>
			</div>
		</div>

		<div class="sms-enrollment-stats">
			<div class="sms-enrollment-stat">
				<div class="sms-enrollment-stat-icon total"><span class="dashicons dashicons-groups"></span></div>
				<div>
					<div class="sms-enrollment-stat-number"><?php echo intval( $total_enrollments ); ?></div>
					<div class="sms-enrollment-stat-label"><?php esc_html_e( 'Total Enrollments', 'school-management-system' ); ?></div>
				</div>
			</div>
			<div class="sms-enrollment-stat">
				<div class="sms-enrollment-stat-icon active"><span class="dashicons dashicons-yes-alt"></span></div>
				<div>
					<div class="sms-enrollment-stat-number"><?php echo intval( $active_enrollments ); ?></div>
					<div class="sms-enrollment-stat-label"><?php esc_html_e( 'Active', 'school-management-system' ); ?></div>
				</div>
			</div>
			<div class="sms-enrollment-stat">
				<div class="sms-enrollment-stat-icon pending"><span class="dashicons dashicons-clock"></span></div>
				<div>
					<div class="sms-enrollment-stat-number"><?php echo intval( $pending_enrollments ); ?></div>
					<div class="sms-enrollment-stat-label"><?php esc_html_e( 'Pending', 'school-management-system' ); ?></div>
				</div>
			</div>
			<div class="sms-enrollment-stat">
				<div class="sms-enrollment-stat-icon inactive"><span class="dashicons dashicons-minus"></span></div>
				<div>
					<div class="sms-enrollment-stat-number"><?php echo intval( $inactive_enrollments ); ?></div>
					<div class="sms-enrollment-stat-label"><?php esc_html_e( 'Inactive', 'school-management-system' ); ?></div>
				</div>
			</div>
			<div class="sms-enrollment-stat">
				<div class="sms-enrollment-stat-icon students"><span class="dashicons dashicons-user"></span></div>
				<div>
					<div class="sms-enrollment-stat-number"><?php echo intval( $students_count ); ?></div>
					<div class="sms-enrollment-stat-label"><?php esc_html_e( 'Total Students', 'school-management-system' ); ?></div>
				</div>
			</div>
		</div>

	<!-- Add/Edit Form -->
		<div class="sms-panel" id="sms-enrollment-form">
			<div class="sms-panel-header">
				<h2><?php echo $edit_enrollment ? esc_html__( 'Edit Enrollment', 'school-management-system' ) : esc_html__( 'Enroll New Student', 'school-management-system' ); ?></h2>
				<div class="sms-enrollments-subtitle" style="opacity: 0.8; font-size: 12px; margin: 0;"><?php esc_html_e( 'Fill in student details and assign to class. Student will be automatically added to Students list.', 'school-management-system' ); ?></div>
			</div>
			<div class="sms-panel-body">
				<!-- New Student Form -->
				<form method="post" action="" id="sms-enroll-student-form">
					<?php wp_nonce_field( 'sms_nonce_form', 'sms_nonce' ); ?>
					<input type="hidden" name="enrollment_id" id="enrollment_id" value="<?php echo $edit_enrollment ? intval( $edit_enrollment->id ) : ''; ?>">
					<input type="hidden" name="student_id" id="student_id" value="<?php echo $edit_student ? intval( $edit_student->id ) : ''; ?>">
					<div class="sms-form-grid">
							<div class="sms-form-field">
								<label class="sms-form-label" for="first_name"><?php esc_html_e( 'Student Name', 'school-management-system' ); ?></label>
								<div class="sms-form-control">
									<input type="text" name="first_name" id="first_name" value="<?php echo $edit_student ? esc_attr( $edit_student->first_name . ($edit_student->last_name ? ' ' . $edit_student->last_name : '') ) : ''; ?>" placeholder="<?php esc_attr_e( 'Enter student full name', 'school-management-system' ); ?>" />
								</div>
							</div>
							<div class="sms-form-field">
								<label class="sms-form-label" for="class_id"><?php esc_html_e( 'Class Name', 'school-management-system' ); ?></label>
								<div class="sms-form-control">
									<select name="class_id" id="class_id" required>
										<option value=""><?php esc_html_e( 'Select Class', 'school-management-system' ); ?></option>
										<?php
										$classes = Classm::get_all( array(), 100 );
										foreach ( $classes as $class ) {
											?>
											<option value="<?php echo intval( $class->id ); ?>" <?php echo $edit_enrollment && $edit_enrollment->class_id == $class->id ? 'selected' : ''; ?>>
												<?php echo esc_html( $class->class_name ); ?>
											</option>
											<?php
										}
										?>
									</select>
								</div>
							</div>
							<div class="sms-form-field">
								<label class="sms-form-label" for="roll_number"><?php esc_html_e( 'Roll Number', 'school-management-system' ); ?></label>
								<div class="sms-form-control">
									<input type="text" name="roll_number" id="roll_number" value="<?php echo $edit_student ? esc_attr( $edit_student->roll_number ) : ''; ?>" placeholder="<?php esc_attr_e( 'Auto-generated if empty', 'school-management-system' ); ?>" />
								</div>
							</div>
							<div class="sms-form-field">
								<label class="sms-form-label" for="enrollment_date"><?php esc_html_e( 'Enrollment Date', 'school-management-system' ); ?></label>
								<div class="sms-form-control">
									<input type="date" name="enrollment_date" id="enrollment_date" value="<?php echo $edit_enrollment ? esc_attr( $edit_enrollment->enrollment_date ) : esc_attr( date( 'Y-m-d' ) ); ?>" />
								</div>
							</div>
							<div class="sms-form-field">
								<label class="sms-form-label" for="status"><?php esc_html_e( 'Status / Payment', 'school-management-system' ); ?></label>
								<div class="sms-form-control">
									<select name="status" id="status">
										<option value="active" <?php echo $edit_enrollment && 'active' === $edit_enrollment->status ? 'selected' : ''; ?>><?php esc_html_e( 'Paid (Active)', 'school-management-system' ); ?></option>
										<option value="pending" <?php echo $edit_enrollment && 'pending' === $edit_enrollment->status ? 'selected' : ''; ?>><?php esc_html_e( 'Unpaid (Pending)', 'school-management-system' ); ?></option>
									</select>
								</div>
							</div>
							<div class="sms-form-field">
								<label class="sms-form-label" for="address"><?php esc_html_e( 'Address', 'school-management-system' ); ?></label>
								<div class="sms-form-control">
									<input type="text" name="address" id="address" value="<?php echo $edit_student ? esc_attr( $edit_student->address ) : ''; ?>" placeholder="<?php esc_attr_e( 'Enter address', 'school-management-system' ); ?>" />
								</div>
							</div>
							<div class="sms-form-field">
								<label class="sms-form-label" for="parent_name"><?php esc_html_e( 'Parents Name', 'school-management-system' ); ?></label>
								<div class="sms-form-control">
									<input type="text" name="parent_name" id="parent_name" value="<?php echo $edit_student ? esc_attr( $edit_student->parent_name ) : ''; ?>" placeholder="<?php esc_attr_e( 'Enter parents name', 'school-management-system' ); ?>" />
								</div>
							</div>
							<div class="sms-form-field">
								<label class="sms-form-label" for="parent_phone"><?php esc_html_e( 'Parents Phone Number', 'school-management-system' ); ?></label>
								<div class="sms-form-control">
									<input type="text" name="parent_phone" id="parent_phone" value="<?php echo $edit_student ? esc_attr( $edit_student->parent_phone ) : ''; ?>" placeholder="<?php esc_attr_e( 'Enter parents phone', 'school-management-system' ); ?>" />
								</div>
							</div>
							<div class="sms-form-field">
								<label class="sms-form-label" for="admission_fee"><?php esc_html_e( 'Admission Fee', 'school-management-system' ); ?></label>
								<div class="sms-form-control">
									<input type="number" name="admission_fee" id="admission_fee" value="<?php echo $edit_fee ? esc_attr( $edit_fee->amount ) : ''; ?>" placeholder="<?php esc_attr_e( 'Enter amount', 'school-management-system' ); ?>" step="0.01" />
								</div>
							</div>
						</div>
						<div class="sms-form-actions">
							<button type="submit" name="sms_enroll_new_student" class="sms-btn sms-btn-primary">
								<span class="dashicons dashicons-<?php echo $edit_enrollment ? 'update' : 'plus-alt'; ?>"></span>
								<?php echo $edit_enrollment ? esc_html__( 'Update Enrollment', 'school-management-system' ) : esc_html__( 'Enroll Student', 'school-management-system' ); ?>
							</button>
							<?php if ( $edit_enrollment ) : ?>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=sms-enrollments' ) ); ?>" class="sms-btn sms-btn-secondary">
									<?php esc_html_e( 'Cancel', 'school-management-system' ); ?>
								</a>
							<?php endif; ?>
						</div>
					</form>
			</div>
		</div>

	<!-- Enrollments List -->
		<div class="sms-panel">
			<div class="sms-panel-header">
				<h2><?php esc_html_e( 'Enrollments List', 'school-management-system' ); ?></h2>
				<a class="sms-cta-btn" href="#sms-enrollment-form" style="font-size: 12px; padding: 6px 12px; margin-right: auto; margin-left: 15px; background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3);">
					<span class="dashicons dashicons-plus-alt"></span>
					<?php esc_html_e( 'Enroll Student', 'school-management-system' ); ?>
				</a>
				<form method="get" action="" class="sms-enrollments-search">
					<input type="hidden" name="page" value="sms-enrollments" />
					<?php $fee_status = isset( $_GET['fee_status'] ) ? sanitize_text_field( $_GET['fee_status'] ) : ''; ?>
					<select name="fee_status" style="margin-right: 10px; border-radius: 10px; border: 1px solid #dee2e6; padding: 10px 12px;">
						<option value=""><?php esc_html_e( 'All Payment Status', 'school-management-system' ); ?></option>
						<option value="paid" <?php selected( $fee_status, 'paid' ); ?>><?php esc_html_e( 'Paid', 'school-management-system' ); ?></option>
						<option value="pending" <?php selected( $fee_status, 'pending' ); ?>><?php esc_html_e( 'Pending', 'school-management-system' ); ?></option>
					</select>
					<input type="search" name="s" value="<?php echo isset( $_GET['s'] ) ? esc_attr( $_GET['s'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'Search by student or class...', 'school-management-system' ); ?>" />
					<button type="submit" class="sms-search-btn"><?php esc_html_e( 'Search', 'school-management-system' ); ?></button>
					<?php if ( ! empty( $_GET['s'] ) || ! empty( $_GET['fee_status'] ) ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=sms-enrollments' ) ); ?>" class="sms-reset-btn"><?php esc_html_e( 'Reset', 'school-management-system' ); ?></a>
					<?php endif; ?>
				</form>
			</div>
			<div class="sms-panel-body">

	<form method="post" action="">
	<?php wp_nonce_field( 'sms_bulk_delete_enrollments_nonce', 'sms_bulk_delete_enrollments_nonce' ); ?>
	<div class="tablenav top">
		<div class="alignleft actions bulkactions">
			<select name="action">
				<option value="-1"><?php esc_html_e( 'Bulk Actions', 'school-management-system' ); ?></option>
				<option value="bulk_delete_enrollments"><?php esc_html_e( 'Delete', 'school-management-system' ); ?></option>
			</select>
			<input type="submit" class="button action" value="<?php esc_attr_e( 'Apply', 'school-management-system' ); ?>">
		</div>
	</div>
	<div class="sms-enrollments-table-wrap">
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<td id="cb" class="manage-column column-cb check-column"><input id="cb-select-all-enrollments" type="checkbox"></td>
				<th><?php esc_html_e( 'Student Name', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Class Name', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Roll Number', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Enrollment Date', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Status', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Address', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Parents Name', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Parents Phone', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Payment Details', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'school-management-system' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$search_term = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
			
			$filters = array(
				'search'     => $search_term,
				'fee_status' => $fee_status,
			);
			$enrollments = Enrollment::get_with_filters( $filters, 50 );
			
			if ( ! empty( $enrollments ) ) {
				foreach ( $enrollments as $enrollment ) {
					$student = Student::get( $enrollment->student_id );
					$class   = Classm::get( $enrollment->class_id );
					$delete_url = wp_nonce_url( admin_url( 'admin.php?page=sms-enrollments&action=delete&id=' . $enrollment->id ), 'sms_delete_enrollment_nonce', '_wpnonce' );
					?>
					<tr>
						<th scope="row" class="check-column"><input type="checkbox" name="enrollment_ids[]" value="<?php echo intval( $enrollment->id ); ?>"></th>
						<td>
							<strong><?php echo $student ? esc_html( $student->first_name . ' ' . $student->last_name ) : 'N/A'; ?></strong>
						</td>
						<td><?php echo $class ? esc_html( $class->class_name ) : 'N/A'; ?></td>
						<td><?php echo $student ? esc_html( $student->roll_number ) : 'N/A'; ?></td>
						<td><?php echo esc_html( $enrollment->enrollment_date ); ?></td>
						<td><?php echo esc_html( $enrollment->status ); ?></td>
						<td><?php echo $student ? esc_html( $student->address ) : 'N/A'; ?></td>
						<td><?php echo $student ? esc_html( $student->parent_name ) : 'N/A'; ?></td>
						<td><?php echo $student ? esc_html( $student->parent_phone ) : 'N/A'; ?></td>
						<td>
							<?php
							$fees = Fee::get_student_fees( $enrollment->student_id );
							$admission_fee = null;
							if ( ! empty( $fees ) ) {
								foreach ( $fees as $f ) {
									if ( 'Admission Fee' === $f->fee_type && $f->class_id == $enrollment->class_id ) {
										$admission_fee = $f;
										break;
									}
								}
							}
							
							if ( $admission_fee ) {
								$currency = get_option( 'sms_settings' )['currency'] ?? '৳';
								echo '<div style="font-weight:bold;">' . esc_html( $currency . ' ' . number_format( $admission_fee->amount, 2 ) ) . '</div>';
								
								$status_class = 'paid' === $admission_fee->status ? 'active' : 'pending';
								echo '<span class="sms-status-pill ' . esc_attr( $status_class ) . '" style="font-size:10px; padding:2px 6px;">' . esc_html( ucfirst( $admission_fee->status ) ) . '</span>';
								
								if ( 'paid' === $admission_fee->status ) {
									echo '<button type="button" class="button button-small sms-voucher-btn" data-fee-id="' . intval( $admission_fee->id ) . '" title="' . esc_attr__( 'Download Voucher', 'school-management-system' ) . '" style="margin-left:5px; vertical-align: middle;"><span class="dashicons dashicons-download" style="font-size:14px; line-height:1.5;"></span></button>';
								}
							} else {
								echo '<span style="color:#999;">-</span>';
							}
							?>
						</td>
						<td>
							<div class="sms-row-actions">
								<a class="sms-row-action-btn edit" href="<?php echo esc_url( admin_url( 'admin.php?page=sms-enrollments&action=edit&id=' . $enrollment->id ) ); ?>">
									<span class="dashicons dashicons-edit"></span>
									<?php esc_html_e( 'Edit', 'school-management-system' ); ?>
								</a>
								<a class="sms-row-action-btn delete" href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('<?php esc_attr_e( 'Are you sure?', 'school-management-system' ); ?>')">
									<span class="dashicons dashicons-trash"></span>
									<?php esc_html_e( 'Delete', 'school-management-system' ); ?>
								</a>
							</div>
						</td>
					</tr>
					<?php
				}
			} else {
				?>
				<tr>
					<td colspan="5"><?php esc_html_e( 'No enrollments found', 'school-management-system' ); ?></td>
				</tr>
				<?php
			}
			?>
		</tbody>
	</table>
	</div>
	</form>
			</div>
		</div>
	</div>
</div>
