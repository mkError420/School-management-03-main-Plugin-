<?php

/**

 * Students admin template.

 *

 * @package School_Management_System

 */



use School_Management_System\Student;

use School_Management_System\Classm;

use School_Management_System\Enrollment;



// Check user capability.

if ( ! current_user_can( 'manage_options' ) ) {

	wp_die( esc_html__( 'Unauthorized', 'school-management-system' ) );

}



$student = null;

$is_edit = false;

$action = sanitize_text_field( $_GET['action'] ?? '' );

$student_id = intval( $_GET['id'] ?? 0 );



if ( 'edit' === $action && $student_id ) {

	$student = Student::get( $student_id );

	if ( ! $student ) {

		wp_die( esc_html__( 'Student not found', 'school-management-system' ) );

	}

	$is_edit = true;

}



$show_form = ( 'add' === $action || $is_edit );



$current_class_id = 0;

if ( $is_edit && $student ) {

	$enrollments = Enrollment::get_student_enrollments( $student->id );

	if ( ! empty( $enrollments ) ) {

		// Get the most recent enrollment.

		$current_class_id = $enrollments[0]->class_id;

	}

}



$message = '';

if ( isset( $_GET['sms_message'] ) ) {

	$sms_message = sanitize_text_field( $_GET['sms_message'] );

	if ( 'student_added' === $sms_message ) {

		$message = __( 'Student added successfully.', 'school-management-system' );

	} elseif ( 'student_updated' === $sms_message ) {

		$message = __( 'Student updated successfully.', 'school-management-system' );

	} elseif ( 'student_deleted' === $sms_message ) {

		$message = __( 'Student deleted successfully.', 'school-management-system' );

	} elseif ( 'students_bulk_deleted' === $sms_message ) {

		$count = intval( $_GET['count'] ?? 0 );

		$message = sprintf( __( '%d students deleted successfully.', 'school-management-system' ), $count );

	} elseif ( 'import_completed' === $sms_message ) {

		$count = intval( $_GET['count'] ?? 0 );

		$failed = intval( $_GET['failed'] ?? 0 );

		$error_msg = isset( $_GET['error'] ) ? sanitize_text_field( urldecode( $_GET['error'] ) ) : '';

		$message = sprintf( __( 'Import completed. %d students added successfully. %d failed.', 'school-management-system' ), $count, $failed );

		if ( $failed > 0 && ! empty( $error_msg ) ) {

			$message .= ' ' . sprintf( __( 'Last error: %s', 'school-management-system' ), $error_msg );

		} elseif ( $failed > 0 ) {

			$message .= ' ' . __( '(duplicates or missing fields)', 'school-management-system' );

		}

	}

}



$total_students = Student::count();

$active_students = Student::count( array( 'status' => 'active' ) );

$pending_students = Student::count( array( 'status' => 'pending' ) );



?>

