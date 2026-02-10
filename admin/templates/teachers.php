<?php
/**
 * Teachers admin template.
 *
 * @package School_Management_System
 */

use School_Management_System\Teacher;

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'Unauthorized', 'school-management-system' ) );
}

$teacher = null;
$is_edit = false;
$action = sanitize_text_field( $_GET['action'] ?? '' );
$teacher_id = intval( $_GET['id'] ?? 0 );

if ( 'edit' === $action && $teacher_id ) {
	$teacher = Teacher::get( $teacher_id );
	if ( ! $teacher ) {
		wp_die( esc_html__( 'Teacher not found', 'school-management-system' ) );
	}
	$is_edit = true;
}

// The $teacher variable is set above when editing.
$full_name      = $is_edit ? trim( $teacher->first_name . ' ' . $teacher->last_name ) : '';
$email          = $is_edit ? $teacher->email : '';
$phone          = $is_edit ? $teacher->phone : '';
$employee_id    = $is_edit ? $teacher->employee_id : '';
$qualifications = $is_edit ? $teacher->qualification : '';
$status         = $is_edit ? $teacher->status : 'active';

$message = '';
if ( isset( $_GET['sms_message'] ) ) {
	$sms_message = sanitize_text_field( $_GET['sms_message'] );
	if ( 'teachers_bulk_deleted' === $sms_message ) {
		$count = intval( $_GET['count'] ?? 0 );
		$message = sprintf( __( '%d teachers deleted successfully.', 'school-management-system' ), $count );
	} elseif ( 'teacher_deleted' === $sms_message ) {
		$message = __( 'Teacher deleted successfully.', 'school-management-system' );
	}
}

$total_teachers = Teacher::count();
$active_teachers = Teacher::count( array( 'status' => 'active' ) );
$inactive_teachers = Teacher::count( array( 'status' => 'inactive' ) );

