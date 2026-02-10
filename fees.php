<?php
/**
 * Fees admin template.
 *
 * @package School_Management_System
 */

use School_Management_System\Fee;
use School_Management_System\Student;
use School_Management_System\Classm;

// Check user capability.
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'Unauthorized', 'school-management-system' ) );
}

$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
$fee_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
$fee    = null;

if ( 'edit' === $action && $fee_id ) {
	$fee = Fee::get( $fee_id );
}

// Handle messages
$message = '';
$msg_type = 'success';
if ( isset( $_GET['sms_message'] ) ) {
	$msg_code = sanitize_text_field( $_GET['sms_message'] );
	if ( 'fee_added' === $msg_code ) {
		$message = __( 'Fee added successfully.', 'school-management-system' );
	} elseif ( 'fee_updated' === $msg_code ) {
		$message = __( 'Fee updated successfully.', 'school-management-system' );
	} elseif ( 'fee_deleted' === $msg_code ) {
		$message = __( 'Fee deleted successfully.', 'school-management-system' );
	} elseif ( 'fee_add_error' === $msg_code ) {
		$error = isset( $_GET['error'] ) ? urldecode( $_GET['error'] ) : '';
		$message = sprintf( __( 'Error adding fee: %s', 'school-management-system' ), $error );
		$msg_type = 'error';
	}
}

// Get stats
$total_fees_count = Fee::count();
$paid_fees_count = Fee::count( array( 'status' => 'paid' ) );
$pending_fees_count = Fee::count( array( 'status' => 'pending' ) );

