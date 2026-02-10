<?php
/**
 * Attendance admin template.
 *
 * @package School_Management_System
 */

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'Unauthorized', 'school-management-system' ) );
}

$message = '';
$message_class = 'notice-success';
if ( isset( $_GET['sms_message'] ) ) {
	$sms_message = sanitize_text_field( $_GET['sms_message'] );
	if ( 'file_uploaded' === $sms_message ) {
		$message = __( 'File uploaded successfully.', 'school-management-system' );
	} elseif ( 'file_deleted' === $sms_message ) {
		$message = __( 'File deleted successfully.', 'school-management-system' );
	} elseif ( 'file_upload_error' === $sms_message ) {
		$message = __( 'Error uploading file.', 'school-management-system' );
		$message_class = 'notice-error';
	} elseif ( 'no_file_selected' === $sms_message ) {
		$message = __( 'No file was selected for upload.', 'school-management-system' );
		$message_class = 'notice-warning';
	}
}

?>
<style>
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
</style>
<div class="wrap">
	<h1><?php esc_html_e( 'Notice', 'school-management-system' ); ?></h1>

	<?php if ( ! empty( $message ) ) : ?>
		<div class="notice <?php echo esc_attr( $message_class ); ?> is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
	<?php endif; ?>

	<!-- File Upload Form -->
	<div style="background: #fff; padding: 20px; border: 1px solid #ddd; margin-bottom: 30px; border-radius: 4px;">
		<h2><?php esc_html_e( 'Upload Notice File', 'school-management-system' ); ?></h2>
		<p><?php esc_html_e( 'Upload a PDF, Word, or CSV file containing notices.', 'school-management-system' ); ?></p>
		<form method="post" action="" enctype="multipart/form-data">
			<?php wp_nonce_field( 'sms_attendance_upload_nonce', 'sms_attendance_upload_nonce_field' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="notice_name"><?php esc_html_e( 'Notice Name', 'school-management-system' ); ?></label>
					</th>
					<td>
						<input type="text" name="notice_name" id="notice_name" class="regular-text" required />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="attendance_file"><?php esc_html_e( 'Notice File', 'school-management-system' ); ?></label>
					</th>
					<td>
						<input type="file" name="attendance_file" id="attendance_file" accept=".pdf,.doc,.docx,.csv" />
					</td>
				</tr>
			</table>
			<button type="submit" name="sms_upload_attendance_file" class="button button-primary">
				<?php esc_html_e( 'Upload File', 'school-management-system' ); ?>
			</button>
		</form>
	</div>

	<!-- Display Uploaded File -->
	<?php
	$uploaded_files = get_option( 'sms_attendance_uploaded_files', array() );
	$search_term = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';

	if ( ! empty( $search_term ) && ! empty( $uploaded_files ) ) {
		$uploaded_files = array_filter( $uploaded_files, function( $file ) use ( $search_term ) {
			return stripos( $file['notice_name'], $search_term ) !== false || stripos( basename( $file['file'] ), $search_term ) !== false;
		} );
	}

	if ( ! empty( get_option( 'sms_attendance_uploaded_files', array() ) ) ) :
		?>
	<div style="background: #fff; padding: 20px; border: 1px solid #ddd; margin-bottom: 30px; border-radius: 4px;">
		<h2><?php esc_html_e( 'Current Notice Files', 'school-management-system' ); ?></h2>
		<form method="get" action="" style="margin-bottom: 20px; float: right;">
			<input type="hidden" name="page" value="sms-attendance" />
			<input type="search" name="s" value="<?php echo esc_attr( $search_term ); ?>" placeholder="<?php esc_attr_e( 'Search notices...', 'school-management-system' ); ?>" />
			<button type="submit" class="sms-search-btn"><?php esc_html_e( 'Search', 'school-management-system' ); ?></button>
			<?php if ( ! empty( $search_term ) ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=sms-attendance' ) ); ?>" class="sms-reset-btn"><?php esc_html_e( 'Reset', 'school-management-system' ); ?></a>
			<?php endif; ?>
		</form>
		<div style="clear: both;"></div>
		<?php if ( ! empty( $uploaded_files ) ) : ?>
		<table class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Notice Name', 'school-management-system' ); ?></th>
					<th><?php esc_html_e( 'File', 'school-management-system' ); ?></th>
					<th><?php esc_html_e( 'Upload Date', 'school-management-system' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'school-management-system' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $uploaded_files as $index => $file ) : ?>
					<?php $delete_url = wp_nonce_url( admin_url( 'admin.php?page=sms-attendance&action=delete_attendance_file&file_index=' . $index ), 'sms_delete_attendance_file_nonce', '_wpnonce' ); ?>
					<tr>
						<td><?php echo esc_html( $file['notice_name'] ?? '' ); ?></td>
						<td><a href="<?php echo esc_url( $file['url'] ); ?>" target="_blank"><?php echo esc_html( basename( $file['file'] ) ); ?></a></td>
						<td><?php echo esc_html( $file['upload_date'] ?? '' ); ?></td>
						<td><a href="<?php echo esc_url( $delete_url ); ?>" class="button button-small button-link-delete" style="color: #a00;" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this file?', 'school-management-system' ); ?>')"><?php esc_html_e( 'Delete', 'school-management-system' ); ?></a></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php else : ?>
			<p><?php esc_html_e( 'No notices found matching your search.', 'school-management-system' ); ?></p>
		<?php endif; ?>
	</div>
	<?php endif; ?>

</div>