?>
<style>
 .sms-teachers-page {
 	max-width: 100%;
 }

 .sms-teachers-header {
 	display: flex;
 	justify-content: space-between;
 	align-items: flex-start;
 	gap: 16px;
 	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
 	color: #fff;
 	padding: 22px 22px;
 	border-radius: 16px;
 	box-shadow: 0 10px 30px rgba(102, 126, 234, 0.22);
 	margin: 10px 0 18px;
 }

 .sms-teachers-title h1 {
 	margin: 0;
 	color: #fff;
 	font-size: 22px;
 	line-height: 1.2;
 }

 .sms-teachers-subtitle {
 	margin: 6px 0 0;
 	opacity: 0.92;
 	font-size: 13px;
 }

 .sms-teachers-header-actions {
 	display: flex;
 	gap: 10px;
 	flex-wrap: wrap;
 	justify-content: flex-end;
 }

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

 .sms-cta-btn:hover {
 	background: rgba(255,255,255,0.24);
 	color: #fff;
 }

 .sms-teacher-stats {
 	display: grid;
 	grid-template-columns: repeat(3, minmax(0, 1fr));
 	gap: 16px;
 	margin-bottom: 18px;
 }

 .sms-teacher-stat {
 	background: #fff;
 	border-radius: 16px;
 	padding: 18px;
 	box-shadow: 0 8px 22px rgba(0,0,0,0.08);
 	border: 1px solid #eef1f5;
 	display: flex;
 	align-items: center;
 	gap: 14px;
 }

 .sms-teacher-stat-icon {
 	width: 44px;
 	height: 44px;
 	border-radius: 12px;
 	display: flex;
 	align-items: center;
 	justify-content: center;
 	color: #fff;
 }

 .sms-teacher-stat-icon.total { background: linear-gradient(135deg, #667eea 0%, #a8b8f8 100%); }
 .sms-teacher-stat-icon.active { background: linear-gradient(135deg, #28a745 0%, #71e891 100%); }
 .sms-teacher-stat-icon.inactive { background: linear-gradient(135deg, #dc3545 0%, #f86c7b 100%); }

 .sms-teacher-stat-icon .dashicons {
 	font-size: 20px;
 	width: 20px;
 	height: 20px;
 }

 .sms-teacher-stat-number {
 	font-size: 20px;
 	font-weight: 800;
 	color: #2c3e50;
 	line-height: 1.1;
 }

 .sms-teacher-stat-label {
 	font-size: 12px;
 	color: #6c757d;
 	font-weight: 700;
 	text-transform: uppercase;
 	letter-spacing: 0.4px;
 }

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

 .sms-panel-header h2 {
 	margin: 0;
 	font-size: 15px;
 	font-weight: 800;
 	color: #fff;
 }

 .sms-panel-body {
 	padding: 18px;
 }

 .sms-teachers-search {
 	display: flex;
 	gap: 10px;
 	flex-wrap: wrap;
 	justify-content: flex-end;
 	margin: 0;
 }

 .sms-teachers-search input[type="search"] {
 	min-width: 260px;
 	padding: 10px 12px;
 	border: 1px solid #dee2e6;
 	border-radius: 10px;
 }
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

 .sms-teachers-table-wrap {
 	overflow-x: auto;
 	-webkit-overflow-scrolling: touch;
 }

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

 .sms-status-pill.active {
 	background: rgba(40, 167, 69, 0.12);
 	color: #155724;
 	border-color: rgba(40, 167, 69, 0.28);
 }

 .sms-status-pill.inactive {
 	background: rgba(220, 53, 69, 0.12);
 	color: #721c24;
 	border-color: rgba(220, 53, 69, 0.28);
 }

 .sms-row-actions {
 	display: inline-flex;
 	gap: 8px;
 	flex-wrap: wrap;
 }

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

 .sms-row-action-btn:hover {
 	box-shadow: 0 6px 16px rgba(0,0,0,0.08);
 	transform: translateY(-1px);
 }

 .sms-row-action-btn.edit { border-color: rgba(102, 126, 234, 0.35); color: #4b5bdc; }
 .sms-row-action-btn.delete { border-color: rgba(220, 53, 69, 0.35); color: #dc3545; }

/* Responsive styles for the teachers list */
@media screen and (max-width: 782px) {
	.wp-list-table.teachers-table {
		border: 0;
	}
	.wp-list-table.teachers-table thead {
		display: none;
	}
	.wp-list-table.teachers-table tr {
		margin-bottom: 20px;
		display: block;
		border: 1px solid #ddd;
		border-radius: 4px;
		box-shadow: 0 1px 1px rgba(0,0,0,.04);
	}
	.wp-list-table.teachers-table td {
		display: block;
		text-align: right;
		border-bottom: 1px solid #eee;
		padding-right: 15px;
	}
	.wp-list-table.teachers-table td:last-child {
		border-bottom: 0;
	}
	.wp-list-table.teachers-table td::before {
		content: attr(data-label);
		float: left;
		font-weight: bold;
	}

	.sms-teachers-header {
		flex-direction: column;
		align-items: flex-start;
	}

	.sms-teachers-header-actions {
		width: 100%;
		justify-content: flex-start;
	}

	.sms-teacher-stats {
		grid-template-columns: 1fr;
	}

	.sms-teachers-search {
		justify-content: flex-start;
	}

	.sms-teachers-search input[type="search"] {
		min-width: 0;
		width: 100%;
	}
}
</style>
<div class="wrap">
	<div class="sms-teachers-page">
		<div class="sms-teachers-header">
			<div class="sms-teachers-title">
				<h1><?php esc_html_e( 'Teachers', 'school-management-system' ); ?></h1>
				<div class="sms-teachers-subtitle"><?php esc_html_e( 'Manage teacher profiles, employee IDs, and status.', 'school-management-system' ); ?></div>
			</div>
			<div class="sms-teachers-header-actions">
				<a class="sms-cta-btn" href="#sms-teacher-form">
					<span class="dashicons dashicons-plus-alt"></span>
					<?php echo $is_edit ? esc_html__( 'Edit Mode', 'school-management-system' ) : esc_html__( 'Add Teacher', 'school-management-system' ); ?>
				</a>
				<?php if ( $is_edit ) : ?>
					<a class="sms-cta-btn" href="<?php echo esc_url( admin_url( 'admin.php?page=sms-teachers' ) ); ?>">
						<span class="dashicons dashicons-no"></span>
						<?php esc_html_e( 'Cancel', 'school-management-system' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>

	<?php if ( ! empty( $message ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
	<?php endif; ?>

		<div class="sms-teacher-stats">
			<div class="sms-teacher-stat">
				<div class="sms-teacher-stat-icon total"><span class="dashicons dashicons-businessman"></span></div>
				<div>
					<div class="sms-teacher-stat-number"><?php echo intval( $total_teachers ); ?></div>
					<div class="sms-teacher-stat-label"><?php esc_html_e( 'Total Teachers', 'school-management-system' ); ?></div>
				</div>
			</div>
			<div class="sms-teacher-stat">
				<div class="sms-teacher-stat-icon active"><span class="dashicons dashicons-yes-alt"></span></div>
				<div>
					<div class="sms-teacher-stat-number"><?php echo intval( $active_teachers ); ?></div>
					<div class="sms-teacher-stat-label"><?php esc_html_e( 'Active', 'school-management-system' ); ?></div>
				</div>
			</div>
			<div class="sms-teacher-stat">
				<div class="sms-teacher-stat-icon inactive"><span class="dashicons dashicons-minus"></span></div>
				<div>
					<div class="sms-teacher-stat-number"><?php echo intval( $inactive_teachers ); ?></div>
					<div class="sms-teacher-stat-label"><?php esc_html_e( 'Inactive', 'school-management-system' ); ?></div>
				</div>
			</div>
		</div>

	<!-- Add/Edit Form -->
	<div class="sms-panel" id="sms-teacher-form">
		<div class="sms-panel-header">
			<h2>
			<?php
			if ( $is_edit ) {
				esc_html_e( 'Edit Teacher', 'school-management-system' );
			} else {
				esc_html_e( 'Add New Teacher', 'school-management-system' );
			}
			?>
			</h2>
		</div>

		<div class="sms-panel-body">
		<form method="post" action="">
			<?php wp_nonce_field( 'sms_nonce_form', 'sms_nonce' ); ?>

			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="first_name"><?php esc_html_e( 'Full Name', 'school-management-system' ); ?></label>
						</th>
						<td>
							<input type="text" name="first_name" id="first_name" class="regular-text" value="<?php echo esc_attr( $full_name ); ?>" required>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="email"><?php esc_html_e( 'Email', 'school-management-system' ); ?></label>
						</th>
						<td>
							<input type="email" name="email" id="email" class="regular-text" value="<?php echo esc_attr( $email ); ?>" required>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="phone"><?php esc_html_e( 'Phone', 'school-management-system' ); ?></label>
						</th>
						<td>
							<input type="text" name="phone" id="phone" class="regular-text" value="<?php echo esc_attr( $phone ); ?>">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="employee_id"><?php esc_html_e( 'Employee ID', 'school-management-system' ); ?></label>
						</th>
						<td>
							<input type="text" name="employee_id" id="employee_id" class="regular-text" value="<?php echo esc_attr( $employee_id ); ?>" placeholder="<?php esc_attr_e( 'Auto-generated if empty', 'school-management-system' ); ?>">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="qualifications"><?php esc_html_e( 'Qualifications', 'school-management-system' ); ?></label>
						</th>
						<td>
							<textarea name="qualifications" id="qualifications" class="large-text" rows="5"><?php echo esc_textarea( $qualifications ); ?></textarea>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="status"><?php esc_html_e( 'Status', 'school-management-system' ); ?></label>
						</th>
						<td>
							<select name="status" id="status">
								<option value="active" <?php selected( $status, 'active' ); ?>><?php esc_html_e( 'Active', 'school-management-system' ); ?></option>
								<option value="inactive" <?php selected( $status, 'inactive' ); ?>><?php esc_html_e( 'Inactive', 'school-management-system' ); ?></option>
							</select>
						</td>
					</tr>
				</tbody>
			</table>

			<?php
			if ( $is_edit ) {
				?>
				<input type="hidden" name="teacher_id" value="<?php echo intval( $teacher->id ); ?>" />
				<button type="submit" name="sms_edit_teacher" class="button button-primary">
					<?php esc_html_e( 'Update Teacher', 'school-management-system' ); ?>
				</button>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=sms-teachers' ) ); ?>" class="button">
					<?php esc_html_e( 'Cancel', 'school-management-system' ); ?>
				</a>
				<?php
			} else {
				?>
				<button type="submit" name="sms_add_teacher" class="button button-primary">
					<?php esc_html_e( 'Add Teacher', 'school-management-system' ); ?>
				</button>
				<?php
			}
			?>
		</form>
		</div>
	</div>

	<!-- Teachers List -->
	<div class="sms-panel">
		<div class="sms-panel-header">
			<h2><?php esc_html_e( 'Teachers List', 'school-management-system' ); ?></h2>
			<form method="get" action="" class="sms-teachers-search">
				<input type="hidden" name="page" value="sms-teachers" />
				<input type="search" name="s" value="<?php echo isset( $_GET['s'] ) ? esc_attr( $_GET['s'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'Search by name, email, or ID...', 'school-management-system' ); ?>" />
				<button type="submit" class="sms-search-btn"><?php esc_html_e( 'Search', 'school-management-system' ); ?></button>
				<?php if ( ! empty( $_GET['s'] ) ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=sms-teachers' ) ); ?>" class="sms-reset-btn"><?php esc_html_e( 'Reset', 'school-management-system' ); ?></a>
				<?php endif; ?>
			</form>
		</div>
		<div class="sms-panel-body">

	<form method="post" action="">
	<?php wp_nonce_field( 'sms_bulk_delete_teachers_nonce', 'sms_bulk_delete_teachers_nonce' ); ?>
	<div class="tablenav top">
		<div class="alignleft actions bulkactions">
			<select name="action">
				<option value="-1"><?php esc_html_e( 'Bulk Actions', 'school-management-system' ); ?></option>
				<option value="bulk_delete_teachers"><?php esc_html_e( 'Delete', 'school-management-system' ); ?></option>
			</select>
			<input type="submit" class="button action" value="<?php esc_attr_e( 'Apply', 'school-management-system' ); ?>">
		</div>
	</div>
	<div class="sms-teachers-table-wrap">
	<table class="wp-list-table widefat fixed striped teachers-table">
		<thead>
			<tr>
				<td id="cb" class="manage-column column-cb check-column"><input id="cb-select-all-teachers" type="checkbox"></td>
				<th><?php esc_html_e( 'ID', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Name', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Employee ID', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Email', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Phone', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Qualifications', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Status', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'school-management-system' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$search_term = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
			if ( ! empty( $search_term ) ) {
				$teachers = Teacher::search( $search_term );
			} else {
				$teachers = Teacher::get_all( array(), 50 );
			}
			if ( ! empty( $teachers ) ) {
				foreach ( $teachers as $teacher ) {
					?>
					<tr class="teacher-row">
						<th scope="row" class="check-column"><input type="checkbox" name="teacher_ids[]" value="<?php echo intval( $teacher->id ); ?>"></th>
						<td data-label="<?php esc_attr_e( 'ID', 'school-management-system' ); ?>"><?php echo intval( $teacher->id ); ?></td>
						<td data-label="<?php esc_attr_e( 'Name', 'school-management-system' ); ?>"><?php echo esc_html( trim( $teacher->first_name . ' ' . $teacher->last_name ) ); ?></td>
						<td data-label="<?php esc_attr_e( 'Employee ID', 'school-management-system' ); ?>"><?php echo esc_html( $teacher->employee_id ); ?></td>
						<td data-label="<?php esc_attr_e( 'Email', 'school-management-system' ); ?>"><?php echo esc_html( $teacher->email ); ?></td>
						<td data-label="<?php esc_attr_e( 'Phone', 'school-management-system' ); ?>"><?php echo esc_html( $teacher->phone ); ?></td>
						<td data-label="<?php esc_attr_e( 'Qualifications', 'school-management-system' ); ?>"><?php echo esc_html( $teacher->qualification ); ?></td>
						<td data-label="<?php esc_attr_e( 'Status', 'school-management-system' ); ?>">
							<span class="sms-status-pill <?php echo 'inactive' === $teacher->status ? 'inactive' : 'active'; ?>">
								<?php echo esc_html( $teacher->status ); ?>
							</span>
						</td>
						<td data-label="<?php esc_attr_e( 'Actions', 'school-management-system' ); ?>">
							<div class="sms-row-actions">
								<a class="sms-row-action-btn edit" href="<?php echo esc_url( admin_url( 'admin.php?page=sms-teachers&action=edit&id=' . intval( $teacher->id ) . '#sms-teacher-form' ) ); ?>">
									<span class="dashicons dashicons-edit"></span>
									<?php esc_html_e( 'Edit', 'school-management-system' ); ?>
								</a>
								<a class="sms-row-action-btn delete sms-delete-teacher" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=sms-teachers&action=delete&id=' . intval( $teacher->id ) ), 'sms_delete_teacher_nonce' ) ); ?>">
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
					<td colspan="9"><?php esc_html_e( 'No teachers found', 'school-management-system' ); ?></td>
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
		$(document).on('click', 'a.sms-delete-teacher', function(e) {
			if (!window.confirm('<?php echo esc_js( __( 'Are you sure?', 'school-management-system' ) ); ?>')) {
				e.preventDefault();
			}
		});
	});
	</script>
	</div>
</div>
