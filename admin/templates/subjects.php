<?php
/**
 * Subjects admin template.
 *
 * @package School_Management_System
 */

use School_Management_System\Subject;
use School_Management_System\Teacher;

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'Unauthorized', 'school-management-system' ) );
}

$subject = null;
$is_edit = false;
$action = sanitize_text_field( $_GET['action'] ?? '' );
$subject_id = intval( $_GET['id'] ?? 0 );

if ( 'edit' === $action && $subject_id ) {
	$subject = Subject::get( $subject_id );
	if ( ! $subject ) {
		wp_die( esc_html__( 'Subject not found', 'school-management-system' ) );
	}
	$is_edit = true;
}

$message = '';
if ( isset( $_GET['sms_message'] ) ) {
	$sms_message = sanitize_text_field( $_GET['sms_message'] );
	if ( 'subjects_bulk_deleted' === $sms_message ) {
		$count = intval( $_GET['count'] ?? 0 );
		$message = sprintf( __( '%d subjects deleted successfully.', 'school-management-system' ), $count );
	}
}

$total_subjects = Subject::count();
$active_subjects = Subject::count( array( 'status' => 'active' ) );
$inactive_subjects = Subject::count( array( 'status' => 'inactive' ) );

?>
<style>
 .sms-subjects-page { max-width: 100%; }
 .sms-subjects-header {
 	display: flex;
 	justify-content: space-between;
 	align-items: flex-start;
 	gap: 16px;
 	background: linear-gradient(135deg, #ff6b6b 0%, #feca57 100%);
 	color: #fff;
 	padding: 22px;
 	border-radius: 16px;
 	box-shadow: 0 10px 30px rgba(255, 107, 107, 0.22);
 	margin: 10px 0 18px;
 }
 .sms-subjects-title h1 { margin: 0; color: #fff; font-size: 22px; line-height: 1.2; }
 .sms-subjects-subtitle { margin: 6px 0 0; opacity: 0.92; font-size: 13px; }
 .sms-subjects-header-actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }
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

 .sms-subject-stats {
 	display: grid;
 	grid-template-columns: repeat(3, minmax(0, 1fr));
 	gap: 16px;
 	margin-bottom: 18px;
 }
 .sms-subject-stat {
 	background: #fff;
 	border-radius: 16px;
 	padding: 18px;
 	box-shadow: 0 8px 22px rgba(0,0,0,0.08);
 	border: 1px solid #eef1f5;
 	display: flex;
 	align-items: center;
 	gap: 14px;
 }
 .sms-subject-stat-icon {
 	width: 44px;
 	height: 44px;
 	border-radius: 12px;
 	display: flex;
 	align-items: center;
 	justify-content: center;
 	color: #fff;
 }
 .sms-subject-stat-icon.total { background: linear-gradient(135deg, #ff6b6b 0%, #feca57 100%); }
 .sms-subject-stat-icon.active { background: linear-gradient(135deg, #48dbfb 0%, #0abde3 100%); }
 .sms-subject-stat-icon.inactive { background: linear-gradient(135deg, #ee5a6f 0%, #f368e0 100%); }
 .sms-subject-stat-icon .dashicons { font-size: 20px; width: 20px; height: 20px; }
 .sms-subject-stat-number { font-size: 20px; font-weight: 800; color: #2c3e50; line-height: 1.1; }
 .sms-subject-stat-label { font-size: 12px; color: #6c757d; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; }

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

 .sms-subjects-search {
 	display: flex;
 	gap: 10px;
 	flex-wrap: wrap;
 	justify-content: flex-end;
 	margin: 0;
 }
 .sms-subjects-search input[type="search"] { min-width: 260px; padding: 10px 12px; border: 1px solid #dee2e6; border-radius: 10px; }
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
 .sms-subjects-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }

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

 @media (max-width: 782px) {
 	.sms-subjects-header { flex-direction: column; align-items: flex-start; }
 	.sms-subjects-header-actions { width: 100%; justify-content: flex-start; }
 	.sms-subject-stats { grid-template-columns: 1fr; }
 	.sms-subjects-search { justify-content: flex-start; }
 	.sms-subjects-search input[type="search"] { min-width: 0; width: 100%; }
 }
</style>
<div class="wrap">
	<div class="sms-subjects-page">
		<div class="sms-subjects-header">
			<div class="sms-subjects-title">
				<h1><?php esc_html_e( 'Subjects', 'school-management-system' ); ?></h1>
				<div class="sms-subjects-subtitle"><?php esc_html_e( 'Manage subject lists, codes, and availability status.', 'school-management-system' ); ?></div>
			</div>
			<div class="sms-subjects-header-actions">
				<a class="sms-cta-btn" href="#sms-subject-form">
					<span class="dashicons dashicons-plus-alt"></span>
					<?php echo $is_edit ? esc_html__( 'Edit Mode', 'school-management-system' ) : esc_html__( 'Add Subject', 'school-management-system' ); ?>
				</a>
				<?php if ( $is_edit ) : ?>
					<a class="sms-cta-btn" href="<?php echo esc_url( admin_url( 'admin.php?page=sms-subjects' ) ); ?>">
						<span class="dashicons dashicons-no"></span>
						<?php esc_html_e( 'Cancel', 'school-management-system' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>

	<?php if ( ! empty( $message ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
	<?php endif; ?>

		<div class="sms-subject-stats">
			<div class="sms-subject-stat">
				<div class="sms-subject-stat-icon total"><span class="dashicons dashicons-book"></span></div>
				<div>
					<div class="sms-subject-stat-number"><?php echo intval( $total_subjects ); ?></div>
					<div class="sms-subject-stat-label"><?php esc_html_e( 'Total Subjects', 'school-management-system' ); ?></div>
				</div>
			</div>
			<div class="sms-subject-stat">
				<div class="sms-subject-stat-icon active"><span class="dashicons dashicons-yes-alt"></span></div>
				<div>
					<div class="sms-subject-stat-number"><?php echo intval( $active_subjects ); ?></div>
					<div class="sms-subject-stat-label"><?php esc_html_e( 'Active', 'school-management-system' ); ?></div>
				</div>
			</div>
			<div class="sms-subject-stat">
				<div class="sms-subject-stat-icon inactive"><span class="dashicons dashicons-minus"></span></div>
				<div>
					<div class="sms-subject-stat-number"><?php echo intval( $inactive_subjects ); ?></div>
					<div class="sms-subject-stat-label"><?php esc_html_e( 'Inactive', 'school-management-system' ); ?></div>
				</div>
			</div>
		</div>

	<!-- Add/Edit Form -->
		<div class="sms-panel" id="sms-subject-form">
			<div class="sms-panel-header">
				<h2><?php echo $is_edit ? esc_html__( 'Edit Subject', 'school-management-system' ) : esc_html__( 'Add New Subject', 'school-management-system' ); ?></h2>
			</div>
			<div class="sms-panel-body">

		<form method="post" action="">
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="subject_name"><?php esc_html_e( 'Subject Name *', 'school-management-system' ); ?></label>
					</th>
					<td>
						<input type="text" name="subject_name" id="subject_name" required value="<?php echo $subject ? esc_attr( $subject->subject_name ) : ''; ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="subject_code"><?php esc_html_e( 'Subject Code *', 'school-management-system' ); ?></label>
					</th>
					<td>
						<input type="text" name="subject_code" id="subject_code" required value="<?php echo $subject ? esc_attr( $subject->subject_code ) : ''; ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="teacher_id"><?php esc_html_e( 'Assign Teacher', 'school-management-system' ); ?></label>
					</th>
					<td>
						<select name="teacher_id" id="teacher_id">
							<option value=""><?php esc_html_e( 'Select Teacher', 'school-management-system' ); ?></option>
							<?php
							$teachers = Teacher::get_all( array( 'status' => 'active' ), 1000 );
							foreach ( $teachers as $teacher ) {
								printf( '<option value="%d" %s>%s</option>', intval( $teacher->id ), selected( $subject ? $subject->teacher_id : 0, $teacher->id, false ), esc_html( $teacher->first_name . ' ' . $teacher->last_name ) );
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="status"><?php esc_html_e( 'Status', 'school-management-system' ); ?></label>
					</th>
					<td>
						<select name="status" id="status">
							<option value="active" <?php echo ! $subject || 'active' === $subject->status ? 'selected' : ''; ?>>
								<?php esc_html_e( 'Active', 'school-management-system' ); ?>
							</option>
							<option value="inactive" <?php echo $subject && 'inactive' === $subject->status ? 'selected' : ''; ?>>
								<?php esc_html_e( 'Inactive', 'school-management-system' ); ?>
							</option>
						</select>
					</td>
				</tr>
			</table>

			<?php if ( $is_edit ) : ?>
				<input type="hidden" name="subject_id" value="<?php echo intval( $subject->id ); ?>" />
				<button type="submit" name="sms_edit_subject" class="button button-primary">
					<?php esc_html_e( 'Update Subject', 'school-management-system' ); ?>
				</button>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=sms-subjects' ) ); ?>" class="button">
					<?php esc_html_e( 'Cancel', 'school-management-system' ); ?>
				</a>
			<?php else : ?>
				<button type="submit" name="sms_add_subject" class="button button-primary">
					<?php esc_html_e( 'Add Subject', 'school-management-system' ); ?>
				</button>
			<?php endif; ?>
		</form>
			</div>
		</div>

	<!-- Subjects List -->
		<div class="sms-panel">
			<div class="sms-panel-header">
				<h2><?php esc_html_e( 'Subjects List', 'school-management-system' ); ?></h2>
				<form method="get" action="" class="sms-subjects-search">
					<input type="hidden" name="page" value="sms-subjects" />
					<select name="teacher_id" style="margin-right: 10px; border-radius: 10px; border: 1px solid #dee2e6; padding: 10px 12px;">
						<option value=""><?php esc_html_e( 'All Teachers', 'school-management-system' ); ?></option>
						<?php
						$teachers = Teacher::get_all( array( 'status' => 'active' ), 1000 );
						$selected_teacher = isset( $_GET['teacher_id'] ) ? intval( $_GET['teacher_id'] ) : 0;
						foreach ( $teachers as $teacher ) {
							printf( '<option value="%d" %s>%s</option>', intval( $teacher->id ), selected( $selected_teacher, $teacher->id, false ), esc_html( $teacher->first_name . ' ' . $teacher->last_name ) );
						}
						?>
					</select>
					<input type="search" name="s" value="<?php echo isset( $_GET['s'] ) ? esc_attr( $_GET['s'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'Search subjects...', 'school-management-system' ); ?>" />
					<button type="submit" class="sms-search-btn"><?php esc_html_e( 'Search', 'school-management-system' ); ?></button>
					<?php if ( ! empty( $_GET['s'] ) || ! empty( $_GET['teacher_id'] ) ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=sms-subjects' ) ); ?>" class="sms-reset-btn"><?php esc_html_e( 'Reset', 'school-management-system' ); ?></a>
					<?php endif; ?>
				</form>
			</div>
			<div class="sms-panel-body">

	<form method="post" action="">
	<?php wp_nonce_field( 'sms_bulk_delete_subjects_nonce', 'sms_bulk_delete_subjects_nonce' ); ?>
	<div class="tablenav top">
		<div class="alignleft actions bulkactions">
			<select name="action">
				<option value="-1"><?php esc_html_e( 'Bulk Actions', 'school-management-system' ); ?></option>
				<option value="bulk_delete_subjects"><?php esc_html_e( 'Delete', 'school-management-system' ); ?></option>
			</select>
			<input type="submit" class="button action" value="<?php esc_attr_e( 'Apply', 'school-management-system' ); ?>">
		</div>
	</div>
	<div class="sms-subjects-table-wrap">
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<td id="cb" class="manage-column column-cb check-column"><input id="cb-select-all-subjects" type="checkbox"></td>
				<th><?php esc_html_e( 'ID', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Subject Name', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Subject Code', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Assigned Teacher', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Status', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'school-management-system' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$search_term = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
			$teacher_filter = isset( $_GET['teacher_id'] ) ? intval( $_GET['teacher_id'] ) : 0;

			if ( ! empty( $search_term ) ) {
				$subjects = Subject::search( $search_term );
			} elseif ( $teacher_filter ) {
				$subjects = Subject::get_all( array( 'teacher_id' => $teacher_filter ), 50 );
			} else {
				$subjects = Subject::get_all( array(), 50 );
			}
			if ( ! empty( $subjects ) ) {
				foreach ( $subjects as $subject ) {
					?>
					<tr>
						<th scope="row" class="check-column"><input type="checkbox" name="subject_ids[]" value="<?php echo intval( $subject->id ); ?>"></th>
						<td><?php echo intval( $subject->id ); ?></td>
						<td><?php echo esc_html( $subject->subject_name ); ?></td>
						<td><?php echo esc_html( $subject->subject_code ); ?></td>
						<td>
							<?php 
							$teacher_obj = $subject->teacher_id ? Teacher::get( $subject->teacher_id ) : null;
							echo $teacher_obj ? esc_html( $teacher_obj->first_name . ' ' . $teacher_obj->last_name ) : '<span style="color:#999;">' . esc_html__( 'Not Assigned', 'school-management-system' ) . '</span>'; 
							?>
						</td>
						<td>
							<span class="sms-status-pill <?php echo 'inactive' === $subject->status ? 'inactive' : 'active'; ?>">
								<?php echo esc_html( $subject->status ); ?>
							</span>
						</td>
						<td>
							<div class="sms-row-actions">
								<a class="sms-row-action-btn edit" href="<?php echo esc_url( admin_url( 'admin.php?page=sms-subjects&action=edit&id=' . $subject->id ) ); ?>">
									<span class="dashicons dashicons-edit"></span>
									<?php esc_html_e( 'Edit', 'school-management-system' ); ?>
								</a>
							</div>
						</td>
					</tr>
					<?php
				}
			} else {
				?>
				<tr>
					<td colspan="7"><?php esc_html_e( 'No subjects found', 'school-management-system' ); ?></td>
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
