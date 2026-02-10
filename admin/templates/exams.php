<?php
/**
 * Exams admin template.
 *
 * @package School_Management_System
 */

use School_Management_System\Exam;
use School_Management_System\Classm;

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'Unauthorized', 'school-management-system' ) );
}

$exam = null;
$is_edit = false;
$action = sanitize_text_field( $_GET['action'] ?? '' );
$exam_id = intval( $_GET['id'] ?? 0 );

if ( 'edit' === $action && $exam_id ) {
	$exam = Exam::get( $exam_id );
	if ( ! $exam ) {
		wp_die( esc_html__( 'Exam not found', 'school-management-system' ) );
	}
	$is_edit = true;
}

$total_exams = Exam::count();
$scheduled_exams = Exam::count( array( 'status' => 'scheduled' ) );
$completed_exams = Exam::count( array( 'status' => 'completed' ) );
$cancelled_exams = Exam::count( array( 'status' => 'cancelled' ) );

?>
<style>
 .sms-exams-page { max-width: 100%; }
 .sms-exams-header {
 	display: flex;
 	justify-content: space-between;
 	align-items: flex-start;
 	gap: 16px;
 	background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
 	color: #fff;
 	padding: 22px;
 	border-radius: 16px;
 	box-shadow: 0 10px 30px rgba(240, 147, 251, 0.22);
 	margin: 10px 0 18px;
 }
 .sms-exams-title h1 { margin: 0; color: #fff; font-size: 22px; line-height: 1.2; }
 .sms-exams-subtitle { margin: 6px 0 0; opacity: 0.92; font-size: 13px; }
 .sms-exams-header-actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }
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

 .sms-exam-stats {
 	display: grid;
 	grid-template-columns: repeat(4, minmax(0, 1fr));
 	gap: 16px;
 	margin-bottom: 18px;
 }
 .sms-exam-stat {
 	background: #fff;
 	border-radius: 16px;
 	padding: 18px;
 	box-shadow: 0 8px 22px rgba(0,0,0,0.08);
 	border: 1px solid #eef1f5;
 	display: flex;
 	align-items: center;
 	gap: 14px;
 }
 .sms-exam-stat-icon {
 	width: 44px;
 	height: 44px;
 	border-radius: 12px;
 	display: flex;
 	align-items: center;
 	justify-content: center;
 	color: #fff;
 }
 .sms-exam-stat-icon.total { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
 .sms-exam-stat-icon.scheduled { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
 .sms-exam-stat-icon.completed { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
 .sms-exam-stat-icon.cancelled { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
 .sms-exam-stat-icon .dashicons { font-size: 20px; width: 20px; height: 20px; }
 .sms-exam-stat-number { font-size: 20px; font-weight: 800; color: #2c3e50; line-height: 1.1; }
 .sms-exam-stat-label { font-size: 12px; color: #6c757d; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; }

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

 .sms-exams-search {
 	display: flex;
 	gap: 10px;
 	flex-wrap: wrap;
 	justify-content: flex-end;
 	margin: 0;
 }
 .sms-exams-search input[type="search"] { min-width: 260px; padding: 10px 12px; border: 1px solid #dee2e6; border-radius: 10px; }
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
 .sms-exams-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }

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
 .sms-status-pill.scheduled { background: rgba(79, 172, 254, 0.12); color: #0056b3; border-color: rgba(79, 172, 254, 0.28); }
 .sms-status-pill.completed { background: rgba(67, 233, 123, 0.12); color: #155724; border-color: rgba(67, 233, 123, 0.28); }
 .sms-status-pill.cancelled { background: rgba(250, 112, 154, 0.12); color: #721c24; border-color: rgba(250, 112, 154, 0.28); }

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
 	.sms-exams-header { flex-direction: column; align-items: flex-start; }
 	.sms-exams-header-actions { width: 100%; justify-content: flex-start; }
 	.sms-exam-stats { grid-template-columns: 1fr; }
 	.sms-exams-search { justify-content: flex-start; }
 	.sms-exams-search input[type="search"] { min-width: 0; width: 100%; }
 }
</style>
<div class="wrap">
	<div class="sms-exams-page">
		<div class="sms-exams-header">
			<div class="sms-exams-title">
				<h1><?php esc_html_e( 'Exams', 'school-management-system' ); ?></h1>
				<div class="sms-exams-subtitle"><?php esc_html_e( 'Schedule and manage exams, track dates and status.', 'school-management-system' ); ?></div>
			</div>
			<div class="sms-exams-header-actions">
				<a class="sms-cta-btn" href="#sms-exam-form">
					<span class="dashicons dashicons-plus-alt"></span>
					<?php echo $is_edit ? esc_html__( 'Edit Mode', 'school-management-system' ) : esc_html__( 'Add Exam', 'school-management-system' ); ?>
				</a>
				<?php if ( $is_edit ) : ?>
					<a class="sms-cta-btn" href="<?php echo esc_url( admin_url( 'admin.php?page=sms-exams' ) ); ?>">
						<span class="dashicons dashicons-no"></span>
						<?php esc_html_e( 'Cancel', 'school-management-system' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>

		<div class="sms-exam-stats">
			<div class="sms-exam-stat">
				<div class="sms-exam-stat-icon total"><span class="dashicons dashicons-clipboard"></span></div>
				<div>
					<div class="sms-exam-stat-number"><?php echo intval( $total_exams ); ?></div>
					<div class="sms-exam-stat-label"><?php esc_html_e( 'Total Exams', 'school-management-system' ); ?></div>
				</div>
			</div>
			<div class="sms-exam-stat">
				<div class="sms-exam-stat-icon scheduled"><span class="dashicons dashicons-clock"></span></div>
				<div>
					<div class="sms-exam-stat-number"><?php echo intval( $scheduled_exams ); ?></div>
					<div class="sms-exam-stat-label"><?php esc_html_e( 'Scheduled', 'school-management-system' ); ?></div>
				</div>
			</div>
			<div class="sms-exam-stat">
				<div class="sms-exam-stat-icon completed"><span class="dashicons dashicons-yes-alt"></span></div>
				<div>
					<div class="sms-exam-stat-number"><?php echo intval( $completed_exams ); ?></div>
					<div class="sms-exam-stat-label"><?php esc_html_e( 'Completed', 'school-management-system' ); ?></div>
				</div>
			</div>
			<div class="sms-exam-stat">
				<div class="sms-exam-stat-icon cancelled"><span class="dashicons dashicons-no"></span></div>
				<div>
					<div class="sms-exam-stat-number"><?php echo intval( $cancelled_exams ); ?></div>
					<div class="sms-exam-stat-label"><?php esc_html_e( 'Cancelled', 'school-management-system' ); ?></div>
				</div>
			</div>
		</div>

	<!-- Add/Edit Form -->
		<div class="sms-panel" id="sms-exam-form">
			<div class="sms-panel-header">
				<h2><?php echo $is_edit ? esc_html__( 'Edit Exam', 'school-management-system' ) : esc_html__( 'Add New Exam', 'school-management-system' ); ?></h2>
			</div>
			<div class="sms-panel-body">

		<form method="post" action="">
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="exam_name"><?php esc_html_e( 'Exam Name *', 'school-management-system' ); ?></label>
					</th>
					<td>
						<input type="text" name="exam_name" id="exam_name" required value="<?php echo $exam ? esc_attr( $exam->exam_name ) : ''; ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="exam_code"><?php esc_html_e( 'Exam Code *', 'school-management-system' ); ?></label>
					</th>
					<td>
						<input type="text" name="exam_code" id="exam_code" required value="<?php echo $exam ? esc_attr( $exam->exam_code ) : ''; ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="class_id"><?php esc_html_e( 'Class *', 'school-management-system' ); ?></label>
					</th>
					<td>
						<select name="class_id" id="class_id" required>
							<option value=""><?php esc_html_e( 'Select Class', 'school-management-system' ); ?></option>
							<?php
							$classes = Classm::get_all( array(), 100 );
							foreach ( $classes as $class ) {
								?>
								<option value="<?php echo intval( $class->id ); ?>" <?php echo $exam && $exam->class_id === $class->id ? 'selected' : ''; ?>>
									<?php echo esc_html( $class->class_name ); ?>
								</option>
								<?php
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="exam_date"><?php esc_html_e( 'Exam Date *', 'school-management-system' ); ?></label>
					</th>
					<td>
						<input type="date" name="exam_date" id="exam_date" required value="<?php echo $exam ? esc_attr( $exam->exam_date ) : ''; ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="total_marks"><?php esc_html_e( 'Total Marks', 'school-management-system' ); ?></label>
					</th>
					<td>
						<input type="number" name="total_marks" id="total_marks" value="<?php echo $exam ? intval( $exam->total_marks ) : '100'; ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="passing_marks"><?php esc_html_e( 'Passing Marks', 'school-management-system' ); ?></label>
					</th>
					<td>
						<input type="number" name="passing_marks" id="passing_marks" value="<?php echo $exam ? intval( $exam->passing_marks ) : '40'; ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="status"><?php esc_html_e( 'Status', 'school-management-system' ); ?></label>
					</th>
					<td>
						<select name="status" id="status">
							<option value="scheduled" <?php echo ! $exam || 'scheduled' === $exam->status ? 'selected' : ''; ?>>
								<?php esc_html_e( 'Scheduled', 'school-management-system' ); ?>
							</option>
							<option value="completed" <?php echo $exam && 'completed' === $exam->status ? 'selected' : ''; ?>>
								<?php esc_html_e( 'Completed', 'school-management-system' ); ?>
							</option>
							<option value="cancelled" <?php echo $exam && 'cancelled' === $exam->status ? 'selected' : ''; ?>>
								<?php esc_html_e( 'Cancelled', 'school-management-system' ); ?>
							</option>
						</select>
					</td>
				</tr>
			</table>

			<?php if ( $is_edit ) : ?>
				<input type="hidden" name="exam_id" value="<?php echo intval( $exam->id ); ?>" />
				<button type="submit" name="sms_edit_exam" class="button button-primary">
					<?php esc_html_e( 'Update Exam', 'school-management-system' ); ?>
				</button>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=sms-exams' ) ); ?>" class="button">
					<?php esc_html_e( 'Cancel', 'school-management-system' ); ?>
				</a>
			<?php else : ?>
				<button type="submit" name="sms_add_exam" class="button button-primary">
					<?php esc_html_e( 'Add Exam', 'school-management-system' ); ?>
				</button>
			<?php endif; ?>
		</form>
			</div>
		</div>

	<!-- Exams List -->
		<div class="sms-panel">
			<div class="sms-panel-header">
				<h2><?php esc_html_e( 'Exams List', 'school-management-system' ); ?></h2>
				<form method="get" action="" class="sms-exams-search">
					<input type="hidden" name="page" value="sms-exams" />
					<input type="search" name="s" value="<?php echo isset( $_GET['s'] ) ? esc_attr( $_GET['s'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'Search exams...', 'school-management-system' ); ?>" />
					<button type="submit" class="sms-search-btn"><?php esc_html_e( 'Search', 'school-management-system' ); ?></button>
					<?php if ( ! empty( $_GET['s'] ) ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=sms-exams' ) ); ?>" class="sms-reset-btn"><?php esc_html_e( 'Reset', 'school-management-system' ); ?></a>
					<?php endif; ?>
				</form>
			</div>
			<div class="sms-panel-body">

	<div class="sms-exams-table-wrap">
	<table class="wp-list-table widefat fixed striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'ID', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Exam Name', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Class', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Exam Code', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Date', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Status', 'school-management-system' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'school-management-system' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$search_term = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
			if ( ! empty( $search_term ) ) {
				$exams = Exam::search( $search_term );
			} else {
				$exams = Exam::get_all( array(), 50 );
			}
			if ( ! empty( $exams ) ) {
				foreach ( $exams as $exam ) {
					$class = Classm::get( $exam->class_id );
					?>
					<tr>
						<td><?php echo intval( $exam->id ); ?></td>
						<td><?php echo esc_html( $exam->exam_name ); ?></td>
						<td><?php echo $class ? esc_html( $class->class_name ) : ''; ?></td>
						<td><?php echo esc_html( $exam->exam_code ); ?></td>
						<td><?php echo esc_html( $exam->exam_date ); ?></td>
						<td>
							<span class="sms-status-pill <?php echo esc_attr( $exam->status ); ?>">
								<?php echo esc_html( $exam->status ); ?>
							</span>
						</td>
						<td>
							<div class="sms-row-actions">
								<a class="sms-row-action-btn edit" href="<?php echo esc_url( admin_url( 'admin.php?page=sms-exams&action=edit&id=' . $exam->id ) ); ?>">
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
					<td colspan="7"><?php esc_html_e( 'No exams found', 'school-management-system' ); ?></td>
				</tr>
				<?php
			}
			?>
		</tbody>
	</table>
	</div>
			</div>
		</div>
	</div>
</div>
