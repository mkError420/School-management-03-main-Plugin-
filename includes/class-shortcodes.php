<?php
/**
 * Shortcodes class for frontend portals.
 *
 * @package School_Management_System
 */

namespace School_Management_System;

/**
 * Shortcodes class
 */
class Shortcodes {

	/**
	 * Register all shortcodes.
	 */
	public function register_shortcodes() {
		add_shortcode( 'sms_student_login', array( $this, 'student_login_shortcode' ) );
		add_shortcode( 'sms_student_portal', array( $this, 'student_portal_shortcode' ) );
		add_shortcode( 'sms_parent_portal', array( $this, 'parent_portal_shortcode' ) );
		add_shortcode( 'sms_class_timetable', array( $this, 'timetable_shortcode' ) );
		add_shortcode( 'sms_exam_results', array( $this, 'exam_results_shortcode' ) );
	}

	/**
	 * Student login shortcode.
	 *
	 * @return string HTML for student login form.
	 */
	public function student_login_shortcode() {
		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
			if ( Auth::is_student( $user->ID ) ) {
				return '<p>' . esc_html__( 'You are already logged in', 'school-management-system' ) . '</p>';
			}
		}

		ob_start();
		?>
		<div class="sms-login-container">
			<h2><?php esc_html_e( 'Student Login', 'school-management-system' ); ?></h2>
			<form method="post" class="sms-login-form">
				<?php wp_nonce_field( 'sms_login_form', 'sms_login_nonce' ); ?>
				
				<div class="sms-form-group">
					<label for="sms_email"><?php esc_html_e( 'Email', 'school-management-system' ); ?></label>
					<input type="email" id="sms_email" name="sms_email" required />
				</div>

				<div class="sms-form-group">
					<label for="sms_password"><?php esc_html_e( 'Password', 'school-management-system' ); ?></label>
					<input type="password" id="sms_password" name="sms_password" required />
				</div>

