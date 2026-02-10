<?php



/**



 * Results admin template.



 *



 * @package School_Management_System



 */







use School_Management_System\Result;



use School_Management_System\Exam;



use School_Management_System\Student;



use School_Management_System\Classm;



use School_Management_System\Subject;







if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'edit_posts' ) ) {



	wp_die( esc_html__( 'Unauthorized', 'school-management-system' ) );



}







$result = null;



$is_edit = false;



$action = sanitize_text_field( $_GET['action'] ?? '' );



$result_id = intval( $_GET['id'] ?? 0 );







$total_results = Result::count();



$passed_results = Result::count( array( 'grade !=' => 'F' ) );



$failed_results = Result::count( array( 'grade' => 'F' ) );



$average_percentage = Result::get_overall_average();







// Filters



$class_id_filter = intval( $_GET['class_id'] ?? 0 );



$exam_id_filter = intval( $_GET['exam_id'] ?? 0 );



$subject_id_filter = intval( $_GET['subject_id'] ?? 0 );







if ( 'edit' === $action && $result_id ) {



	$result = Result::get( $result_id );



	if ( ! $result ) {



		wp_die( esc_html__( 'Result record not found', 'school-management-system' ) );



	}



	$is_edit = true;



}







$show_form = ( 'add' === $action || $is_edit );



?>



<style>

/* Modern Results System Styles */

.sms-results-page { max-width: 100%; }

/* Modern Form Container Styles */

#sms-result-form {

	background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);

	border-radius: 20px;

	box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);

	border: 1px solid rgba(102, 126, 234, 0.1);

	overflow: hidden;

	backdrop-filter: blur(10px);

	transition: all 0.3s ease;

}

#sms-result-form:hover {

	box-shadow: 0 25px 50px rgba(102, 126, 234, 0.15);

	transform: translateY(-2px);

}

.sms-table-header {

	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

	color: #fff;

	padding: 25px 30px;

	border: none;

	display: flex;

	justify-content: space-between;

	align-items: center;

	position: relative;

	overflow: hidden;

}

.sms-table-header::before {

	content: '';

	position: absolute;

	top: 0;

	left: -100%;

	width: 100%;

	height: 100%;

	background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);

	animation: shimmer 3s infinite;

}

@keyframes shimmer {

	0% { left: -100%; }

	100% { left: 100%; }

}

.sms-table-header h3 {

	margin: 0;

	font-size: 22px;

	font-weight: 700;

	color: #fff;

	text-shadow: 0 2px 4px rgba(0,0,0,0.1);

}

.sms-panel-body {

	padding: 40px 30px;

	background: #fff;

}

.sms-modern-form {

	max-width: 900px;

	margin: 0 auto;

}

.sms-form-grid {

	display: grid;

	grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));

	gap: 30px;

	margin-bottom: 40px;

}

.sms-form-field {

	position: relative;

}

.sms-form-label {

	display: flex;

	align-items: center;

	gap: 12px;

	margin-bottom: 12px;

	font-weight: 600;

	color: #2c3e50;

	font-size: 14px;

	text-transform: uppercase;

	letter-spacing: 0.5px;

}

.sms-form-label .dashicons {

	font-size: 18px;

	color: #667eea;

	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

	border-radius: 8px;

	padding: 6px;

	color: #fff;

}

.sms-form-control {

	position: relative;

}

.sms-select, .sms-input {

	width: 100%;

	padding: 15px 20px;

	border: 2px solid #e9ecef;

	border-radius: 12px;

	font-size: 15px;

	transition: all 0.3s ease;

	background: #fff;

	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);

}

.sms-select:focus, .sms-input:focus {

	outline: none;

	border-color: #667eea;

	box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1), 0 8px 20px rgba(102, 126, 234, 0.15);

	transform: translateY(-2px);

}

.sms-select {

	appearance: none;

	background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23667eea' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6,9 12,15 18,9'%3e%3c/polyline%3e%3c/svg%3e");

	background-repeat: no-repeat;

	background-position: right 15px center;

	background-size: 20px;

	padding-right: 50px;

}

.required {

	color: #f74c4c;

	font-weight: 700;

	margin-left: 4px;

}

.sms-form-actions {

	display: flex;

	gap: 15px;

	justify-content: flex-end;

	align-items: center;

	padding-top: 20px;

	border-top: 2px solid #f8f9fa;

}

.sms-btn {

	display: inline-flex;

	align-items: center;

	gap: 10px;

	padding: 14px 28px;

	border: none;

	border-radius: 12px;

	font-weight: 600;

	font-size: 14px;

	text-decoration: none;

	cursor: pointer;

	transition: all 0.3s ease;

	text-transform: uppercase;

	letter-spacing: 0.5px;

	position: relative;

	overflow: hidden;

}

.sms-btn::before {

	content: '';

	position: absolute;

	top: 0;

	left: -100%;

	width: 100%;

	height: 100%;

	background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);

	transition: left 0.5s ease;

}

.sms-btn:hover::before {

	left: 100%;

}

.sms-btn-primary {

	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

	color: #fff;

	box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);

}

.sms-btn-primary:hover {

	transform: translateY(-3px);

	box-shadow: 0 12px 30px rgba(102, 126, 234, 0.4);

}

.sms-btn-secondary {

	background: #f8f9fa;

	color: #6c757d;

	border: 2px solid #e9ecef;

}

.sms-btn-secondary:hover {

	background: #e9ecef;

	color: #495057;

	transform: translateY(-2px);

}

.form-loading {

	text-align: center;

	padding: 40px;

	color: #6c757d;

}

.loading-spinner {

	width: 40px;

	height: 40px;

	border: 4px solid #f3f3f3;

	border-top: 4px solid #667eea;

	border-radius: 50%;

	animation: spin 1s linear infinite;

	margin: 0 auto 20px;

}

@keyframes spin {

	0% { transform: rotate(0deg); }

	100% { transform: rotate(360deg); }

}

.input-help {

	margin-top: 8px;

	font-size: 13px;

	color: #6c757d;

	font-style: italic;

}

/* Responsive Design */

@media (max-width: 768px) {

	.sms-form-grid {

		grid-template-columns: 1fr;

		gap: 20px;

	}

	.sms-panel-body {

		padding: 25px 20px;

	}

	.sms-table-header {

		padding: 20px;

		flex-direction: column;

		gap: 15px;

		text-align: center;

	}

	.sms-form-actions {

		flex-direction: column;

		gap: 10px;

	}

	.sms-btn {

		width: 100%;

		justify-content: center;

	}

}

.sms-results-header {

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

.sms-results-title h1 { margin: 0; color: #fff; font-size: 22px; line-height: 1.2; }

.sms-results-subtitle { margin: 6px 0 0; opacity: 0.92; font-size: 13px; }

.sms-results-header-actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }

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

	transition: all 0.3s ease;

}

