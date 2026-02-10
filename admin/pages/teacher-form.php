<?php
/**
 * Teacher add/edit form.
 *
 * @package School_Management_System
 */

// The $teacher variable is set in teachers.php when editing.
$is_edit = isset( $teacher );

$first_name     = $is_edit ? $teacher->first_name : '';
$last_name      = $is_edit ? $teacher->last_name : '';
$email          = $is_edit ? $teacher->email : '';
$phone          = $is_edit ? $teacher->phone : '';
$employee_id    = $is_edit ? $teacher->employee_id : '';
$qualifications = $is_edit ? $teacher->qualification : '';
$status         = $is_edit ? $teacher->status : 'active';

?>
<style>
 .sms-teacher-form-page { max-width: 100%; }
 .sms-teacher-form-hero {
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
 .sms-teacher-form-hero h1 { margin: 0; color: #fff; font-size: 22px; line-height: 1.2; }
 .sms-teacher-form-hero p { margin: 6px 0 0; opacity: 0.92; font-size: 13px; }
 .sms-teacher-form-actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }
 .sms-hero-btn {
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
 }
 .sms-hero-btn:hover { background: rgba(255,255,255,0.24); color: #fff; }

 .sms-teacher-form-card {
 	background: #fff;
 	border: 1px solid #e9ecef;
 	border-radius: 16px;
 	box-shadow: 0 8px 22px rgba(0,0,0,0.06);
 	overflow: hidden;
 }
 .sms-teacher-form-card-header {
 	background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
 	color: #fff;
 	padding: 14px 18px;
 	font-size: 15px;
 	font-weight: 800;
 }
 .sms-teacher-form-card-body { padding: 18px; }

 .sms-form-grid {
 	display: grid;
 	grid-template-columns: repeat(2, minmax(0, 1fr));
 	gap: 14px;
 }
 .sms-form-field { display: flex; flex-direction: column; gap: 6px; }
 .sms-form-field.sms-span-2 { grid-column: 1 / -1; }
 .sms-form-label { font-weight: 700; color: #2c3e50; }
 .sms-form-control input,
 .sms-form-control select,
 .sms-form-control textarea {
 	width: 100%;
 	max-width: 100%;
 	padding: 10px 12px;
 	border: 1px solid #dee2e6;
 	border-radius: 10px;
 }
 .sms-form-footer {
 	margin-top: 16px;
 	display: flex;
 	gap: 10px;
 	flex-wrap: wrap;
 	align-items: center;
 }

 @media (max-width: 782px) {
 	.sms-teacher-form-hero { flex-direction: column; }
 	.sms-teacher-form-actions { width: 100%; justify-content: flex-start; }
 	.sms-form-grid { grid-template-columns: 1fr; }
 	.sms-form-field.sms-span-2 { grid-column: auto; }
 }
</style>

<div class="wrap">
	<div class="sms-teacher-form-page">
		<div class="sms-teacher-form-hero">
			<div>
				<h1>
					<?php
					if ( $is_edit ) {
						esc_html_e( 'Edit Teacher', 'school-management-system' );
					} else {
						esc_html_e( 'Add New Teacher', 'school-management-system' );
					}
					?>
				</h1>
				<p><?php esc_html_e( 'Update teacher profile details and status.', 'school-management-system' ); ?></p>
			</div>
			<div class="sms-teacher-form-actions">
				<a class="sms-hero-btn" href="<?php echo esc_url( admin_url( 'admin.php?page=sms-teachers' ) ); ?>">
					<span class="dashicons dashicons-arrow-left-alt"></span>
					<?php esc_html_e( 'Back to Teachers', 'school-management-system' ); ?>
				</a>
				<?php if ( $is_edit ) : ?>
					<a class="sms-hero-btn" href="<?php echo esc_url( admin_url( 'admin.php?page=sms-teachers' ) ); ?>">
						<span class="dashicons dashicons-no"></span>
						<?php esc_html_e( 'Cancel', 'school-management-system' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>

		<div class="sms-teacher-form-card">
			<div class="sms-teacher-form-card-header">
				<?php echo $is_edit ? esc_html__( 'Teacher Details', 'school-management-system' ) : esc_html__( 'New Teacher Details', 'school-management-system' ); ?>
			</div>
			<div class="sms-teacher-form-card-body">
				<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=sms-teachers' ) ); ?>">
					<input type="hidden" name="action" value="<?php echo $is_edit ? 'sms_edit_teacher' : 'sms_add_teacher'; ?>">
					<?php if ( $is_edit ) { ?>
						<input type="hidden" name="teacher_id" value="<?php echo intval( $teacher->id ); ?>">
					<?php } ?>
					<?php wp_nonce_field( $is_edit ? 'sms_edit_teacher_' . $teacher->id : 'sms_add_teacher' ); ?>

					<div class="sms-form-grid">
						<div class="sms-form-field">
							<label class="sms-form-label" for="first_name"><?php esc_html_e( 'First Name', 'school-management-system' ); ?></label>
							<div class="sms-form-control">
								<input type="text" name="first_name" id="first_name" value="<?php echo esc_attr( $first_name ); ?>" required>
							</div>
						</div>

						<div class="sms-form-field">
							<label class="sms-form-label" for="last_name"><?php esc_html_e( 'Last Name', 'school-management-system' ); ?></label>
							<div class="sms-form-control">
								<input type="text" name="last_name" id="last_name" value="<?php echo esc_attr( $last_name ); ?>" required>
							</div>
						</div>

						<div class="sms-form-field">
							<label class="sms-form-label" for="email"><?php esc_html_e( 'Email', 'school-management-system' ); ?></label>
							<div class="sms-form-control">
								<input type="email" name="email" id="email" value="<?php echo esc_attr( $email ); ?>" required>
							</div>
						</div>

						<div class="sms-form-field">
							<label class="sms-form-label" for="phone"><?php esc_html_e( 'Phone', 'school-management-system' ); ?></label>
							<div class="sms-form-control">
								<input type="text" name="phone" id="phone" value="<?php echo esc_attr( $phone ); ?>">
							</div>
						</div>

						<div class="sms-form-field">
							<label class="sms-form-label" for="employee_id"><?php esc_html_e( 'Employee ID', 'school-management-system' ); ?></label>
							<div class="sms-form-control">
								<input type="text" name="employee_id" id="employee_id" value="<?php echo esc_attr( $employee_id ); ?>" required>
							</div>
						</div>

						<div class="sms-form-field">
							<label class="sms-form-label" for="status"><?php esc_html_e( 'Status', 'school-management-system' ); ?></label>
							<div class="sms-form-control">
								<select name="status" id="status">
									<option value="active" <?php selected( $status, 'active' ); ?>><?php esc_html_e( 'Active', 'school-management-system' ); ?></option>
									<option value="inactive" <?php selected( $status, 'inactive' ); ?>><?php esc_html_e( 'Inactive', 'school-management-system' ); ?></option>
								</select>
							</div>
						</div>

						<div class="sms-form-field sms-span-2">
							<label class="sms-form-label" for="qualifications"><?php esc_html_e( 'Qualifications', 'school-management-system' ); ?></label>
							<div class="sms-form-control">
								<textarea name="qualifications" id="qualifications" rows="5"><?php echo esc_textarea( $qualifications ); ?></textarea>
							</div>
						</div>
					</div>

					<div class="sms-form-footer">
						<?php
						if ( $is_edit ) {
							submit_button( esc_html__( 'Update Teacher', 'school-management-system' ), 'primary', 'submit', false );
						} else {
							submit_button( esc_html__( 'Add Teacher', 'school-management-system' ), 'primary', 'submit', false );
						}
						?>
						<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=sms-teachers' ) ); ?>">
							<?php esc_html_e( 'Cancel', 'school-management-system' ); ?>
						</a>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>