				<button type="submit" name="sms_login_submit" class="sms-btn"><?php esc_html_e( 'Login', 'school-management-system' ); ?></button>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Student portal shortcode.
	 *
	 * @return string HTML for student portal.
	 */
	public function student_portal_shortcode() {
		if ( ! is_user_logged_in() || ! Auth::is_student() ) {
			return '<p>' . esc_html__( 'You must be logged in as a student to view this content', 'school-management-system' ) . '</p>';
		}

		$user = wp_get_current_user();
		$student = Student::get_by_user_id( $user->ID );

		if ( ! $student ) {
			return '<p>' . esc_html__( 'Student profile not found', 'school-management-system' ) . '</p>';
		}

		ob_start();
		?>
		<div class="sms-student-portal">
			<h2><?php esc_html_e( 'Student Portal', 'school-management-system' ); ?></h2>

			<div class="sms-student-info">
				<h3><?php esc_html_e( 'Student Information', 'school-management-system' ); ?></h3>
				<table>
					<tr>
						<th><?php esc_html_e( 'Name', 'school-management-system' ); ?></th>
						<td><?php echo esc_html( $student->first_name . ' ' . $student->last_name ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Roll Number', 'school-management-system' ); ?></th>
						<td><?php echo esc_html( $student->roll_number ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Email', 'school-management-system' ); ?></th>
						<td><?php echo esc_html( $student->email ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Enrollment Date', 'school-management-system' ); ?></th>
						<td><?php echo esc_html( $student->enrollment_date ); ?></td>
					</tr>
				</table>
			</div>

			<h3><?php esc_html_e( 'Academic Performance', 'school-management-system' ); ?></h3>
			
			<?php
			$results = Result::get_student_results( $student->id );
			// Group by Exam
			$exam_results = array();
			if ( ! empty( $results ) ) {
				foreach ( $results as $result ) {
					if ( isset( $result->status ) && 'draft' === $result->status ) continue;
					$exam_results[ $result->exam_id ][] = $result;
				}
			}

			if ( ! empty( $exam_results ) ) {
				foreach ( $exam_results as $exam_id => $subjects ) {
					$exam = Exam::get( $exam_id );
					
					// Calculate Exam Summary for Header
					$exam_total_obtained = 0;
					$exam_total_max = 0;
					foreach ( $subjects as $res ) {
						$exam_total_obtained += $res->obtained_marks;
						$exam_total_max += $exam->total_marks;
					}
					$exam_percentage = ( $exam_total_max > 0 ) ? ( $exam_total_obtained / $exam_total_max ) * 100 : 0;
					$exam_grade = Result::calculate_grade( $exam_percentage, $exam->passing_marks );
					$exam_gpa = Result::calculate_gpa( $exam_percentage );
					?>
					<div class="sms-exam-card" style="background: #fff; border: 1px solid #eee; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
						<div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px; margin-bottom: 15px;">
							<div>
								<h4 style="margin: 0; color: #2c3e50; font-size: 18px;"><?php echo esc_html( $exam->exam_name ); ?></h4>
								<div style="font-size: 13px; color: #666; margin-top: 4px;">
									<span style="margin-right: 10px;"><strong><?php esc_html_e( 'Grade:', 'school-management-system' ); ?></strong> <?php echo esc_html( $exam_grade ); ?></span>
									<span style="margin-right: 10px;"><strong><?php esc_html_e( 'GPA:', 'school-management-system' ); ?></strong> <?php echo number_format( $exam_gpa, 2 ); ?></span>
									<span><strong><?php esc_html_e( 'Marks:', 'school-management-system' ); ?></strong> <?php echo floatval( $exam_total_obtained ) . ' / ' . floatval( $exam_total_max ); ?></span>
								</div>
							</div>
							<span style="font-size: 12px; color: #666;"><?php echo esc_html( $exam->exam_date ); ?></span>
						</div>
						
						<table class="sms-results-table" style="width: 100%; border-collapse: collapse;">
							<thead>
								<tr style="background: #f9f9f9;">
									<th style="padding: 10px; text-align: left;"><?php esc_html_e( 'Subject', 'school-management-system' ); ?></th>
									<th style="padding: 10px; text-align: center;"><?php esc_html_e( 'Marks', 'school-management-system' ); ?></th>
									<th style="padding: 10px; text-align: center;"><?php esc_html_e( 'Grade', 'school-management-system' ); ?></th>
									<th style="padding: 10px; text-align: center;"><?php esc_html_e( 'GPA', 'school-management-system' ); ?></th>
									<th style="padding: 10px; text-align: left;"><?php esc_html_e( 'Remarks', 'school-management-system' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php 
								$total_obtained = 0;
								$total_max = 0;
								foreach ( $subjects as $res ) {
									$subject = Subject::get( $res->subject_id );
									$gpa = Result::calculate_gpa( $res->percentage );
									$total_obtained += $res->obtained_marks;
									$total_max += $exam->total_marks; // Assuming total marks per subject is exam total marks
									?>
									<tr style="border-bottom: 1px solid #eee;">
										<td style="padding: 10px;"><?php echo esc_html( $subject->subject_name ); ?></td>
										<td style="padding: 10px; text-align: center;">
											<strong><?php echo floatval( $res->obtained_marks ); ?></strong> 
											<span style="color: #999; font-size: 11px;">/ <?php echo floatval( $exam->total_marks ); ?></span>
										</td>
										<td style="padding: 10px; text-align: center;">
											<span style="background: #e8f5e9; color: #2e7d32; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">
												<?php echo esc_html( $res->grade ); ?>
											</span>
										</td>
										<td style="padding: 10px; text-align: center;"><?php echo number_format( $gpa, 2 ); ?></td>
										<td style="padding: 10px; font-size: 13px; color: #666;"><?php echo esc_html( $res->remarks ); ?></td>
									</tr>
									<?php
								}
								?>
							</tbody>
							<tfoot style="border-top: 2px solid #e0e0e0;">
								<?php
								$overall_percentage = ( $total_max > 0 ) ? ( $total_obtained / $total_max ) * 100 : 0;
								$overall_grade      = Result::calculate_grade( $overall_percentage, $exam->passing_marks );
								$pass_fail_status   = ( $overall_percentage >= $exam->passing_marks ) ? __( 'Pass', 'school-management-system' ) : __( 'Fail', 'school-management-system' );
								$rank               = Result::get_student_rank_in_exam( $student->id, $exam_id );
								?>
								<tr style="background: #f0f8ff; font-weight: bold;">
									<td style="padding: 10px;"><?php esc_html_e( 'Total / Summary', 'school-management-system' ); ?></td>
									<td style="padding: 10px; text-align: center;"><?php echo esc_html( $total_obtained . ' / ' . $total_max ); ?></td>
									<td style="padding: 10px; text-align: center;"><?php echo esc_html( $overall_grade ); ?></td>
									<td style="padding: 10px; text-align: center;"><?php echo number_format( Result::calculate_gpa( $overall_percentage ), 2 ); ?></td>
									<td style="padding: 10px; text-align: left; color: <?php echo 'Pass' === $pass_fail_status ? '#28a745' : '#dc3545'; ?>;"><?php echo esc_html( $pass_fail_status ); ?></td>
								</tr>
								<tr style="background: #eaf6ff;">
									<td colspan="3" style="padding: 10px; text-align: left; font-size: 13px;">
										<strong style="margin-right: 15px;"><?php esc_html_e( 'Overall Percentage:', 'school-management-system' ); ?></strong> <?php echo number_format( $overall_percentage, 2 ); ?>%
										<strong style="margin-left: 25px; margin-right: 15px;"><?php esc_html_e( 'Class Rank:', 'school-management-system' ); ?></strong> <?php echo $rank > 0 ? esc_html( $rank ) : 'N/A'; ?>
									</td>
									<td colspan="2" style="padding: 10px; text-align: right;">
										<button onclick="printResultCard(this)" style="background: #2c3e50; color: #fff; border: none; padding: 8px 18px; border-radius: 4px; cursor: pointer; font-size: 12px;">
											<span class="dashicons dashicons-printer" style="vertical-align: middle; margin-right: 5px;"></span>
											<?php esc_html_e( 'Print / Download PDF', 'school-management-system' ); ?>
										</button>
									</td>
								</tr>
							</tfoot>
						</table>
					</div>
					<?php
				}
			} else {
				echo '<p>' . esc_html__( 'No results available yet.', 'school-management-system' ) . '</p>';
			}
			?>

			<p>
				<a href="<?php echo esc_url( home_url( '/?sms_action=logout' ) ); ?>" class="sms-btn-logout">
					<?php esc_html_e( 'Logout', 'school-management-system' ); ?>
				</a>
			</p>
		</div>
		<script>
		function printResultCard(button) {
			var card = button.closest('.sms-exam-card');
			var studentInfo = document.querySelector('.sms-student-info').innerHTML;
			var schoolName = "<?php echo esc_js( get_option( 'sms_settings' )['school_name'] ?? get_bloginfo( 'name' ) ); ?>";
			var schoolLogo = "<?php echo esc_js( get_option( 'sms_settings' )['school_logo'] ?? '' ); ?>";

			var printWindow = window.open('', '', 'height=800,width=1200');
			printWindow.document.write('<html><head><title>Result Card</title>');
			printWindow.document.write(`
				<style>
					@media print {
						@page { size: A4; margin: 20mm; }
						body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
					}
					body { font-family: Arial, sans-serif; color: #333; }
					.report-card { border: 2px solid #eee; padding: 20px; border-radius: 10px; }
					.rc-header { text-align: center; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; margin-bottom: 20px; display: flex; align-items: center; justify-content: center; gap: 20px; }
					.rc-header img { max-height: 60px; }
					.rc-header h1 { margin: 0; font-size: 24px; color: #2c3e50; }
					.rc-header h2 { margin: 0; font-size: 18px; color: #666; font-weight: normal; }
					.rc-student-info table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
					.rc-student-info th, .rc-student-info td { padding: 8px; text-align: left; border: 1px solid #eee; }
					.rc-student-info th { background: #f9f9f9; width: 150px; }
					.rc-results-table { width: 100%; border-collapse: collapse; }
					.rc-results-table th, .rc-results-table td { padding: 10px; text-align: left; border: 1px solid #eee; }
					.rc-results-table thead th { background: #f0f8ff; }
					.rc-results-table tfoot { font-weight: bold; }
					.rc-results-table tfoot td { background: #f0f8ff; }
					.rc-footer { margin-top: 40px; display: flex; justify-content: space-between; font-size: 12px; color: #888; }
					.rc-signature { border-top: 1px solid #ccc; padding-top: 5px; width: 200px; text-align: center; }
				</style>
			`);
			printWindow.document.write('</head><body>');
			printWindow.document.write('<div class="report-card">');
			printWindow.document.write('<div class="rc-header">');
			if(schoolLogo) {
				printWindow.document.write('<img src="' + schoolLogo + '" alt="School Logo" />');
			}
			printWindow.document.write('<div><h1>' + schoolName + '</h1><h2>Report Card</h2></div>');
			printWindow.document.write('</div>');
			printWindow.document.write('<div class="rc-student-info">' + studentInfo + '</div>');
			printWindow.document.write('<div class="rc-results-table">' + card.innerHTML + '</div>');
			printWindow.document.write('<div class="rc-footer"><div class="rc-signature">Class Teacher</div><div class="rc-signature">Principal</div></div>');
			printWindow.document.write('</div>');
			printWindow.document.write('</body></html>');
			printWindow.document.close();
			setTimeout(function() {
				printWindow.print();
			}, 500);
		}
		</script>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Parent portal shortcode.
	 *
	 * @return string HTML for parent portal.
	 */
	public function parent_portal_shortcode() {
		if ( ! is_user_logged_in() || ! Auth::is_parent() ) {
			return '<p>' . esc_html__( 'You must be logged in as a parent to view this content', 'school-management-system' ) . '</p>';
		}

		ob_start();
		?>
		<div class="sms-parent-portal">
			<h2><?php esc_html_e( 'Parent Portal', 'school-management-system' ); ?></h2>
			<p><?php esc_html_e( 'Welcome to the parent portal. You can view your child\'s academic progress here.', 'school-management-system' ); ?></p>

			<p>
				<a href="<?php echo esc_url( home_url( '/?sms_action=logout' ) ); ?>" class="sms-btn-logout">
					<?php esc_html_e( 'Logout', 'school-management-system' ); ?>
				</a>
			</p>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Timetable shortcode.
	 *
	 * @return string HTML for class timetable.
	 */
	public function timetable_shortcode() {
		ob_start();
		?>
		<div class="sms-timetable">
			<h2><?php esc_html_e( 'Class Timetable', 'school-management-system' ); ?></h2>
			<p><?php esc_html_e( 'Timetable feature coming soon.', 'school-management-system' ); ?></p>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Exam results shortcode.
	 *
	 * @return string HTML for exam results lookup.
	 */
	public function exam_results_shortcode() {
		ob_start();
		?>
		<div class="sms-exam-results">
			<h2><?php esc_html_e( 'Exam Results Lookup', 'school-management-system' ); ?></h2>
			
			<form method="get" class="sms-results-search">
				<input type="text" name="roll_number" placeholder="<?php esc_attr_e( 'Enter Roll Number', 'school-management-system' ); ?>" />
				<button type="submit"><?php esc_html_e( 'Search', 'school-management-system' ); ?></button>
			</form>

			<?php
			if ( isset( $_GET['roll_number'] ) ) {
				$roll_number = sanitize_text_field( $_GET['roll_number'] );
				$student = Student::get_by_roll_number( $roll_number );

				if ( $student ) {
					$results = Result::get_student_results( $student->id );
					?>
					<div class="sms-results-display">
						<h3><?php echo esc_html( $student->first_name . ' ' . $student->last_name ); ?></h3>
						<table class="sms-results-table">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Exam', 'school-management-system' ); ?></th>
									<th><?php esc_html_e( 'Obtained Marks', 'school-management-system' ); ?></th>
									<th><?php esc_html_e( 'Percentage', 'school-management-system' ); ?></th>
									<th><?php esc_html_e( 'Grade', 'school-management-system' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								if ( ! empty( $results ) ) {
									foreach ( $results as $result ) {
										$exam = Exam::get( $result->exam_id );
										?>
										<tr>
											<td><?php echo esc_html( $exam->exam_name ?? 'N/A' ); ?></td>
											<td><?php echo floatval( $result->obtained_marks ); ?></td>
											<td><?php echo number_format( floatval( $result->percentage ), 2 ); ?>%</td>
											<td><?php echo esc_html( $result->grade ); ?></td>
										</tr>
										<?php
									}
								} else {
									?>
									<tr>
										<td colspan="4"><?php esc_html_e( 'No results found', 'school-management-system' ); ?></td>
									</tr>
									<?php
								}
								?>
							</tbody>
						</table>
					</div>
					<?php
				} else {
					?>
					<p><?php esc_html_e( 'Student not found', 'school-management-system' ); ?></p>
					<?php
				}
			}
			?>
		</div>
		<?php
		return ob_get_clean();
	}
}