.sms-cta-btn:hover { background: rgba(255,255,255,0.24); color: #fff; transform: translateY(-1px); }



/* Statistics Dashboard */

.sms-results-stats-grid {

	display: grid;

	grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));

	gap: 20px;

	margin-bottom: 25px;

}

.sms-stat-card {

	background: #fff;

	border-radius: 16px;

	padding: 20px;

	box-shadow: 0 8px 22px rgba(0,0,0,0.08);

	border: 1px solid #eef1f5;

	display: flex;

	align-items: center;

	gap: 16px;

	transition: all 0.3s ease;

	position: relative;

	overflow: hidden;

}

.sms-stat-card::before {

	content: '';

	position: absolute;

	top: 0;

	left: 0;

	right: 0;

	height: 4px;

	background: linear-gradient(90deg, var(--stat-color) 0%, var(--stat-color-light) 100%);

}

.sms-stat-card.total { --stat-color: #667eea; --stat-color-light: #a8b8f8; }

.sms-stat-card.passed { --stat-color: #00d2d3; --stat-color-light: #54a0ff; }

.sms-stat-card.failed { --stat-color: #f74c4c; --stat-color-light: #ff6b6b; }

.sms-stat-card.average { --stat-color: #feca57; --stat-color-light: #ff9ff3; }

.sms-stat-card:hover { transform: translateY(-3px); box-shadow: 0 12px 28px rgba(0,0,0,0.12); }

.sms-stat-icon {

	width: 50px;

	height: 50px;

	border-radius: 12px;

	display: flex;

	align-items: center;

	justify-content: center;

	color: #fff;

	font-size: 24px;

}

.sms-stat-card.total .sms-stat-icon { background: linear-gradient(135deg, #667eea 0%, #a8b8f8 100%); }

.sms-stat-card.passed .sms-stat-icon { background: linear-gradient(135deg, #00d2d3 0%, #54a0ff 100%); }

.sms-stat-card.failed .sms-stat-icon { background: linear-gradient(135deg, #f74c4c 0%, #ff6b6b 100%); }

.sms-stat-card.average .sms-stat-icon { background: linear-gradient(135deg, #feca57 0%, #ff9ff3 100%); }

.sms-stat-content { flex: 1; }

.sms-stat-number { font-size: 28px; font-weight: 800; color: #2c3e50; line-height: 1.1; margin-bottom: 4px; }

.sms-stat-label { font-size: 13px; color: #6c757d; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; }



/* Performance Chart */

.sms-performance-chart {

	background: #fff;

	border-radius: 16px;

	padding: 25px;

	box-shadow: 0 8px 22px rgba(0,0,0,0.08);

	border: 1px solid #eef1f5;

	margin-bottom: 25px;

}

.sms-chart-header {

	display: flex;

	justify-content: space-between;

	align-items: center;

	margin-bottom: 25px;

}

.sms-chart-header h3 { margin: 0; color: #2c3e50; font-size: 18px; font-weight: 600; }

.sms-chart-legend { display: flex; gap: 12px; }

.grade-legend {

	padding: 4px 8px;

	border-radius: 4px;

	font-size: 12px;

	font-weight: 600;

	color: #fff;

}

.grade-legend.grade-aplus { background: #4CAF50; }

.grade-legend.grade-a { background: #2196F3; }

.grade-legend.grade-b { background: #FF9800; }

.grade-legend.grade-c { background: #FFC107; }

.grade-legend.grade-d { background: #9E9E9E; }

.grade-legend.grade-f { background: #f44336; }

.sms-chart-bars {

	display: flex;

	align-items: flex-end;

	justify-content: space-around;

	height: 200px;

	gap: 15px;

}

.chart-bar-container {

	display: flex;

	flex-direction: column;

	align-items: center;

	flex: 1;

	max-width: 80px;

}

.chart-bar {

	width: 100%;

	background: linear-gradient(180deg, var(--bar-color) 0%, var(--bar-color-light) 100%);

	border-radius: 8px 8px 0 0;

	min-height: 10px;

	transition: height 0.5s ease;

	position: relative;

}

.chart-bar.grade-aplus { --bar-color: #4CAF50; --bar-color-light: #81C784; }

.chart-bar.grade-a { --bar-color: #2196F3; --bar-color-light: #64B5F6; }

.chart-bar.grade-b { --bar-color: #FF9800; --bar-color-light: #FFB74D; }

.chart-bar.grade-c { --bar-color: #FFC107; --bar-color-light: #FFD54F; }

.chart-bar.grade-d { --bar-color: #9E9E9E; --bar-color-light: #BDBDBD; }

.chart-bar.grade-f { --bar-color: #f44336; --bar-color-light: #EF5350; }

.chart-label {

	margin-top: 8px;

	font-weight: 600;

	color: #2c3e50;

	font-size: 14px;

}

.chart-count {

	margin-top: 4px;

	font-size: 12px;

	color: #6c757d;

	font-weight: 500;

}



/* Modern Filter Design */

.sms-filter-grid {

	display: grid;

	grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));

	gap: 20px;

	align-items: end;

}

.sms-filter-field label {

	display: block;

	margin-bottom: 8px;

	font-weight: 600;

	color: #2c3e50;

	font-size: 13px;

	text-transform: uppercase;

	letter-spacing: 0.4px;

}

.sms-filter-field select {

	width: 100%;

	padding: 10px 12px;

	border: 2px solid #e9ecef;

	border-radius: 8px;

	font-size: 14px;

	transition: all 0.3s ease;

}

.sms-filter-field select:focus {

	outline: none;

	border-color: #667eea;

	box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);

}

.sms-filter-actions {

	display: flex;

	gap: 10px;

}

.sms-btn {

	display: inline-flex;

	align-items: center;

	gap: 8px;

	padding: 10px 16px;

	border: none;

	border-radius: 8px;

	font-weight: 600;

	font-size: 13px;

	text-decoration: none;

	cursor: pointer;

	transition: all 0.3s ease;

	text-transform: uppercase;

	letter-spacing: 0.4px;

}

.sms-btn-primary {

	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

	color: #fff;

}

.sms-btn-primary:hover {

	transform: translateY(-1px);

	box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);

}

.sms-btn-secondary {

	background: #f8f9fa;

	color: #6c757d;

	border: 1px solid #dee2e6;

}

.sms-btn-secondary:hover {

	background: #e9ecef;

	color: #495057;

}



/* Modern Table Design */

.sms-results-table-container {

	background: #fff;

	border-radius: 16px;

	box-shadow: 0 8px 22px rgba(0,0,0,0.08);

	border: 1px solid #eef1f5;

	overflow: hidden;

}

.sms-table-header {

	display: flex;

	justify-content: space-between;

	align-items: center;

	padding: 20px 25px;

	border-bottom: 1px solid #eef1f5;

	background: #f8f9fa;

}

.sms-table-header h3 { margin: 0; color: #2c3e50; font-size: 18px; font-weight: 600; }

.sms-modern-table {

	border: none !important;

	box-shadow: none !important;

}

.sms-modern-table thead th {

	background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;

	border: none !important;

	color: #2c3e50 !important;

	font-weight: 700 !important;

	text-transform: uppercase;

	letter-spacing: 0.4px;

	font-size: 12px !important;

	padding: 15px 20px !important;

}

.sms-modern-table tbody td {

	padding: 15px 20px !important;

	border: none !important;

	border-bottom: 1px solid #f8f9fa !important;

	vertical-align: middle !important;

}

.sms-modern-table tbody tr:hover {

	background: #f8f9fa !important;

}

.sms-student-info {

	display: flex;

	align-items: center;

	gap: 12px;

}

.student-avatar {

	width: 40px;

	height: 40px;

	border-radius: 50%;

	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

	display: flex;

	align-items: center;

	justify-content: center;

	color: #fff;

}

.student-avatar .dashicons { font-size: 20px; }

.student-details strong {

	display: block;

	color: #2c3e50;

	font-weight: 600;

}

.sms-class-badge {

	background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);

	color: #1565c0;

	padding: 4px 10px;

	border-radius: 12px;

	font-size: 12px;

	font-weight: 600;

}

.sms-performance-cell {

	display: flex;

	flex-direction: column;

	gap: 8px;

}

.performance-marks {

	display: flex;

	align-items: baseline;

	gap: 4px;

}

.marks-obtained {

	font-size: 18px;

	font-weight: 700;

	color: #2c3e50;

}

.marks-total {

	font-size: 14px;

	color: #6c757d;

}

.performance-percentage {

	font-size: 14px;

	font-weight: 600;

	padding: 4px 8px;

	border-radius: 6px;

	text-align: center;

}

.performance-pass {

	background: #e8f5e9;

	color: #2e7d32;

}

.performance-fail {

	background: #ffebee;

	color: #c62828;

}

.performance-grade {

	font-size: 16px;

	font-weight: 700;

	padding: 6px 12px;

	border-radius: 8px;

	text-align: center;

	color: #fff;

}

.performance-grade.grade-aplus { background: linear-gradient(135deg, #4CAF50 0%, #81C784 100%); }

.performance-grade.grade-a { background: linear-gradient(135deg, #2196F3 0%, #64B5F6 100%); }

.performance-grade.grade-b { background: linear-gradient(135deg, #FF9800 0%, #FFB74D 100%); }

.performance-grade.grade-c { background: linear-gradient(135deg, #FFC107 0%, #FFD54F 100%); }

.performance-grade.grade-d { background: linear-gradient(135deg, #9E9E9E 0%, #BDBDBD 100%); }

.performance-grade.grade-f { background: linear-gradient(135deg, #f44336 0%, #EF5350 100%); }

.sms-row-actions {

	display: flex;

	gap: 8px;

}

.sms-action-btn {

	display: inline-flex;

	align-items: center;

	gap: 6px;

	padding: 6px 10px;

	border-radius: 6px;

	text-decoration: none;

	font-size: 12px;

	font-weight: 600;

	transition: all 0.3s ease;

}

.sms-action-btn.edit {

	background: #e3f2fd;

	color: #1565c0;

}

.sms-action-btn.edit:hover {

	background: #bbdefb;

	color: #0d47a1;

}

.sms-action-btn.delete {

	background: #ffebee;

	color: #c62828;

}

.sms-action-btn.delete:hover {

	background: #ffcdd2;

	color: #b71c1c;

}

.no-results {

	text-align: center !important;

	padding: 40px !important;

	color: #6c757d !important;

	font-size: 16px !important;

}



/* Modern Form Styles */

.form-subtitle {

	margin: 8px 0 0;

	color: #6c757d;

	font-size: 14px;

	font-weight: 400;

}

.sms-modern-form {

	max-width: 800px;

}

.sms-form-grid {

	display: grid;

	grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));

	gap: 25px;

	margin-bottom: 30px;

}

.sms-form-field {

	display: flex;

	flex-direction: column;

}

.sms-form-label {

	display: flex;

	align-items: center;

	gap: 8px;

	margin-bottom: 8px;

	border: 1px solid #e9ecef;

	border-radius: 16px;

	box-shadow: 0 8px 22px rgba(0,0,0,0.06);

	overflow: hidden;

	margin-bottom: 25px;

}

.sms-panel-header {

	background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);

	padding: 20px 25px;

	border-bottom: 1px solid #e9ecef;

}

.sms-panel-header h2 {

	margin: 0;

	color: #2c3e50;

	font-size: 18px;

	font-weight: 600;

}

.sms-panel-body {

	padding: 25px;

}



/* Responsive Design */

@media (max-width: 768px) {

	.sms-results-header {

		flex-direction: column;

		gap: 15px;

	}

	.sms-results-stats-grid {

		grid-template-columns: 1fr;

	}

	.sms-filter-grid {

		grid-template-columns: 1fr;

	}

	.sms-chart-legend {

		flex-wrap: wrap;

	}

	.sms-chart-bars {

		gap: 8px;

	}

	.sms-table-header {

		flex-direction: column;

		gap: 15px;

		align-items: flex-start;

	}

	.sms-modern-table {

		font-size: 14px;

	}

	.sms-performance-cell {

		gap: 4px;

	}

	.marks-obtained {

		font-size: 16px;

	}

	.performance-grade {

		font-size: 14px;

	}

}



@media print {

	.sms-results-header,

	.sms-results-stats-grid,

	.sms-performance-chart,

	.sms-panel-header,

	.sms-table-header,

	.sms-row-actions {

		display: none !important;

	}

	.sms-modern-table {

		border: 1px solid #000 !important;

	}

	.sms-modern-table thead th,

	.sms-modern-table tbody td {

		border: 1px solid #000 !important;

		padding: 8px !important;

	}

}

</style>



<div class="wrap">



	<div class="sms-results-page">



		<div class="sms-results-header">



			<div class="sms-results-title">



				<h1><?php esc_html_e( 'Exam Results', 'school-management-system' ); ?></h1>



				<div class="sms-results-subtitle"><?php esc_html_e( 'Manage student exam results, grades, and performance analytics.', 'school-management-system' ); ?></div>



			</div>



			<div class="sms-results-header-actions">



				<?php if ( ! $show_form ) : ?>



					<a class="sms-cta-btn" href="<?php echo esc_url( admin_url( 'admin.php?page=sms-results&action=add' ) ); ?>">



						<span class="dashicons dashicons-plus-alt"></span>



						<?php esc_html_e( 'Add New Result', 'school-management-system' ); ?>



					</a>



				<?php else : ?>



					<a class="sms-cta-btn" href="<?php echo esc_url( admin_url( 'admin.php?page=sms-results' ) ); ?>">



						<span class="dashicons dashicons-arrow-left-alt"></span>



						<?php esc_html_e( 'Back to Results', 'school-management-system' ); ?>



					</a>



				<?php endif; ?>



			</div>



		</div>



		<?php if ( ! $show_form ) : ?>

			<!-- Statistics Dashboard -->

			<div class="sms-results-stats-grid">

				<div class="sms-stat-card total">

					<div class="sms-stat-icon">

						<span class="dashicons dashicons-clipboard"></span>

					</div>

					<div class="sms-stat-content">

						<div class="sms-stat-number"><?php echo intval( $total_results ); ?></div>

						<div class="sms-stat-label"><?php esc_html_e( 'Total Results', 'school-management-system' ); ?></div>

					</div>

				</div>

				<div class="sms-stat-card passed">

					<div class="sms-stat-icon">

						<span class="dashicons dashicons-yes-alt"></span>

					</div>

					<div class="sms-stat-content">

						<div class="sms-stat-number"><?php echo intval( $passed_results ); ?></div>

						<div class="sms-stat-label"><?php esc_html_e( 'Passed', 'school-management-system' ); ?></div>

					</div>

				</div>

				<div class="sms-stat-card failed">

					<div class="sms-stat-icon">

						<span class="dashicons dashicons-no-alt"></span>

					</div>

					<div class="sms-stat-content">

						<div class="sms-stat-number"><?php echo intval( $failed_results ); ?></div>

						<div class="sms-stat-label"><?php esc_html_e( 'Failed', 'school-management-system' ); ?></div>

					</div>

				</div>

				<div class="sms-stat-card average">

					<div class="sms-stat-icon">

						<span class="dashicons dashicons-chart-bar"></span>

					</div>

					<div class="sms-stat-content">

						<div class="sms-stat-number"><?php echo number_format( $average_percentage, 1 ); ?>%</div>

						<div class="sms-stat-label"><?php esc_html_e( 'Average Score', 'school-management-system' ); ?></div>

					</div>

				</div>

			</div>



			<!-- Performance Chart -->

			<div class="sms-performance-chart">

				<div class="sms-chart-header">

					<h3><?php esc_html_e( 'Grade Distribution', 'school-management-system' ); ?></h3>

					<div class="sms-chart-legend">

						<span class="grade-legend grade-a+">A+</span>

						<span class="grade-legend grade-a">A</span>

						<span class="grade-legend grade-b">B</span>

						<span class="grade-legend grade-c">C</span>

						<span class="grade-legend grade-d">D</span>

						<span class="grade-legend grade-f">F</span>

					</div>

				</div>

				<div class="sms-chart-bars">

					<?php

					$grade_stats = Result::get_grade_distribution();

					$total_for_chart = array_sum( $grade_stats );

					foreach ( [ 'A+', 'A', 'B', 'C', 'D', 'F' ] as $grade ) {

						$count = $grade_stats[ $grade ] ?? 0;

						$percentage = $total_for_chart > 0 ? ( $count / $total_for_chart ) * 100 : 0;

						echo '<div class="chart-bar-container">';

						echo '<div class="chart-bar grade-' . strtolower( $grade ) . '" style="height: ' . $percentage . '%"></div>';

						echo '<div class="chart-label">' . $grade . '</div>';

						echo '<div class="chart-count">' . $count . '</div>';

						echo '</div>';

					}

					?>

				</div>

			</div>

		<?php endif; ?>







		<?php if ( $show_form ) : ?>



			<div class="sms-results-table-container" id="sms-result-form">



				<div class="sms-table-header">

					<h3><?php echo $is_edit ? esc_html__( 'Edit Result', 'school-management-system' ) : esc_html__( 'Add New Result', 'school-management-system' ); ?></h3>

					<div class="sms-table-actions">

						<a href="<?php echo esc_url( admin_url( 'admin.php?page=sms-results' ) ); ?>" class="sms-btn sms-btn-secondary">

							<span class="dashicons dashicons-arrow-left-alt"></span>

							<?php esc_html_e( 'Back to Results', 'school-management-system' ); ?>

						</a>

					</div>

				</div>



				<div class="sms-panel-body">

					<form method="post" action="" id="sms-result-form-ajax" class="sms-modern-form">

						<?php wp_nonce_field( 'sms_nonce_form', 'sms_nonce' ); ?>



						<div class="sms-form-grid">

							<div class="sms-form-field">

								<label class="sms-form-label" for="class_id">

									<span class="dashicons dashicons-home"></span>

									<?php esc_html_e( 'Class', 'school-management-system' ); ?> <span class="required">*</span>

								</label>

								<div class="sms-form-control">

									<select name="class_id" id="class_id" required class="sms-select">

										<option value=""><?php esc_html_e( 'Select Class', 'school-management-system' ); ?></option>

										<?php

										$classes = Classm::get_all( array(), 1000 );

										foreach ( $classes as $class ) {

											?>

											<option value="<?php echo intval( $class->id ); ?>" <?php selected( $result ? $result->class_id : 0, $class->id ); ?>>

												<?php echo esc_html( $class->class_name ); ?>

											</option>

											<?php

										}

										?>

									</select>

								</div>

							</div>

							<div class="sms-form-field">

								<label class="sms-form-label" for="student_id">

									<span class="dashicons dashicons-users"></span>

									<?php esc_html_e( 'Student', 'school-management-system' ); ?> <span class="required">*</span>

								</label>

								<div class="sms-form-control">

									<select name="student_id" id="student_id" required class="sms-select">

										<option value=""><?php esc_html_e( 'Select Student', 'school-management-system' ); ?></option>

									</select>

								</div>

							</div>

							

							<div class="sms-form-field">

								<label class="sms-form-label" for="exam_id">

									<span class="dashicons dashicons-clipboard"></span>

									<?php esc_html_e( 'Exam', 'school-management-system' ); ?> <span class="required">*</span>

								</label>

								<div class="sms-form-control">

									<select name="exam_id" id="exam_id" required class="sms-select">

										<option value=""><?php esc_html_e( 'Select Exam', 'school-management-system' ); ?></option>

										<?php

										$exams = Exam::get_all( array(), 1000 );

										foreach ( $exams as $exam ) {

											$class = Classm::get( $exam->class_id );

											$class_name = $class ? $class->class_name : 'Unknown Class';

											?>

											<option value="<?php echo intval( $exam->id ); ?>" <?php selected( $result ? $result->exam_id : 0, $exam->id ); ?>>

												<?php echo esc_html( $exam->exam_name . ' (' . $class_name . ')' ); ?>

											</option>

											<?php

										}

										?>

									</select>

								</div>

							</div>



							<div class="sms-form-field">

								<label class="sms-form-label" for="subject_id">

									<span class="dashicons dashicons-book"></span>

									<?php esc_html_e( 'Subject', 'school-management-system' ); ?> <span class="required">*</span>

								</label>

								<div class="sms-form-control">

									<select name="subject_id" id="subject_id" required class="sms-select">

										<option value=""><?php esc_html_e( 'Select Subject', 'school-management-system' ); ?></option>

										<?php

										$subjects = Subject::get_all( array(), 1000 );

										foreach ( $subjects as $subject ) {

											?>

											<option value="<?php echo intval( $subject->id ); ?>" <?php selected( $result ? $result->subject_id : 0, $subject->id ); ?>>

												<?php echo esc_html( $subject->subject_name . ' (' . $subject->subject_code . ')' ); ?>

											</option>

											<?php

										}

										?>

									</select>

								</div>

							</div>



							


							<div class="sms-form-field">

								<label class="sms-form-label" for="obtained_marks">

									<span class="dashicons dashicons-chart-line"></span>

									<?php esc_html_e( 'Obtained Marks', 'school-management-system' ); ?> <span class="required">*</span>

								</label>

								<div class="sms-form-control">

									<input type="number" name="obtained_marks" id="obtained_marks" step="0.01" required class="sms-input" value="<?php echo $result ? esc_attr( $result->obtained_marks ) : ''; ?>" placeholder="<?php esc_attr_e( 'Enter obtained marks', 'school-management-system' ); ?>" />

									<div class="input-help" id="total-marks-info"></div>

								</div>

							</div>

						</div>

						<div class="sms-form-actions">

							<?php if ( $is_edit ) : ?>

								<input type="hidden" name="result_id" value="<?php echo intval( $result->id ); ?>" />

								<button type="submit" name="sms_edit_result" class="sms-btn sms-btn-primary">

									<span class="dashicons dashicons-edit"></span>

									<?php esc_html_e( 'Update Result', 'school-management-system' ); ?>

								</button>

							<?php else : ?>

								<button type="submit" name="sms_add_result" class="sms-btn sms-btn-primary">

									<span class="dashicons dashicons-plus-alt"></span>

									<?php esc_html_e( 'Add Result', 'school-management-system' ); ?>

								</button>

							<?php endif; ?>

						</div>



						<div class="form-loading" id="form-loading" style="display: none;">

							<div class="loading-spinner"></div>

							<p><?php esc_html_e( 'Processing...', 'school-management-system' ); ?></p>

						</div>

					</form>

				</div>

			</div>



		<?php else : ?>



			<div class="sms-panel" id="sms-result-filter">



				<div class="sms-panel-header">



					<h2><?php esc_html_e( 'Filter Results', 'school-management-system' ); ?></h2>



				</div>



				<div class="sms-panel-body">



					<form method="get" action="">



						<input type="hidden" name="page" value="sms-results">



						<div class="sms-filter-grid">



							<div class="sms-filter-field">



								<label><?php esc_html_e( 'Class', 'school-management-system' ); ?></label>



								<select name="class_id">



									<option value=""><?php esc_html_e( 'All Classes', 'school-management-system' ); ?></option>



									<?php



									$classes = Classm::get_all();



									foreach ( $classes as $class ) {



										echo '<option value="' . intval( $class->id ) . '" ' . selected( $class_id_filter, $class->id, false ) . '>' . esc_html( $class->class_name ) . '</option>';



									}



									?>



								</select>



							</div>



							<div class="sms-filter-field">



								<label><?php esc_html_e( 'Exam', 'school-management-system' ); ?></label>



								<select name="exam_id">



									<option value=""><?php esc_html_e( 'All Exams', 'school-management-system' ); ?></option>



									<?php



									$exams = Exam::get_all();



									foreach ( $exams as $exam ) {



										echo '<option value="' . intval( $exam->id ) . '" ' . selected( $exam_id_filter, $exam->id, false ) . '>' . esc_html( $exam->exam_name ) . '</option>';



									}



									?>



								</select>



							</div>



							<div class="sms-filter-field">



								<label><?php esc_html_e( 'Subject', 'school-management-system' ); ?></label>



								<select name="subject_id">



									<option value=""><?php esc_html_e( 'All Subjects', 'school-management-system' ); ?></option>



									<?php



									$subjects = Subject::get_all();



									foreach ( $subjects as $subject ) {



										echo '<option value="' . intval( $subject->id ) . '" ' . selected( $subject_id_filter, $subject->id, false ) . '>' . esc_html( $subject->subject_name ) . '</option>';



									}



									?>



								</select>



							</div>



							<div class="sms-filter-actions">



								<button type="submit" class="sms-btn sms-btn-primary">



									<span class="dashicons dashicons-filter"></span>



									<?php esc_html_e( 'Apply Filters', 'school-management-system' ); ?>



								</button>



								<a href="<?php echo esc_url( admin_url( 'admin.php?page=sms-results' ) ); ?>" class="sms-btn sms-btn-secondary">



									<span class="dashicons dashicons-dismiss"></span>



									<?php esc_html_e( 'Clear', 'school-management-system' ); ?>



								</a>



							</div>



						</div>



					</form>



				</div>



			</div>



			<div class="sms-results-table-container">

				<div class="sms-table-header">

					<h3><?php esc_html_e( 'Results List', 'school-management-system' ); ?></h3>

					<div class="sms-table-actions">

						<button class="sms-btn sms-btn-secondary" onclick="window.print()">

							<span class="dashicons dashicons-printer"></span>

							<?php esc_html_e( 'Print', 'school-management-system' ); ?>

						</button>

					</div>

				</div>



				<table class="wp-list-table widefat fixed striped sms-modern-table">

					<thead>

						<tr>

						<tr>
							<th><?php esc_html_e( 'Student', 'school-management-system' ); ?></th>
							<th><?php esc_html_e( 'Class', 'school-management-system' ); ?></th>
							<th><?php esc_html_e( 'Exam', 'school-management-system' ); ?></th>
							<th style="width: 50%;"><?php esc_html_e( 'Results', 'school-management-system' ); ?></th>
						</tr>

					</thead>

					<tbody>

						<?php

						$filters = array(

							'class_id'   => $class_id_filter,

							'exam_id'    => $exam_id_filter,

							'subject_id' => $subject_id_filter,

						);

						$results = Result::get_by_filters( $filters );
						$grouped_results = [];
						if ( ! empty( $results ) ) {
							foreach ( $results as $result ) {
								$grouped_results[ $result->student_id ][ $result->exam_id ][] = $result;
							}
						}
						if ( ! empty( $grouped_results ) ) {
							foreach ( $grouped_results as $student_id => $exams ) {
								foreach ( $exams as $exam_id => $subject_results ) {
									$first_result = $subject_results[0];
									$total_obtained_marks = 0;
									$total_max_marks = 0;
									foreach ( $subject_results as $res ) {
										$total_obtained_marks += $res->obtained_marks;
										$total_max_marks += $res->total_marks;
									}
									$overall_percentage = $total_max_marks > 0 ? ( $total_obtained_marks / $total_max_marks ) * 100 : 0;
									$overall_grade = Result::calculate_grade( $overall_percentage, $first_result->passing_marks );
									?>
									<tr>
										<td>
											<div class="sms-student-info">
												<div class="student-avatar">
													<span class="dashicons dashicons-user"></span>
												</div>
												<div class="student-details">
													<strong><?php echo esc_html( $first_result->first_name . ' ' . $first_result->last_name ); ?></strong><br>
													<span class="description"><?php echo esc_html( $first_result->roll_number ); ?></span>
												</div>
											</div>
										</td>
										<td><span class="sms-class-badge"><?php echo esc_html( $first_result->class_name ); ?></span></td>
										<td>
											<?php echo esc_html( $first_result->exam_name ); ?><br>
											<small style="font-weight:bold;"><?php printf( esc_html__( 'Total: %s/%s (%s)', 'school-management-system' ), $total_obtained_marks, $total_max_marks, $overall_grade ); ?></small>
										</td>
										<td style="padding: 0 !important; vertical-align: top;">
											<table class="inner-results" style="width:100%; background: transparent; border: none;">
												<thead class="screen-reader-text">
													<tr>
														<th><?php esc_html_e( 'Subject', 'school-management-system' ); ?></th>
														<th><?php esc_html_e( 'Performance', 'school-management-system' ); ?></th>
														<th><?php esc_html_e( 'Actions', 'school-management-system' ); ?></th>
													</tr>
												</thead>
												<tbody>
													<?php foreach ( $subject_results as $row ) : ?>
														<?php
														$grade_class = 'grade-' . strtolower( str_replace( '+', 'plus', $row->grade ) );
														$performance_class = $row->percentage >= $row->passing_marks ? 'performance-pass' : 'performance-fail';
														?>
														<tr style="background: transparent;">
															<td style="width: 30%; border:none; padding: 8px; border-bottom: 1px solid #f8f9fa;"><?php echo esc_html( $row->subject_name ); ?></td>
															<td style="width: 45%; border:none; padding: 8px; border-bottom: 1px solid #f8f9fa;">
																<div class="sms-performance-cell">
																	<div class="performance-marks">
																		<span class="marks-obtained"><?php echo esc_html( $row->obtained_marks ); ?></span>
																		<span class="marks-total">/ <?php echo esc_html( $row->total_marks ?? '100' ); ?></span>
																	</div>
																	<div class="performance-percentage <?php echo esc_attr( $performance_class ); ?>">
																		<?php echo number_format( $row->percentage, 1 ); ?>%
																	</div>
																	<div class="performance-grade <?php echo esc_attr( $grade_class ); ?>">
																		<?php echo esc_html( $row->grade ); ?>
																	</div>
																</div>
															</td>
															<td style="width: 25%; border:none; padding: 8px; border-bottom: 1px solid #f8f9fa; text-align: right;">
																<div class="sms-row-actions">
																	<a class="sms-action-btn edit" href="<?php echo esc_url( admin_url( 'admin.php?page=sms-results&action=edit&id=' . $row->id ) ); ?>">
																		<span class="dashicons dashicons-edit"></span>
																		<?php esc_html_e( 'Edit', 'school-management-system' ); ?>
																	</a>
																	<a class="sms-action-btn delete" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=sms-results&action=delete&id=' . $row->id ), 'sms_delete_result_nonce', '_wpnonce' ) ); ?>" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this result?', 'school-management-system' ); ?>')">
																		<span class="dashicons dashicons-trash"></span>
																		<?php esc_html_e( 'Delete', 'school-management-system' ); ?>
																	</a>
																</div>
															</td>
														</tr>
													<?php endforeach; ?>
												</tbody>
											</table>
										</td>
									</tr>
									<?php
								}
							}
						} else {
							echo '<tr><td colspan="4" class="no-results">' . esc_html__( 'No results found.', 'school-management-system' ) . '</td></tr>';
						}
						?>

					</tbody>

				</table>

			</div>



		<?php endif; ?>



	</div>



</div>



<script>

jQuery(document).ready(function($) {

	// Store exam data for total marks display

	var examData = <?php echo json_encode(array_reduce(Exam::get_all(array(), 1000), function($carry, $exam) {

		$carry[$exam->id] = array(

			'total_marks' => $exam->total_marks,

			'passing_marks' => $exam->passing_marks

		);

		return $carry;

	}, array())); ?>;

	// Store student data by class
	var studentData = {};
	
	// Test AJAX functionality
	function testAJAX() {
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'sms_test',
				nonce: '<?php echo wp_create_nonce("sms_get_students_nonce"); ?>'
			},
			success: function(response) {
				console.log('AJAX Test Response:', response);
			},
			error: function(xhr, status, error) {
				console.log('AJAX Test Error:', status, error);
				console.log('Response Text:', xhr.responseText);
			}
		});
	}
	
	// Run test on page load
	testAJAX();
	
	// Load students via AJAX when class is selected
	function loadStudentsByClass(classId) {
		console.log('Loading students for class ID:', classId);
		
		if (!classId) {
			$('#student_id').html('<option value=""><?php esc_html_e( "Select Student", "school-management-system" ); ?></option>');
			return;
		}
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'sms_get_students_by_class',
				class_id: classId,
				nonce: '<?php echo wp_create_nonce("sms_get_students_nonce"); ?>'
			},
			beforeSend: function() {
				console.log('Sending AJAX request...');
				$('#student_id').html('<option value=""><?php esc_html_e( "Loading...", "school-management-system" ); ?></option>');
			},
			success: function(response) {
				console.log('AJAX Response:', response);
				if (response.success && response.data) {
					console.log('Students found:', response.data.length);
					var options = '<option value=""><?php esc_html_e( "Select Student", "school-management-system" ); ?></option>';
					$.each(response.data, function(index, student) {
						console.log('Adding student:', student.name);
						options += '<option value="' + student.id + '">' + 
								  student.name + 
								  '</option>';
					});
					$('#student_id').html(options);
					console.log('Student dropdown updated');
				} else {
					console.log('No students found or error in response');
					$('#student_id').html('<option value=""><?php esc_html_e( "Select Student", "school-management-system" ); ?></option>');
				}
			},
			error: function(xhr, status, error) {
				console.log('AJAX Error:', status, error);
				console.log('Response Text:', xhr.responseText);
				$('#student_id').html('<option value=""><?php esc_html_e( "Select Student", "school-management-system" ); ?></option>');
			}
		});
	}



	// Show total marks when exam is selected

	$('#exam_id').on('change', function() {

		var examId = $(this).val();

		var totalMarksInfo = $('#total-marks-info');

		

		if (examId && examData[examId]) {

			var exam = examData[examId];

			totalMarksInfo.html('Total Marks: ' + exam.total_marks + ' | Passing Marks: ' + exam.passing_marks);

		} else {

			totalMarksInfo.html('');

		}

	});

	// Filter students when class is selected
	$('#class_id').on('change', function() {
		var classId = $(this).val();
		loadStudentsByClass(classId);
	});

	});



	// Handle form submission via AJAX

	$('#sms-result-form-ajax').on('submit', function(e) {

		e.preventDefault();

		

		var $form = $(this);

		var $loading = $('#form-loading');

		var $submitBtn = $form.find('button[type="submit"]');

		var isEdit = $form.find('input[name="result_id"]').length > 0;

		

		// Client-side validation

		var examId = $('#exam_id').val();

		var subjectId = $('#subject_id').val();

		var studentId = $('#student_id').val();

		var obtainedMarks = $('#obtained_marks').val();

		

		if (!examId || !subjectId || !studentId || !obtainedMarks) {

			showFormMessage('error', 'Please fill in all required fields.');

			return false;

		}

		

		if (parseFloat(obtainedMarks) < 0) {

			showFormMessage('error', 'Obtained marks cannot be negative.');

			return false;

		}

		

		// Show loading state

		$loading.show();

		$submitBtn.prop('disabled', true);

		

		// Prepare form data

		var formData = new FormData(this);

		formData.append('action', 'sms_add_result');

		

		console.log('Submitting form with data:', {

			exam_id: examId,

			subject_id: subjectId,

			student_id: studentId,

			obtained_marks: obtainedMarks

		});

		

		// Send AJAX request

		$.ajax({

			url: ajaxurl,

			type: 'POST',

			data: formData,

			processData: false,

			contentType: false,

			timeout: 10000, // 10 second timeout

			success: function(response) {

				$loading.hide();

				$submitBtn.prop('disabled', false);

				

				if (response.success) {

					// Show success message

					showFormMessage('success', response.data.message || (isEdit ? 'Result updated successfully!' : 'Result added successfully!'));

					

					// If adding new result, clear form and redirect after delay

					if (!isEdit) {

						// Clear form

						$form[0].reset();

						$('#total-marks-info').html('');

						

						// Redirect to results list after 2 seconds to show the new result

						setTimeout(function() {

							window.location.href = '<?php echo esc_url(admin_url('admin.php?page=sms-results')); ?>';

						}, 2000);

					} else {

						// For edit, redirect back to list after 1 second

						setTimeout(function() {

							window.location.href = '<?php echo esc_url(admin_url('admin.php?page=sms-results')); ?>';

						}, 1000);

					}

				} else {

					// Show error message

					showFormMessage('error', response.data.message || 'An error occurred. Please try again.');

				}

			},

			error: function(xhr, status, error) {

				$loading.hide();

				$submitBtn.prop('disabled', false);

				

				// Try to get detailed error message

				var errorMessage = 'Server error. Please try again.';

				if (xhr.responseJSON && xhr.responseJSON.data) {

					errorMessage = xhr.responseJSON.data;

				} else if (xhr.responseText) {

					try {

						var response = JSON.parse(xhr.responseText);

						if (response.data) {

							errorMessage = response.data;

						}

					} catch (e) {

						// If JSON parsing fails, use default message

					}

				}

				

				console.error('AJAX Error:', {

					status: status,

					error: error,

					response: xhr.responseText

				});

				

				showFormMessage('error', errorMessage);

			}

		});

	});



	// Show form message

	function showFormMessage(type, message) {

		var $message = $('.form-message');

		

		if ($message.length === 0) {

			$message = $('<div class="form-message"><span class="dashicons"></span><span class="message-text"></span></div>');

			$message.insertBefore($('#sms-result-form-ajax'));

		}

		

		$message.removeClass('success error').addClass(type).addClass('show');

		$message.find('.dashicons').removeClass().addClass('dashicons ' + (type === 'success' ? 'dashicons-yes-alt' : 'dashicons-no-alt'));

		$message.find('.message-text').text(message);

		

		// Auto-hide after 5 seconds

		setTimeout(function() {

			$message.removeClass('show');

		}, 5000);

	}



	// Add hover effects to stat cards

	$('.sms-stat-card').hover(

		function() {

			$(this).addClass('hovered');

		},

		function() {

			$(this).removeClass('hovered');

		}

	);



	// Animate chart bars on page load

	$('.chart-bar').each(function(index) {

		var $bar = $(this);

		var height = $bar.css('height');

		$bar.css('height', '0');

		

		setTimeout(function() {

			$bar.css('height', height);

		}, 100 * index);

	});



	// Add smooth scroll to results table if coming from form submission

	if (window.location.search.indexOf('sms_message=') !== -1) {

		setTimeout(function() {

			$('html, body').animate({

				scrollTop: $('.sms-results-table-container').offset().top - 100

			}, 1000);

		}, 500);

	}

	// Simple Upload Toggle - Working Version
	jQuery(document).ready(function($) {
		// Simple toggle function
		$('#toggle-upload').click(function(e) {
			e.preventDefault();
			$('#upload-content').toggle();
			
			// Change button text
			var $btn = $(this);
			var $text = $btn.find('span').eq(1);
			
			if ($('#upload-content').is(':visible')) {
				$text.text('Hide Upload Options');
				$btn.find('.dashicons').removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-up-alt2');
			} else {
				$text.text('Show Upload Options');
				$btn.find('.dashicons').removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
			}
		});
	});

	// File Dropzone
	$('#dropzone').on('click', function() {
		$('#result-file').click();
	});

	// Drag and Drop
	$('#dropzone').on('dragover', function(e) {
		e.preventDefault();
		$(this).addClass('dragover');
	});

	$('#dropzone').on('dragleave', function(e) {
		e.preventDefault();
		$(this).removeClass('dragover');
	});

	$('#dropzone').on('drop', function(e) {
		e.preventDefault();
		$(this).removeClass('dragover');
		
		var files = e.originalEvent.dataTransfer.files;
		if (files.length > 0) {
			handleFileSelect(files[0]);
		}
	});

	// File Selection
	$('#result-file').on('change', function(e) {
		if (e.target.files.length > 0) {
			handleFileSelect(e.target.files[0]);
		}
	});

	// Remove File
	$('#remove-file').on('click', function() {
		$('#result-file').val('');
		$('#upload-preview').hide();
		$('#process-file').prop('disabled', true);
		$('#upload-results').hide();
	});

	// Download Template
	$('#download-template').on('click', function(e) {
		e.preventDefault();
		downloadTemplate();
	});

	// Process File
	$('#process-file').on('click', function() {
		processUploadedFile();
	});

	function handleFileSelect(file) {
		// Validate file type
		var allowedTypes = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv', 'application/csv'];
		var allowedExtensions = ['.xlsx', '.xls', '.csv'];
		
		var fileName = file.name.toLowerCase();
		var isValidType = false;
		
		for (var i = 0; i < allowedExtensions.length; i++) {
			if (fileName.endsWith(allowedExtensions[i])) {
				isValidType = true;
				break;
			}
		}
		
		if (!isValidType) {
			showFormMessage('error', '<?php esc_html_e( 'Invalid file type. Please upload Excel (.xlsx, .xls) or CSV files only.', 'school-management-system' ); ?>');
			return;
		}
		
		// Validate file size (5MB)
		var maxSize = 5 * 1024 * 1024; // 5MB
		if (file.size > maxSize) {
			showFormMessage('error', '<?php esc_html_e( 'File size too large. Maximum file size is 5MB.', 'school-management-system' ); ?>');
			return;
		}
		
		// Show file preview
		$('#file-name').text(file.name);
		$('#file-size').text(formatFileSize(file.size));
		$('#upload-preview').show();
		$('#process-file').prop('disabled', false);
		
		// Store file for processing
		window.selectedFile = file;
	}

	function formatFileSize(bytes) {
		if (bytes === 0) return '0 Bytes';
		var k = 1024;
		var sizes = ['Bytes', 'KB', 'MB', 'GB'];
		var i = Math.floor(Math.log(bytes) / Math.log(k));
		return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
	}

	function downloadTemplate() {
		// Create a simple CSV template
		var csvContent = "Student Roll Number,Student Name,Obtained Marks,Remarks\nSTU001,John Doe,85,Good performance\nSTU002,Jane Smith,92,Excellent\nSTU003,Bob Johnson,78,Needs improvement";
		
		var blob = new Blob([csvContent], { type: 'text/csv' });
		var url = window.URL.createObjectURL(blob);
		var a = document.createElement('a');
		a.href = url;
		a.download = 'result_upload_template.csv';
		document.body.appendChild(a);
		a.click();
		document.body.removeChild(a);
		window.URL.revokeObjectURL(url);
	}

	function processUploadedFile() {
		if (!window.selectedFile) {
			showFormMessage('error', '<?php esc_html_e( 'No file selected.', 'school-management-system' ); ?>');
			return;
		}
		
		var examId = $('#exam_id').val();
		var subjectId = $('#subject_id').val();
		
		if (!examId || !subjectId) {
			showFormMessage('error', '<?php esc_html_e( 'Please select Exam and Subject before processing the file.', 'school-management-system' ); ?>');
			return;
		}
		
		$('#upload-progress').show();
		$('#upload-results').hide();
		
		var formData = new FormData();
		formData.append('action', 'sms_upload_results');
		formData.append('result_file', window.selectedFile);
		formData.append('exam_id', examId);
		formData.append('subject_id', subjectId);
		formData.append('sms_nonce', $('#sms_nonce').val());
		
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			xhr: function() {
				var xhr = new window.XMLHttpRequest();
				xhr.upload.addEventListener('progress', function(e) {
					if (e.lengthComputable) {
						var percentComplete = (e.loaded / e.total) * 100;
						$('#progress-fill').css('width', percentComplete + '%');
						$('#progress-text').text(Math.round(percentComplete) + '%');
					}
				}, false);
				return xhr;
			},
			success: function(response) {
				$('#upload-progress').hide();
				
				if (response.success) {
					showUploadResults(response.data);
					showFormMessage('success', response.data.message || '<?php esc_html_e( 'File processed successfully!', 'school-management-system' ); ?>');
					
					// Redirect after 3 seconds
					setTimeout(function() {
						window.location.href = '<?php echo esc_url(admin_url('admin.php?page=sms-results')); ?>';
					}, 3000);
				} else {
					showFormMessage('error', response.data.message || '<?php esc_html_e( 'Failed to process file.', 'school-management-system' ); ?>');
				}
			},
			error: function(xhr, status, error) {
				$('#upload-progress').hide();
				showFormMessage('error', '<?php esc_html_e( 'An error occurred while processing the file.', 'school-management-system' ); ?>');
				console.error('Upload error:', error);
			}
		});
	}

	function showUploadResults(data) {
		$('#upload-results').show();
		
		var summaryHtml = '<div class="result-item success">' +
			'<span><?php esc_html_e( 'Total Records Processed:', 'school-management-system' ); ?></span>' +
			'<span><strong>' + data.total + '</strong></span>' +
			'</div>';
		
		if (data.successful > 0) {
			summaryHtml += '<div class="result-item success">' +
				'<span><?php esc_html_e( 'Successfully Imported:', 'school-management-system' ); ?></span>' +
				'<span><strong>' + data.successful + '</strong></span>' +
				'</div>';
		}
		
		if (data.failed > 0) {
			summaryHtml += '<div class="result-item error">' +
				'<span><?php esc_html_e( 'Failed to Import:', 'school-management-system' ); ?></span>' +
				'<span><strong>' + data.failed + '</strong></span>' +
				'</div>';
		}
		
		if (data.duplicates > 0) {
			summaryHtml += '<div class="result-item warning">' +
				'<span><?php esc_html_e( 'Duplicates Skipped:', 'school-management-system' ); ?></span>' +
				'<span><strong>' + data.duplicates + '</strong></span>' +
				'</div>';
		}
		
		$('#results-summary').html(summaryHtml);
		
		// Show detailed results if available
		if (data.details && data.details.length > 0) {
			var detailsHtml = '<h6><?php esc_html_e( 'Detailed Results:', 'school-management-system' ); ?></h6>';
			data.details.forEach(function(item) {
				var statusClass = item.status === 'success' ? 'success' : (item.status === 'error' ? 'error' : 'warning');
				detailsHtml += '<div class="result-item ' + statusClass + '">' +
					'<span>' + item.message + '</span>' +
					'</div>';
			});
			$('#results-details').html(detailsHtml);
		}
	}

});