<style>

 .sms-students-page { max-width: 100%; }

 .sms-students-header {

 	display: flex;

 	justify-content: space-between;

 	align-items: flex-start;

 	gap: 16px;

 	background: linear-gradient(135deg, #28a745 0%, #20c997 100%);

 	color: #fff;

 	padding: 22px;

 	border-radius: 16px;

 	box-shadow: 0 10px 30px rgba(40, 167, 69, 0.22);

 	margin: 10px 0 18px;

 }

 .sms-students-title h1 { margin: 0; color: #fff; font-size: 22px; line-height: 1.2; }

 .sms-students-subtitle { margin: 6px 0 0; opacity: 0.92; font-size: 13px; }

 .sms-students-header-actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }

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



 .sms-student-stats {

 	display: grid;

 	grid-template-columns: repeat(5, minmax(0, 1fr));

 	gap: 16px;

 	margin-bottom: 18px;

 }

 .sms-student-stat {

 	background: #fff;

 	border-radius: 16px;

 	padding: 18px;

 	box-shadow: 0 8px 22px rgba(0,0,0,0.08);

 	border: 1px solid #eef1f5;

 	display: flex;

 	align-items: center;

 	gap: 14px;

 }

 .sms-student-stat-icon {

 	width: 44px;

 	height: 44px;

 	border-radius: 12px;

 	display: flex;

 	align-items: center;

 	justify-content: center;

 	color: #fff;

 }

 .sms-student-stat-icon.total { background: linear-gradient(135deg, #28a745 0%, #71e891 100%); }

 .sms-student-stat-icon.active { background: linear-gradient(135deg, #17a2b8 0%, #6ec8e3 100%); }

 .sms-student-stat-icon.inactive { background: linear-gradient(135deg, #dc3545 0%, #f86c7b 100%); }

 .sms-student-stat-icon .dashicons { font-size: 20px; width: 20px; height: 20px; }

 .sms-student-stat-number { font-size: 20px; font-weight: 800; color: #2c3e50; line-height: 1.1; }

 .sms-student-stat-label { font-size: 12px; color: #6c757d; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; }



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



 .sms-students-search {

 	display: flex;

 	gap: 10px;

 	flex-wrap: wrap;

 	justify-content: flex-end;

 	margin: 0;

 }

 .sms-students-search input[type="search"] { min-width: 260px; padding: 10px 12px; border: 1px solid #dee2e6; border-radius: 10px; }

 .sms-students-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }



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

 .sms-row-action-btn.edit { border-color: rgba(102, 126, 234, 0.35); color: #4b5bdc; }

 .sms-row-action-btn.delete { border-color: rgba(220, 53, 69, 0.35); color: #dc3545; }

 .sms-row-action-btn.details { border-color: rgba(23, 162, 184, 0.35); color: #17a2b8; }



 .student-details-row td { background-color: #f9f9f9; padding: 20px !important; }

 .student-details-list { list-style: none; margin: 0; padding: 0; }

 .student-details-list li { margin-bottom: 8px; }

 .student-details-list strong { display: inline-block; width: 120px; color: #555; }



 @media (max-width: 782px) {

 	.sms-students-header { flex-direction: column; align-items: flex-start; }

 	.sms-students-header-actions { width: 100%; justify-content: flex-start; }

 	.sms-student-stats { grid-template-columns: 1fr; }

 	.sms-students-search { justify-content: flex-start; }

 	.sms-students-search input[type="search"] { min-width: 0; width: 100%; }

 }

</style>

<div class="wrap">

	<div class="sms-students-page">

		<div class="sms-students-header">

			<div class="sms-students-title">

				<h1><?php esc_html_e( 'Students', 'school-management-system' ); ?></h1>

				<div class="sms-students-subtitle"><?php esc_html_e( 'Manage student enrollment, class assignments, and profile details.', 'school-management-system' ); ?></div>

			</div>

			<div class="sms-students-header-actions">

				<a class="sms-cta-btn" href="<?php echo esc_url( admin_url( 'admin.php?page=sms-enrollments' ) ); ?>">

					<span class="dashicons dashicons-plus-alt"></span>

					<?php esc_html_e( 'Enroll New Student', 'school-management-system' ); ?>

				</a>

			</div>

		</div>



	<?php if ( ! empty( $message ) ) : ?>

		<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>

	<?php endif; ?>



		<div class="sms-student-stats">

			<div class="sms-student-stat">

				<div class="sms-student-stat-icon total"><span class="dashicons dashicons-groups"></span></div>

				<div>

					<div class="sms-student-stat-number"><?php echo intval( $total_students ); ?></div>

					<div class="sms-student-stat-label"><?php esc_html_e( 'Total Students', 'school-management-system' ); ?></div>

				</div>

			</div>

			<div class="sms-student-stat">

				<div class="sms-student-stat-icon active"><span class="dashicons dashicons-yes-alt"></span></div>

				<div>

					<div class="sms-student-stat-number"><?php echo intval( $active_students ); ?></div>

					<div class="sms-student-stat-label"><?php esc_html_e( 'Active', 'school-management-system' ); ?></div>

				</div>

			</div>

			<div class="sms-student-stat">

				<div class="sms-student-stat-icon" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);"><span class="dashicons dashicons-clock"></span></div>

				<div>

					<div class="sms-student-stat-number"><?php echo intval( $pending_students ); ?></div>

					<div class="sms-student-stat-label"><?php esc_html_e( 'Pending', 'school-management-system' ); ?></div>

				</div>

			</div>

			<div class="sms-student-stat">

				<div class="sms-student-stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"><span class="dashicons dashicons-plus"></span></div>

				<div>

					<div class="sms-student-stat-label"><?php esc_html_e( 'Add Students Via', 'school-management-system' ); ?></div>

					<div style="font-size: 11px; color: #667eea; font-weight: 700;"><?php esc_html_e( 'Enrollments Section', 'school-management-system' ); ?></div>

				</div>

			</div>

		</div>



	<!-- Students List -->

		<div class="sms-panel">

			<div class="sms-panel-header">

				<h2><?php esc_html_e( 'Students List', 'school-management-system' ); ?></h2>

				<form method="get" action="" class="sms-students-search">

					<input type="hidden" name="page" value="sms-students" />

					<input type="search" name="s" value="<?php echo isset( $_GET['s'] ) ? esc_attr( $_GET['s'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'Search by name, email, or roll...', 'school-management-system' ); ?>" />

					<button type="submit" class="button"><?php esc_html_e( 'Search', 'school-management-system' ); ?></button>

					<?php if ( ! empty( $_GET['s'] ) ) : ?>

						<a href="<?php echo esc_url( admin_url( 'admin.php?page=sms-students' ) ); ?>" class="button"><?php esc_html_e( 'Reset', 'school-management-system' ); ?></a>

					<?php endif; ?>

				</form>

			</div>

			<div class="sms-panel-body">



	<form method="post" action="">

	<?php wp_nonce_field( 'sms_bulk_delete_students_nonce', 'sms_bulk_delete_nonce' ); ?>

	<div class="tablenav top">

		<div class="alignleft actions bulkactions">

			<select name="action">

				<option value="-1"><?php esc_html_e( 'Bulk Actions', 'school-management-system' ); ?></option>

				<option value="bulk_delete"><?php esc_html_e( 'Delete', 'school-management-system' ); ?></option>

			</select>

			<input type="submit" class="button action" value="<?php esc_attr_e( 'Apply', 'school-management-system' ); ?>">

		</div>

	</div>

	<div class="sms-students-table-wrap">

	<table class="wp-list-table widefat fixed striped students-table">

		<thead>

			<tr>

				<td id="cb" class="manage-column column-cb check-column"><input id="cb-select-all-1" type="checkbox"></td>

				<th><?php esc_html_e( 'Student Name', 'school-management-system' ); ?></th>

				<th><?php esc_html_e( 'Class Name', 'school-management-system' ); ?></th>

				<th><?php esc_html_e( 'Roll Number', 'school-management-system' ); ?></th>

				<th><?php esc_html_e( 'Enrollment Date', 'school-management-system' ); ?></th>

				<th><?php esc_html_e( 'Actions', 'school-management-system' ); ?></th>

			</tr>

		</thead>

		<tbody>

			<?php

			$search_term = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';

			

			if ( ! empty( $search_term ) ) {

				$students = Student::search( $search_term );

			} else {

				$students = Student::get_all( array(), 50 );

			}

			

			if ( ! empty( $students ) ) {

				foreach ( $students as $student ) {

					$class_name = '';

					$enrollment_date = '';

					$enrollments = Enrollment::get_student_enrollments( $student->id );

					if ( ! empty( $enrollments ) ) {

						$class_obj = Classm::get( $enrollments[0]->class_id );

						if ( $class_obj ) {

							$class_name = $class_obj->class_name;

						}

						$enrollment_date = $enrollments[0]->enrollment_date ?? $student->enrollment_date ?? '';

					}

					$delete_url = wp_nonce_url( admin_url( 'admin.php?page=sms-students&action=delete&id=' . $student->id ), 'sms_delete_student_nonce', '_wpnonce' );

					?>

					<tr>

						<th scope="row" class="check-column"><input type="checkbox" name="student_ids[]" value="<?php echo intval( $student->id ); ?>"></th>

						<td><?php echo esc_html( $student->first_name . ' ' . $student->last_name ); ?></td>

						<td><?php echo esc_html( $class_name ); ?></td>

						<td><?php echo esc_html( $student->roll_number ?? '' ); ?></td>

						<td><?php echo esc_html( $enrollment_date ? date( 'Y-m-d', strtotime( $enrollment_date ) ) : '' ); ?></td>

						<td>

							<div class="sms-row-actions">

								<button class="sms-row-action-btn details toggle-details-btn" data-target="#details-<?php echo intval( $student->id ); ?>">

									<span class="dashicons dashicons-visibility"></span>

									<?php esc_html_e( 'Details', 'school-management-system' ); ?>

								</button>

								<a class="sms-row-action-btn edit" href="<?php echo esc_url( admin_url( 'admin.php?page=sms-students&action=edit&id=' . $student->id ) ); ?>">

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

					<tr id="details-<?php echo intval( $student->id ); ?>" class="student-details-row" style="display: none;">

						<td colspan="6">

							<ul class="student-details-list">

								<li><strong><?php esc_html_e( 'ID', 'school-management-system' ); ?>:</strong> <?php echo intval( $student->id ); ?></li>

								<li><strong><?php esc_html_e( 'Roll Number', 'school-management-system' ); ?>:</strong> <?php echo esc_html( $student->roll_number ?? '' ); ?></li>

								<li><strong><?php esc_html_e( 'Date of Birth', 'school-management-system' ); ?>:</strong> <?php echo esc_html( $student->dob ); ?></li>

								<li><strong><?php esc_html_e( 'Gender', 'school-management-system' ); ?>:</strong> <?php echo esc_html( $student->gender ); ?></li>

								<li><strong><?php esc_html_e( 'Address', 'school-management-system' ); ?>:</strong> <?php echo esc_html( $student->address ); ?></li>

								<li><strong><?php esc_html_e( 'Parent Name', 'school-management-system' ); ?>:</strong> <?php echo esc_html( $student->parent_name ); ?></li>

								<li><strong><?php esc_html_e( 'Parent Phone', 'school-management-system' ); ?>:</strong> <?php echo esc_html( $student->parent_phone ); ?></li>

								<li><strong><?php esc_html_e( 'Status', 'school-management-system' ); ?>:</strong> <?php echo esc_html( $student->status ); ?></li>

							</ul>

						</td>

					</tr>

					<?php

				}

			} else {

				?>

				<tr>

					<td colspan="6"><?php esc_html_e( 'No students found', 'school-management-system' ); ?></td>

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



	<script>

	jQuery(document).ready(function($) {

		$('.toggle-details-btn').on('click', function(e) {

			e.preventDefault();

			var targetRow = $(this).data('target');

			$(targetRow).toggle();

		});

	});

	</script>

	</div>

</div>

