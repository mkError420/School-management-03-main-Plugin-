<?php
/**
 * Student form template.
 *
 * @package School_Management_System
 */

use School_Management_System\Student;

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'Unauthorized', 'school-management-system' ) );
}

$student = isset( $student ) ? $student : null;
$is_edit = ! is_null( $student );

?>
<div class="wrap">
	<h1><?php echo $is_edit ? esc_html__( 'Edit Student', 'school-management-system' ) : esc_html__( 'Add New Student', 'school-management-system' ); ?></h1>

	<form method="post" action="">
		<?php wp_nonce_field( 'sms_nonce_form', 'sms_nonce' ); ?>

		<table class="form-table">
			<tr>
				<th scope="row">
						<label for="first_name"><?php esc_html_e( 'First Name (required)', 'school-management-system' ); ?></label>
				</th>
				<td>
					<input type="text" name="first_name" id="first_name" required value="<?php echo $student ? esc_attr( $student->first_name ) : ''; ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">
						<label for="last_name"><?php esc_html_e( 'Last Name (required)', 'school-management-system' ); ?></label>
				</th>
				<td>
					<input type="text" name="last_name" id="last_name" required value="<?php echo $student ? esc_attr( $student->last_name ) : ''; ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="email"><?php esc_html_e( 'Email *', 'school-management-system' ); ?></label>
				</th>
				<td>
					<input type="email" name="email" id="email" required value="<?php echo $student ? esc_attr( $student->email ) : ''; ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="roll_number"><?php esc_html_e( 'Roll Number *', 'school-management-system' ); ?></label>
				</th>
				<td>
					<input type="text" name="roll_number" id="roll_number" required value="<?php echo $student ? esc_attr( $student->roll_number ) : ''; ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">
						<label for="phone"><?php esc_html_e( 'Phone (Not required)', 'school-management-system' ); ?></label>
				</th>
				<td>
					<input type="text" name="phone" id="phone" value="<?php echo $student ? esc_attr( $student->phone ) : ''; ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">
						<label for="dob"><?php esc_html_e( 'DOB (required)', 'school-management-system' ); ?></label>
				</th>
				<td>
						<input type="date" name="dob" id="dob" required value="<?php echo $student ? esc_attr( $student->dob ) : ''; ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">
						<label for="gender"><?php esc_html_e( 'Gender (required)', 'school-management-system' ); ?></label>
				</th>
				<td>
						<select name="gender" id="gender" required>
						<option value=""><?php esc_html_e( 'Select', 'school-management-system' ); ?></option>
						<option value="Male" <?php echo $student && 'Male' === $student->gender ? 'selected' : ''; ?>>
							<?php esc_html_e( 'Male', 'school-management-system' ); ?>
						</option>
						<option value="Female" <?php echo $student && 'Female' === $student->gender ? 'selected' : ''; ?>>
							<?php esc_html_e( 'Female', 'school-management-system' ); ?>
						</option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row">
						<label for="parent_name"><?php esc_html_e( 'Guardian Name (required)', 'school-management-system' ); ?></label>
				</th>
				<td>
						<input type="text" name="parent_name" id="parent_name" required value="<?php echo $student ? esc_attr( $student->parent_name ) : ''; ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">
						<label for="parent_phone"><?php esc_html_e( 'Guardian Phone (required)', 'school-management-system' ); ?></label>
				</th>
				<td>
						<input type="text" name="parent_phone" id="parent_phone" required value="<?php echo $student ? esc_attr( $student->parent_phone ) : ''; ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">
						<label for="address"><?php esc_html_e( 'Address (required)', 'school-management-system' ); ?></label>
				</th>
				<td>
						<textarea name="address" id="address" required><?php echo $student ? esc_textarea( $student->address ) : ''; ?></textarea>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="status"><?php esc_html_e( 'Status', 'school-management-system' ); ?></label>
				</th>
				<td>
					<select name="status" id="status">
						<option value="active" <?php echo ! $student || 'active' === $student->status ? 'selected' : ''; ?>>
							<?php esc_html_e( 'Active', 'school-management-system' ); ?>
						</option>
						<option value="inactive" <?php echo $student && 'inactive' === $student->status ? 'selected' : ''; ?>>
							<?php esc_html_e( 'Inactive', 'school-management-system' ); ?>
						</option>
					</select>
				</td>
			</tr>
		</table>

		<?php if ( $is_edit ) : ?>
			<input type="hidden" name="student_id" value="<?php echo intval( $student->id ); ?>" />
			<button type="submit" name="sms_edit_student" class="button button-primary">
				<?php esc_html_e( 'Update Student', 'school-management-system' ); ?>
			</button>
		<?php else : ?>
			<button type="submit" name="sms_add_student" class="button button-primary">
				<?php esc_html_e( 'Add Student', 'school-management-system' ); ?>
			</button>
		<?php endif; ?>
	</form>
</div>
