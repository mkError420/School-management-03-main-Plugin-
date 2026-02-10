<?php
/**
 * Student Attendance admin template with advanced functions.
 *
 * @package School_Management_System
 */

use School_Management_System\Classm;
use School_Management_System\Student;
use School_Management_System\Attendance;

// Security check.
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'Unauthorized', 'school-management-system' ) );
}

// Get current tab and filters.
$active_tab = sanitize_text_field( $_GET['tab'] ?? 'mark_attendance' );
$class_id   = intval( $_GET['class_id'] ?? 0 );
$date       = sanitize_text_field( $_GET['date'] ?? current_time( 'Y-m-d' ) );

// Filters for report tab.
$report_month = intval( $_GET['month'] ?? date( 'm' ) );
$report_year  = intval( $_GET['year'] ?? date( 'Y' ) );

// Handle notifications.
$message = '';
if ( isset( $_GET['sms_message'] ) && 'attendance_saved' === $_GET['sms_message'] ) {
	$message = __( 'Attendance saved successfully.', 'school-management-system' );
}
?>
<style>
	/* Report Table Styles */
	#sms-attendance-report-printable { overflow-x: auto; }
	.attendance-report-table { width: 100%; border-collapse: collapse; }
	.attendance-report-table th, .attendance-report-table td {
		text-align: center;
		font-size: 12px;
		padding: 8px 4px;
		border: 1px solid #e9ecef;
		white-space: nowrap;
	}
	.attendance-report-table thead th { background-color: #f8f9fa; font-weight: 600; }
	.attendance-report-table .student-name {
		text-align: left;
		width: 180px;
		font-weight: bold;
		position: sticky;
		left: 0;
		background-color: #f8f9fa;
		z-index: 1;
	}
	.attendance-report-table tbody .student-name { background-color: #fff; }
	.attendance-report-table tbody tr:hover .student-name { background-color: #f1f6ff; }
	.attendance-report-table .summary-col { font-weight: bold; background-color: #f1f3f5; }

	/* Status Badge Styles */
	.att-status { display: inline-block; width: 24px; height: 24px; line-height: 24px; border-radius: 50%; color: #fff; font-weight: bold; }
	.att-present { background-color: #28a745; }
	.att-absent { background-color: #dc3545; }
	.att-late { background-color: #fd7e14; }
	.att-excused { background-color: #007bff; }
	.att-holiday { background-color: #f0f0f0; color: #666; }

	/* Report Actions & Legend */
	.report-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
	.report-legend { display: flex; gap: 15px; list-style: none; margin: 0; padding: 0; font-size: 12px; }
	.report-legend li { display: flex; align-items: center; gap: 5px; }
</style>
<div class="wrap">
	<h1><?php esc_html_e( 'Student Attendance', 'school-management-system' ); ?></h1>

	<?php if ( ! empty( $message ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $message ); ?></p></div>
	<?php endif; ?>

	<h2 class="nav-tab-wrapper">
		<a href="?page=sms-student-attendance&tab=mark_attendance" class="nav-tab <?php echo 'mark_attendance' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Mark Attendance', 'school-management-system' ); ?></a>
		<a href="?page=sms-student-attendance&tab=attendance_report" class="nav-tab <?php echo 'attendance_report' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Attendance Report', 'school-management-system' ); ?></a>
	</h2>

	<?php if ( 'mark_attendance' === $active_tab ) : ?>
		<div class="card" style="background: #fff; padding: 20px; border: 1px solid #ddd; margin-top: 20px; border-radius: 4px;">
			<form method="get" action="">
				<input type="hidden" name="page" value="sms-student-attendance" />
				<input type="hidden" name="tab" value="mark_attendance" />
				<table class="form-table">
					<tr>
						<th scope="row"><label for="class_id"><?php esc_html_e( 'Select Class', 'school-management-system' ); ?></label></th>
						<td>
							<select name="class_id" id="class_id" required>
								<option value=""><?php esc_html_e( 'Select Class', 'school-management-system' ); ?></option>
								<?php
								$classes = Classm::get_all( array(), 100 );
								foreach ( $classes as $class ) {
									printf( '<option value="%d" %s>%s</option>', intval( $class->id ), selected( $class_id, $class->id, false ), esc_html( $class->class_name ) );
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="date"><?php esc_html_e( 'Date', 'school-management-system' ); ?></label></th>
						<td><input type="date" name="date" id="date" value="<?php echo esc_attr( $date ); ?>" required /></td>
					</tr>
				</table>
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Manage Attendance', 'school-management-system' ); ?></button>
			</form>
		</div>

		<?php if ( $class_id > 0 ) : ?>
			<form method="post" action="">
				<?php wp_nonce_field( 'sms_nonce_form', 'sms_nonce' ); ?>
				<input type="hidden" name="class_id" value="<?php echo intval( $class_id ); ?>" />
				<input type="hidden" name="attendance_date" value="<?php echo esc_attr( $date ); ?>" />

				<div class="tablenav top">
					<div class="alignleft actions">
						<button type="button" id="mark-all-present" class="button"><?php esc_html_e( 'Mark All Present', 'school-management-system' ); ?></button>
					</div>
				</div>

				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Roll Number', 'school-management-system' ); ?></th>
							<th><?php esc_html_e( 'Student Name', 'school-management-system' ); ?></th>
							<th><?php esc_html_e( 'Status', 'school-management-system' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$students = Classm::get_students( $class_id );
						if ( ! empty( $students ) ) {
							foreach ( $students as $student ) {
								$records = Attendance::get_all( array( 'student_id' => $student->id, 'class_id' => $class_id, 'attendance_date' => $date ), 1 );
								$current_status = ! empty( $records ) ? $records[0]->status : 'present';
								?>
								<tr>
									<td><?php echo esc_html( $student->roll_number ); ?></td>
									<td><?php echo esc_html( $student->first_name . ' ' . $student->last_name ); ?></td>
									<td>
										<label style="margin-right: 10px;"><input type="radio" name="attendance[<?php echo intval( $student->id ); ?>]" value="present" <?php checked( $current_status, 'present' ); ?> /> <?php esc_html_e( 'Present', 'school-management-system' ); ?></label>
										<label style="margin-right: 10px;"><input type="radio" name="attendance[<?php echo intval( $student->id ); ?>]" value="absent" <?php checked( $current_status, 'absent' ); ?> /> <?php esc_html_e( 'Absent', 'school-management-system' ); ?></label>
										<label style="margin-right: 10px;"><input type="radio" name="attendance[<?php echo intval( $student->id ); ?>]" value="late" <?php checked( $current_status, 'late' ); ?> /> <?php esc_html_e( 'Late', 'school-management-system' ); ?></label>
										<label><input type="radio" name="attendance[<?php echo intval( $student->id ); ?>]" value="excused" <?php checked( $current_status, 'excused' ); ?> /> <?php esc_html_e( 'Excused', 'school-management-system' ); ?></label>
									</td>
								</tr>
								<?php
							}
						} else {
							echo '<tr><td colspan="3">' . esc_html__( 'No students found in this class.', 'school-management-system' ) . '</td></tr>';
						}
						?>
					</tbody>
				</table>
				<?php if ( ! empty( $students ) ) : ?>
					<p class="submit"><button type="submit" name="sms_mark_attendance" class="button button-primary"><?php esc_html_e( 'Save Attendance', 'school-management-system' ); ?></button></p>
				<?php endif; ?>
			</form>
		<?php endif; ?>

	<?php elseif ( 'attendance_report' === $active_tab ) : ?>
		<div class="card" style="background: #fff; padding: 20px; border: 1px solid #ddd; margin-top: 20px; border-radius: 4px;">
			<h3><?php esc_html_e( 'View Attendance Report', 'school-management-system' ); ?></h3>
			<form method="get" action="">
				<input type="hidden" name="page" value="sms-student-attendance" />
				<input type="hidden" name="tab" value="attendance_report" />
				<table class="form-table">
					<tr>
						<th scope="row"><label for="class_id_report"><?php esc_html_e( 'Select Class', 'school-management-system' ); ?></label></th>
						<td>
							<select name="class_id" id="class_id_report" required>
								<option value=""><?php esc_html_e( 'Select Class', 'school-management-system' ); ?></option>
								<?php
								$classes = Classm::get_all( array(), 100 );
								foreach ( $classes as $class ) {
									printf( '<option value="%d" %s>%s</option>', intval( $class->id ), selected( $class_id, $class->id, false ), esc_html( $class->class_name ) );
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="month_report"><?php esc_html_e( 'Month', 'school-management-system' ); ?></label></th>
						<td>
							<select name="month" id="month_report">
								<?php for ( $m = 1; $m <= 12; $m++ ) : ?>
									<option value="<?php echo esc_attr( $m ); ?>" <?php selected( $report_month, $m ); ?>><?php echo esc_html( date_i18n( 'F', mktime( 0, 0, 0, $m, 1 ) ) ); ?></option>
								<?php endfor; ?>
							</select>
							<select name="year" id="year_report">
								<?php for ( $y = date( 'Y' ); $y >= date( 'Y' ) - 5; $y-- ) : ?>
									<option value="<?php echo esc_attr( $y ); ?>" <?php selected( $report_year, $y ); ?>><?php echo esc_html( $y ); ?></option>
								<?php endfor; ?>
							</select>
						</td>
					</tr>
				</table>
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Generate Report', 'school-management-system' ); ?></button>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=sms-student-attendance&tab=attendance_report' ) ); ?>" class="button"><?php esc_html_e( 'Reset', 'school-management-system' ); ?></a>
			</form>
		</div>

		<?php if ( $class_id > 0 ) : ?>
			<div id="sms-attendance-report-wrapper" class="card">
				<?php
				$days_in_month = cal_days_in_month( CAL_GREGORIAN, $report_month, $report_year );
				$students = Classm::get_students( $class_id );
				$attendance_report = Attendance::get_monthly_class_attendance_report( $class_id, $report_year, $report_month );
				$current_class = Classm::get( $class_id );
				$class_name_display = $current_class ? $current_class->class_name : '';
				?>
				<div class="report-actions">
					<h3 style="margin:0;"><?php printf( 'Attendance Report: %s - %s %s', esc_html( $class_name_display ), esc_html( date_i18n( 'F', mktime( 0, 0, 0, $report_month, 1 ) ) ), esc_html( $report_year ) ); ?></h3>
					<button id="print-attendance-report" class="button"><span class="dashicons dashicons-printer"></span> <?php esc_html_e( 'Print Report', 'school-management-system' ); ?></button>
				</div>

				<ul class="report-legend">
					<li><span class="att-status att-present">P</span> <?php esc_html_e( 'Present', 'school-management-system' ); ?></li>
					<li><span class="att-status att-absent">A</span> <?php esc_html_e( 'Absent', 'school-management-system' ); ?></li>
					<li><span class="att-status att-late">L</span> <?php esc_html_e( 'Late', 'school-management-system' ); ?></li>
					<li><span class="att-status att-excused">E</span> <?php esc_html_e( 'Excused', 'school-management-system' ); ?></li>
					<li><span class="att-status att-holiday">H</span> <?php esc_html_e( 'Holiday', 'school-management-system' ); ?></li>
				</ul>
				<hr>

				<div id="sms-attendance-report-printable">
					<table class="wp-list-table widefat striped attendance-report-table">
						<thead>
							<tr>
								<th class="student-name"><?php esc_html_e( 'Student', 'school-management-system' ); ?></th>
								<?php for ( $day = 1; $day <= $days_in_month; $day++ ) : ?>
									<th><?php echo intval( $day ); ?></th>
								<?php endfor; ?>
								<th class="summary-col" title="<?php esc_attr_e( 'Present', 'school-management-system' ); ?>">P</th>
								<th class="summary-col" title="<?php esc_attr_e( 'Absent', 'school-management-system' ); ?>">A</th>
								<th class="summary-col" title="<?php esc_attr_e( 'Late', 'school-management-system' ); ?>">L</th>
								<th class="summary-col" title="<?php esc_attr_e( 'Excused', 'school-management-system' ); ?>">E</th>
							</tr>
						</thead>
						<tbody>
							<?php
							if ( ! empty( $students ) ) {
								foreach ( $students as $student ) {
									$summary = array( 'present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0 );
									?>
									<tr>
										<td class="student-name"><?php echo esc_html( $student->first_name . ' ' . $student->last_name ); ?></td>
										<?php
										for ( $day = 1; $day <= $days_in_month; $day++ ) {
											$status = $attendance_report[ $student->id ][ $day ] ?? null;
											$day_of_week = date( 'N', strtotime( "$report_year-$report_month-$day" ) );

											if ( $status ) {
												if ( isset( $summary[ $status ] ) ) {
													$summary[ $status ]++;
												}
												$status_char = strtoupper( substr( $status, 0, 1 ) );
												echo '<td><span class="att-status att-' . esc_attr( $status ) . '">' . esc_html( $status_char ) . '</span></td>';
											} elseif ( in_array( (int) $day_of_week, array( 5, 6 ), true ) ) { // Friday or Saturday.
												echo '<td><span class="att-status att-holiday">H</span></td>';
											} else {
												echo '<td>-</td>';
											}
										}
										?>
										<td class="summary-col"><?php echo intval( $summary['present'] ); ?></td>
										<td class="summary-col"><?php echo intval( $summary['absent'] ); ?></td>
										<td class="summary-col"><?php echo intval( $summary['late'] ); ?></td>
										<td class="summary-col"><?php echo intval( $summary['excused'] ); ?></td>
									</tr>
									<?php
								}
							} else {
								echo '<tr><td colspan="' . ( $days_in_month + 5 ) . '">' . esc_html__( 'No students found in this class.', 'school-management-system' ) . '</td></tr>';
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>
<script>
jQuery(document).ready(function($) {
	$('#mark-all-present').on('click', function(e) {
		e.preventDefault();
		$('input[type="radio"][value="present"]').prop('checked', true);
	});

	$('#print-attendance-report').on('click', function(e) {
		e.preventDefault();
		var printContents = document.getElementById('sms-attendance-report-printable').innerHTML;
		var reportTitle = '<h1>' + $('#sms-attendance-report-wrapper h3').first().text() + '</h1>';
		var legendHtml = $('.report-legend').prop('outerHTML');

		var printWindow = window.open('', '', 'height=800,width=1200');
		printWindow.document.write('<!DOCTYPE html><html><head><title>Print Attendance Report</title>');
		printWindow.document.write('<style>' +
			'@page { size: landscape; margin: 10mm; }' +
			'body { font-family: "Segoe UI", Roboto, Helvetica, Arial, sans-serif; color: #333; background: #fff; margin: 0; padding: 20px; }' +
			'.report-container { width: 100%; margin: 0 auto; }' +
			'h1 { text-align: center; color: #2c3e50; font-size: 22px; margin: 0 0 10px 0; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 2px solid #3498db; padding-bottom: 15px; display: inline-block; width: 100%; }' +
			'.report-meta { text-align: center; color: #7f8c8d; font-size: 11px; margin-bottom: 20px; font-style: italic; }' +
			'.report-legend { display: flex; justify-content: center; gap: 15px; list-style: none; padding: 0; margin: 0 0 20px 0; font-size: 11px; }' +
			'.report-legend li { display: flex; align-items: center; gap: 5px; }' +
			'table { width: 100%; border-collapse: collapse; font-size: 10px; border: 1px solid #e0e0e0; margin-top: 10px; }' +
			'th, td { padding: 6px 3px; text-align: center; border: 1px solid #e0e0e0; }' +
			'thead th { background-color: #34495e; color: #fff; font-weight: 600; white-space: nowrap; -webkit-print-color-adjust: exact; print-color-adjust: exact; border: none; }' +
			'tbody tr:nth-child(even) { background-color: #f8f9fa; -webkit-print-color-adjust: exact; print-color-adjust: exact; }' +
			'.student-name { text-align: left; font-weight: 600; color: #2c3e50; padding-left: 8px; background-color: #fff; position: sticky; left: 0; min-width: 150px; border-right: 2px solid #eee; }' +
			'.att-status { display: inline-flex; align-items: center; justify-content: center; width: 18px; height: 18px; border-radius: 50%; color: #fff; font-weight: bold; font-size: 9px; -webkit-print-color-adjust: exact; print-color-adjust: exact; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }' +
			'.att-present { background-color: #28a745 !important; }' +
			'.att-absent { background-color: #dc3545 !important; }' +
			'.att-late { background-color: #fd7e14 !important; }' +
			'.att-excused { background-color: #007bff !important; }' +
			'.att-holiday { background-color: #f0f0f0 !important; color: #666 !important; }' +
			'.summary-col { font-weight: bold; background-color: #ecf0f1 !important; color: #2c3e50; }' +
			'.footer { margin-top: 30px; text-align: center; font-size: 9px; color: #bdc3c7; border-top: 1px solid #eee; padding-top: 10px; }' +
			'</style>');
		printWindow.document.write('</head><body>');
		printWindow.document.write('<div class="report-container">');
		printWindow.document.write(reportTitle);
		printWindow.document.write('<div class="report-meta">Generated on ' + new Date().toLocaleDateString() + '</div>');
		if (legendHtml) printWindow.document.write(legendHtml);
		printWindow.document.write(printContents);
		printWindow.document.write('<div class="footer">School Management System Report</div>');
		printWindow.document.write('</div>');
		printWindow.document.write('</body></html>');
		printWindow.document.close();
		setTimeout(function() {
			printWindow.print();
		}, 500);
	});
});
</script>