?>
<style>
	/* Main Layout & Header */
	.sms-fees-wrapper { max-width: 100%; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; }
	.sms-page-header {
		background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
		color: #fff;
		padding: 25px;
		border-radius: 16px;
		box-shadow: 0 10px 30px rgba(118, 75, 162, 0.25);
		margin: 10px 0 25px;
		display: flex;
		justify-content: space-between;
		align-items: center;
	}
	.sms-page-title h1 { margin: 0; color: #fff; font-size: 24px; font-weight: 700; }
	.sms-page-subtitle { opacity: 0.9; font-size: 14px; margin-top: 5px; }
	
	/* Stats Cards */
	.sms-stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 25px; }
	.sms-stat-card { background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #eef1f5; display: flex; align-items: center; gap: 15px; }
	.sms-stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #fff; }
	.sms-stat-info h3 { margin: 0; font-size: 24px; color: #2d3436; }
	.sms-stat-info p { margin: 0; font-size: 12px; color: #636e72; text-transform: uppercase; font-weight: 600; }
	
	/* Form Section (The requested update) */
	.sms-form-section {
		background: #fff;
		border-radius: 16px;
		box-shadow: 0 8px 30px rgba(0,0,0,0.04);
		border: 1px solid #eef1f5;
		overflow: hidden;
		margin-bottom: 30px;
		transition: all 0.3s ease;
	}
	.sms-form-header {
		padding: 20px 25px;
		border-bottom: 1px solid #f5f6fa;
		background: #fcfcfd;
		display: flex;
		justify-content: space-between;
		align-items: center;
	}
	.sms-form-header h2 { margin: 0; font-size: 18px; color: #2d3436; display: flex; align-items: center; gap: 10px; }
	.sms-form-body { padding: 30px; }
	
	.sms-form-grid {
		display: grid;
		grid-template-columns: repeat(3, 1fr);
		gap: 25px;
	}
	@media (max-width: 1000px) { .sms-form-grid { grid-template-columns: repeat(2, 1fr); } }
	@media (max-width: 600px) { .sms-form-grid { grid-template-columns: 1fr; } }

	.sms-form-group { margin-bottom: 5px; }
	.sms-form-group label { display: block; font-weight: 600; color: #2d3436; margin-bottom: 8px; font-size: 13px; }
	.sms-form-control {
		width: 100%;
		padding: 12px 15px;
		border: 1px solid #dfe6e9;
		border-radius: 8px;
		font-size: 14px;
		color: #2d3436;
		transition: all 0.2s;
		background: #fdfdfd;
	}
	.sms-form-control:focus {
		border-color: #667eea;
		background: #fff;
		box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
		outline: none;
	}
	textarea.sms-form-control { resize: vertical; min-height: 80px; }
	
	.sms-form-actions {
		margin-top: 30px;
		padding-top: 20px;
		border-top: 1px solid #f5f6fa;
		display: flex;
		justify-content: flex-end;
		gap: 15px;
	}
	.sms-btn {
		padding: 12px 24px;
		border-radius: 8px;
		font-weight: 600;
		font-size: 14px;
		cursor: pointer;
		border: none;
		transition: all 0.2s;
		display: inline-flex;
		align-items: center;
		gap: 8px;
		text-decoration: none;
	}
	.sms-btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; box-shadow: 0 4px 15px rgba(118, 75, 162, 0.3); }
	.sms-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(118, 75, 162, 0.4); color: #fff; }
	.sms-btn-secondary { background: #f1f2f6; color: #2d3436; }
	.sms-btn-secondary:hover { background: #dfe4ea; color: #2d3436; }

	/* Table Styles */
	.sms-table-card { background: #fff; border-radius: 16px; box-shadow: 0 8px 30px rgba(0,0,0,0.04); border: 1px solid #eef1f5; overflow: hidden; }
	.sms-status-badge { padding: 5px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
	.status-paid { background: rgba(46, 204, 113, 0.15); color: #27ae60; }
	.status-pending { background: rgba(255, 107, 107, 0.15); color: #ee5253; }
	.status-partial { background: rgba(253, 203, 110, 0.15); color: #e1b12c; }

	/* Notice Section */
	.sms-notice-card {
		background: #fff;
		border-left: 5px solid #00b894;
		border-radius: 12px;
		box-shadow: 0 5px 20px rgba(0,0,0,0.05);
		padding: 20px;
		margin-bottom: 30px;
		display: flex;
		align-items: center;
		gap: 20px;
		position: relative;
		animation: slideIn 0.4s ease-out;
		border: 1px solid #eef1f5;
		border-left-width: 5px;
	}
	.sms-notice-card.error { border-left-color: #ff7675; }
	.sms-notice-icon {
		width: 48px;
		height: 48px;
		border-radius: 50%;
		background: rgba(0, 184, 148, 0.1);
		color: #00b894;
		display: flex;
		align-items: center;
		justify-content: center;
		font-size: 24px;
		flex-shrink: 0;
	}
	.sms-notice-card.error .sms-notice-icon { background: rgba(255, 118, 117, 0.1); color: #ff7675; }
	.sms-notice-content { flex: 1; }
	.sms-notice-content h4 { margin: 0 0 5px; font-size: 16px; color: #2d3436; font-weight: 700; }
	.sms-notice-content p { margin: 0; font-size: 14px; color: #636e72; line-height: 1.5; }
	.sms-notice-dismiss {
		color: #b2bec3;
		cursor: pointer;
		background: none;
		border: none;
		font-size: 20px;
		padding: 5px;
		transition: color 0.2s;
	}
	.sms-notice-dismiss:hover { color: #2d3436; }
	@keyframes slideIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="wrap sms-fees-wrapper">
	
	<!-- Header -->
	<div class="sms-page-header">
		<div class="sms-page-title">
			<h1><?php esc_html_e( 'Fees Management', 'school-management-system' ); ?></h1>
			<div class="sms-page-subtitle"><?php esc_html_e( 'Manage student fees, payments, and invoices.', 'school-management-system' ); ?></div>
		</div>
		<div>
			<button type="button" class="sms-btn sms-btn-secondary" onclick="document.getElementById('add-fee-form').scrollIntoView({behavior: 'smooth'});">
				<span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e( 'Add New Fee', 'school-management-system' ); ?>
			</button>
		</div>
	</div>

	<?php if ( ! empty( $message ) ) : ?>
		<div class="sms-notice-card <?php echo 'error' === $msg_type ? 'error' : ''; ?>">
			<div class="sms-notice-icon">
				<span class="dashicons dashicons-<?php echo 'error' === $msg_type ? 'warning' : 'yes'; ?>"></span>
			</div>
			<div class="sms-notice-content">
				<h4><?php echo 'error' === $msg_type ? esc_html__( 'Error', 'school-management-system' ) : esc_html__( 'Success', 'school-management-system' ); ?></h4>
				<p><?php echo esc_html( $message ); ?></p>
			</div>
			<button type="button" class="sms-notice-dismiss" onclick="this.parentElement.style.display='none';">
				<span class="dashicons dashicons-dismiss"></span>
			</button>
		</div>
	<?php endif; ?>

	<!-- Stats -->
	<div class="sms-stats-row">
		<div class="sms-stat-card">
			<div class="sms-stat-icon" style="background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%);"><span class="dashicons dashicons-money-alt"></span></div>
			<div class="sms-stat-info">
				<h3><?php echo intval( $total_fees_count ); ?></h3>
				<p><?php esc_html_e( 'Total Records', 'school-management-system' ); ?></p>
			</div>
		</div>
		<div class="sms-stat-card">
			<div class="sms-stat-icon" style="background: linear-gradient(135deg, #00b894 0%, #55efc4 100%);"><span class="dashicons dashicons-yes"></span></div>
			<div class="sms-stat-info">
				<h3><?php echo intval( $paid_fees_count ); ?></h3>
				<p><?php esc_html_e( 'Paid Fees', 'school-management-system' ); ?></p>
			</div>
		</div>
		<div class="sms-stat-card">
			<div class="sms-stat-icon" style="background: linear-gradient(135deg, #ff7675 0%, #fab1a0 100%);"><span class="dashicons dashicons-clock"></span></div>
			<div class="sms-stat-info">
				<h3><?php echo intval( $pending_fees_count ); ?></h3>
				<p><?php esc_html_e( 'Pending Fees', 'school-management-system' ); ?></p>
			</div>
		</div>
	</div>

	<!-- Add/Edit Form -->
	<div id="add-fee-form" class="sms-form-section">
		<div class="sms-form-header">
			<h2>
				<span class="dashicons dashicons-edit"></span> 
				<?php echo $fee ? esc_html__( 'Edit Fee Details', 'school-management-system' ) : esc_html__( 'Add New Fee', 'school-management-system' ); ?>
			</h2>
			<?php if ( $fee ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=sms-fees' ) ); ?>" class="sms-btn sms-btn-secondary" style="padding: 8px 15px; font-size: 12px;"><?php esc_html_e( 'Cancel Edit', 'school-management-system' ); ?></a>
			<?php endif; ?>
		</div>
		<div class="sms-form-body">
			<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=sms-fees' ) ); ?>">
				<?php wp_nonce_field( 'sms_nonce_form', 'sms_nonce' ); ?>
				<?php if ( $fee ) : ?>
					<input type="hidden" name="sms_edit_fee" value="1">
					<input type="hidden" name="fee_id" value="<?php echo intval( $fee->id ); ?>">
				<?php else : ?>
					<input type="hidden" name="sms_add_fee" value="1">
				<?php endif; ?>

				<div class="sms-form-grid">
					<!-- Student Selection -->
					<div class="sms-form-group">
						<label for="student_id"><?php esc_html_e( 'Select Student', 'school-management-system' ); ?> <span style="color:red">*</span></label>
						<select name="student_id" id="student_id" class="sms-form-control" required>
							<option value=""><?php esc_html_e( 'Select Student', 'school-management-system' ); ?></option>
							<?php
							// If editing, pre-select. If adding, we might want to load students via AJAX or limit list.
							// For now, loading first 100 or all if reasonable.
							$students = Student::get_all( array( 'status' => 'active' ), 1000 );
							if ( ! empty( $students ) ) {
								foreach ( $students as $student ) {
									// Get class for data attribute
									$enrollments = \School_Management_System\Enrollment::get_student_enrollments( $student->id );
									$class_id = ! empty( $enrollments ) ? $enrollments[0]->class_id : 0;
									
									$selected = ( $fee && $fee->student_id == $student->id ) || ( isset( $_GET['student_id'] ) && $_GET['student_id'] == $student->id ) ? 'selected' : '';
									echo '<option value="' . intval( $student->id ) . '" data-class-id="' . intval( $class_id ) . '" ' . $selected . '>' . esc_html( $student->first_name . ' ' . $student->last_name . ' (' . $student->roll_number . ')' ) . '</option>';
								}
							}
							?>
						</select>
					</div>

					<!-- Class (Auto-filled usually) -->
					<div class="sms-form-group">
						<label for="class_id"><?php esc_html_e( 'Class', 'school-management-system' ); ?></label>
						<select name="class_id" id="class_id" class="sms-form-control">
							<option value=""><?php esc_html_e( 'Select Class', 'school-management-system' ); ?></option>
							<?php
							$classes = Classm::get_all();
							if ( ! empty( $classes ) ) {
								foreach ( $classes as $class ) {
									$selected = ( $fee && $fee->class_id == $class->id ) ? 'selected' : '';
									echo '<option value="' . intval( $class->id ) . '" ' . $selected . '>' . esc_html( $class->class_name ) . '</option>';
								}
							}
							?>
						</select>
					</div>

					<!-- Fee Type -->
					<div class="sms-form-group">
						<label for="fee_type"><?php esc_html_e( 'Fee Type', 'school-management-system' ); ?> <span style="color:red">*</span></label>
						<select name="fee_type" id="fee_type" class="sms-form-control" required>
							<?php
							$fee_types = array( 'Tuition Fee', 'Exam Fee', 'Transport Fee', 'Library Fee', 'Admission Fee', 'Other' );
							foreach ( $fee_types as $type ) {
								$selected = ( $fee && $fee->fee_type == $type ) ? 'selected' : '';
								echo '<option value="' . esc_attr( $type ) . '" ' . $selected . '>' . esc_html( $type ) . '</option>';
							}
							?>
						</select>
					</div>

					<!-- Amount -->
					<div class="sms-form-group">
						<label for="amount"><?php esc_html_e( 'Total Amount', 'school-management-system' ); ?> <span style="color:red">*</span></label>
						<input type="number" step="0.01" name="amount" id="amount" class="sms-form-control" value="<?php echo $fee ? esc_attr( $fee->amount ) : ''; ?>" required placeholder="0.00">
					</div>

					<!-- Fee Month/Year (For Due Date) -->
					<div class="sms-form-group">
						<label><?php esc_html_e( 'Fee For Month', 'school-management-system' ); ?></label>
						<div style="display: flex; gap: 10px;">
							<select name="fee_month" class="sms-form-control">
								<?php
								$current_month = $fee ? date( 'n', strtotime( $fee->due_date ) ) : date( 'n' );
								for ( $m = 1; $m <= 12; $m++ ) {
									$selected = $m == $current_month ? 'selected' : '';
									echo '<option value="' . $m . '" ' . $selected . '>' . date( 'F', mktime( 0, 0, 0, $m, 1 ) ) . '</option>';
								}
								?>
							</select>
							<select name="fee_year" class="sms-form-control">
								<?php
								$current_year = $fee ? date( 'Y', strtotime( $fee->due_date ) ) : date( 'Y' );
								for ( $y = date( 'Y' ) - 1; $y <= date( 'Y' ) + 1; $y++ ) {
									$selected = $y == $current_year ? 'selected' : '';
									echo '<option value="' . $y . '" ' . $selected . '>' . $y . '</option>';
								}
								?>
							</select>
						</div>
					</div>

					<!-- Status -->
					<div class="sms-form-group">
						<label for="status"><?php esc_html_e( 'Payment Status', 'school-management-system' ); ?></label>
						<select name="status" id="status" class="sms-form-control">
							<option value="pending" <?php echo ( $fee && 'pending' === $fee->status ) ? 'selected' : ''; ?>><?php esc_html_e( 'Pending', 'school-management-system' ); ?></option>
							<option value="paid" <?php echo ( $fee && 'paid' === $fee->status ) ? 'selected' : ''; ?>><?php esc_html_e( 'Paid', 'school-management-system' ); ?></option>
							<option value="partially_paid" <?php echo ( $fee && 'partially_paid' === $fee->status ) ? 'selected' : ''; ?>><?php esc_html_e( 'Partially Paid', 'school-management-system' ); ?></option>
						</select>
					</div>

					<!-- Paid Amount (Conditional) -->
					<div class="sms-form-group" id="paid_amount_group" style="display: none;">
						<label for="paid_amount"><?php esc_html_e( 'Paid Amount', 'school-management-system' ); ?></label>
						<input type="number" step="0.01" name="paid_amount" id="paid_amount" class="sms-form-control" value="<?php echo $fee ? esc_attr( $fee->paid_amount ) : ''; ?>" placeholder="0.00">
					</div>

					<!-- Payment Date -->
					<div class="sms-form-group" id="payment_date_group" style="display: none;">
						<label for="payment_date"><?php esc_html_e( 'Payment Date', 'school-management-system' ); ?></label>
						<input type="date" name="payment_date" id="payment_date" class="sms-form-control" value="<?php echo $fee ? esc_attr( $fee->payment_date ) : date( 'Y-m-d' ); ?>">
					</div>
				</div>

				<div class="sms-form-group" style="margin-top: 20px;">
					<label for="remarks"><?php esc_html_e( 'Remarks / Notes', 'school-management-system' ); ?></label>
					<textarea name="remarks" id="remarks" class="sms-form-control" rows="2"><?php echo $fee ? esc_textarea( $fee->remarks ) : ''; ?></textarea>
				</div>

				<div class="sms-form-actions">
					<button type="submit" class="sms-btn sms-btn-primary">
						<span class="dashicons dashicons-saved"></span>
						<?php echo $fee ? esc_html__( 'Update Fee', 'school-management-system' ) : esc_html__( 'Save Fee Record', 'school-management-system' ); ?>
					</button>
				</div>
			</form>
		</div>
	</div>

	<!-- List Table -->
	<div class="sms-table-card">
		<div class="sms-form-header">
			<h2><span class="dashicons dashicons-list-view"></span> <?php esc_html_e( 'Fee Records', 'school-management-system' ); ?></h2>
			<form method="get" style="display: flex; gap: 10px;">
				<input type="hidden" name="page" value="sms-fees">
				<input type="text" name="s" placeholder="<?php esc_attr_e( 'Search...', 'school-management-system' ); ?>" class="sms-form-control" style="padding: 6px 12px; width: 200px;" value="<?php echo isset( $_GET['s'] ) ? esc_attr( $_GET['s'] ) : ''; ?>">
				<button type="submit" class="sms-btn sms-btn-secondary" style="padding: 6px 12px;"><?php esc_html_e( 'Search', 'school-management-system' ); ?></button>
			</form>
		</div>
		
		<div style="overflow-x: auto;">
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Voucher No', 'school-management-system' ); ?></th>
						<th><?php esc_html_e( 'Student', 'school-management-system' ); ?></th>
						<th><?php esc_html_e( 'Class', 'school-management-system' ); ?></th>
						<th><?php esc_html_e( 'Fee Type', 'school-management-system' ); ?></th>
						<th><?php esc_html_e( 'Amount', 'school-management-system' ); ?></th>
						<th><?php esc_html_e( 'Status', 'school-management-system' ); ?></th>
						<th><?php esc_html_e( 'Date', 'school-management-system' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'school-management-system' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					$search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
					// Assuming Fee::get_all supports search or we use a basic get_all
					$fees = Fee::get_all( array(), 20 ); // Pagination to be implemented
					
					if ( ! empty( $fees ) ) {
						foreach ( $fees as $f ) {
							$student = Student::get( $f->student_id );
							$class = Classm::get( $f->class_id );
							$status_class = 'status-' . str_replace( '_', '-', $f->status );
							
							// Filter by search if needed (basic PHP filter if DB search not ready)
							if ( $search && stripos( $student->first_name, $search ) === false && stripos( $f->fee_type, $search ) === false ) {
								continue;
							}
							?>
							<tr>
								<td>#<?php echo intval( $f->id ); ?></td>
								<td>
									<strong><?php echo $student ? esc_html( $student->first_name . ' ' . $student->last_name ) : 'N/A'; ?></strong><br>
									<small><?php echo $student ? esc_html( $student->roll_number ) : ''; ?></small>
								</td>
								<td><?php echo $class ? esc_html( $class->class_name ) : 'N/A'; ?></td>
								<td><?php echo esc_html( $f->fee_type ); ?></td>
								<td>
									<?php echo esc_html( number_format( $f->amount, 2 ) ); ?>
									<?php if ( $f->paid_amount > 0 && $f->paid_amount < $f->amount ) : ?>
										<br><small class="description"><?php printf( __( 'Paid: %s', 'sms' ), number_format( $f->paid_amount, 2 ) ); ?></small>
									<?php endif; ?>
								</td>
								<td><span class="sms-status-badge <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( ucfirst( str_replace( '_', ' ', $f->status ) ) ); ?></span></td>
								<td><?php echo esc_html( $f->due_date ); ?></td>
								<td>
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=sms-fees&action=edit&id=' . $f->id ) ); ?>" class="button button-small"><span class="dashicons dashicons-edit"></span></a>
									<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=sms-fees&action=delete&id=' . $f->id ), 'sms_delete_fee_nonce' ) ); ?>" class="button button-small" onclick="return confirm('<?php esc_attr_e( 'Are you sure?', 'school-management-system' ); ?>');" style="color: #dc3232;"><span class="dashicons dashicons-trash"></span></a>
									<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?action=sms_download_fee_voucher&id=' . $f->id ), 'sms_download_fee_voucher_nonce' ) ); ?>" class="button button-small" target="_blank" title="<?php esc_attr_e( 'Download Voucher', 'school-management-system' ); ?>"><span class="dashicons dashicons-download"></span></a>
								</td>
							</tr>
							<?php
						}
					} else {
						echo '<tr><td colspan="8">' . esc_html__( 'No fee records found.', 'school-management-system' ) . '</td></tr>';
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	function togglePaymentFields() {
		var status = $('#status').val();
		if (status === 'paid' || status === 'partially_paid') {
			$('#payment_date_group').slideDown();
			if (status === 'partially_paid') {
				$('#paid_amount_group').slideDown();
			} else {
				$('#paid_amount_group').slideUp();
			}
		} else {
			$('#payment_date_group').slideUp();
			$('#paid_amount_group').slideUp();
		}
	}
	$('#status').on('change', togglePaymentFields);
	togglePaymentFields();
});
</script>