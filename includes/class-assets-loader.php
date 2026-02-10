<?php
/**
 * Assets Loader class for enqueuing CSS and JS files.
 *
 * @package School_Management_System
 */

namespace School_Management_System;

/**
 * Assets Loader class
 */
class Assets_Loader {

	/**
	 * Enqueue admin scripts and styles.
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 */
	public function enqueue_admin_scripts( $hook_suffix ) {
		// Only enqueue on SMS pages.
		if ( strpos( $hook_suffix, 'sms-' ) === false ) {
			return;
		}

		$script_dependencies = array( 'jquery', 'wp-api' );

		// For settings page, enqueue media uploader scripts.
		if ( 'school-management_page_sms-settings' === $hook_suffix ) {
			wp_enqueue_media();
			$script_dependencies[] = 'media-editor';
		}

		// Enqueue admin stylesheet.
		wp_enqueue_style(
			'sms-admin-style',
			SMS_PLUGIN_URL . 'public/css/admin-style.css',
			array(),
			SMS_VERSION
		);

		$custom_css = "
			/* General Styles */
			.sms-wrap h1, .sms-wrap h2 {
				color: #2c3e50;
			}
 
			/* List Tables */
			.wp-list-table {
				border-radius: 8px;
				box-shadow: 0 2px 10px rgba(0,0,0,0.05);
				border: 1px solid #e0e0e0;
				overflow: hidden;
				background: #fff;
				transition: box-shadow 0.3s ease;
			}
			.wp-list-table:hover {
				box-shadow: 0 8px 25px rgba(0,0,0,0.08);
			}
			.wp-list-table thead th {
				border-bottom: 2px solid #e0e0e0 !important;
				font-weight: 600;
				color: #3c434a;
				text-transform: uppercase;
				font-size: 12px;
				letter-spacing: 0.5px;
			}
			.wp-list-table tbody tr:nth-child(even) {
				background: #fdfdfd;
			}
			.wp-list-table tbody tr {
				transition: background-color 0.2s ease;
			}
			.wp-list-table tbody tr:hover {
				color: #222;
			}
			.wp-list-table td, .wp-list-table th {
				padding: 15px 12px;
				vertical-align: middle;
			}
 
			/* Forms & Postboxes */
			.sms-form-wrap .postbox, #dashboard-widgets .postbox {
				border-radius: 8px !important;
				box-shadow: 0 5px 15px rgba(0,0,0,0.05) !important;
				border: 1px solid #e0e0e0 !important;
			}
 
			/* Buttons */
			.page-title-action, .button-primary {
				box-shadow: none !important;
				text-shadow: none !important;
				border-radius: 5px !important;
				transition: background 0.2s, transform 0.2s;
				padding: 8px 16px !important;
				height: auto !important;
				font-size: 14px;
				font-weight: 600;
			}
			.page-title-action:hover, .button-primary:hover {
				transform: translateY(-1px);
			}
 
			/* Adding icons to page titles */
			.wrap > h1 { display: flex; align-items: center; gap: 10px; font-size: 23px; font-weight: 600; color: #2c3e50; }
			.wrap > h1::before { font-family: dashicons; font-size: 30px; }
			
			/* Unique Page Styles */
			/* Students */
			.school-management_page_sms-students .wrap > h1::before { content: '\\f307'; color: #3498db; }
			.school-management_page_sms-students .wp-list-table { border-top: 4px solid #3498db; }
			.school-management_page_sms-students .wp-list-table thead th { background: linear-gradient(to bottom, #f2f8fc, #eaf4fb); border-bottom-color: #3498db !important; color: #2980b9; }
			.school-management_page_sms-students .sms-btn-add { background: #3498db !important; border-color: #2980b9 !important; color: #fff !important; }
			.school-management_page_sms-students .sms-btn-add:hover { background: #2980b9 !important; }
			.school-management_page_sms-students .sms-btn-import { background: #f39c12 !important; border-color: #e67e22 !important; color: #fff !important; }
			.school-management_page_sms-students .sms-btn-import:hover { background: #e67e22 !important; }
			.school-management_page_sms-students .sms-btn-export { background: #2ecc71 !important; border-color: #27ae60 !important; color: #fff !important; }
			.school-management_page_sms-students .sms-btn-export:hover { background: #27ae60 !important; }
			.school-management_page_sms-students .wp-list-table tbody tr:hover { background-color: #eaf4fb; }

			/* Teachers */
			.school-management_page_sms-teachers .wrap > h1::before { content: '\\f338'; color: #e67e22; }
			.school-management_page_sms-teachers .wp-list-table { border-top: 4px solid #e67e22; }
			.school-management_page_sms-teachers .wp-list-table thead th { background: linear-gradient(to bottom, #fef8f2, #fdf2e9); border-bottom-color: #e67e22 !important; color: #d35400; }
			.school-management_page_sms-teachers .page-title-action { background: #e67e22 !important; border-color: #d35400 !important; }
			.school-management_page_sms-teachers .page-title-action:hover { background: #d35400 !important; }
			.school-management_page_sms-teachers .wp-list-table tbody tr:hover { background-color: #fdf2e9; }

			/* Classes */
			.school-management_page_sms-classes .wrap > h1::before { content: '\\f331'; color: #2ecc71; }
			.school-management_page_sms-classes .wp-list-table { border-top: 4px solid #2ecc71; }
			.school-management_page_sms-classes .wp-list-table thead th { background: linear-gradient(to bottom, #f0fdf5, #eafaf1); border-bottom-color: #2ecc71 !important; color: #27ae60; }
			.school-management_page_sms-classes .page-title-action { background: #2ecc71 !important; border-color: #27ae60 !important; }
			.school-management_page_sms-classes .page-title-action:hover { background: #27ae60 !important; }
			.school-management_page_sms-classes .wp-list-table tbody tr:hover { background-color: #eafaf1; }

			/* Subjects */
			.school-management_page_sms-subjects .wrap > h1::before { content: '\\f108'; color: #9b59b6; }
			.school-management_page_sms-subjects .wp-list-table { border-top: 4px solid #9b59b6; }
			.school-management_page_sms-subjects .wp-list-table thead th { background: linear-gradient(to bottom, #f8f2fa, #f5ebf8); border-bottom-color: #9b59b6 !important; color: #8e44ad; }
			.school-management_page_sms-subjects .page-title-action { background: #9b59b6 !important; border-color: #8e44ad !important; }
			.school-management_page_sms-subjects .page-title-action:hover { background: #8e44ad !important; }
			.school-management_page_sms-subjects .wp-list-table tbody tr:hover { background-color: #f5ebf8; }

			/* Enrollments */
			.school-management_page_sms-enrollments .wrap > h1::before { content: '\\f110'; color: #1abc9c; }
			.school-management_page_sms-enrollments .wp-list-table { border-top: 4px solid #1abc9c; }
			.school-management_page_sms-enrollments .wp-list-table thead th { background: linear-gradient(to bottom, #eefbf8, #e8f8f5); border-bottom-color: #1abc9c !important; color: #16a085; }
			.school-management_page_sms-enrollments .page-title-action { background: #1abc9c !important; border-color: #16a085 !important; }
			.school-management_page_sms-enrollments .page-title-action:hover { background: #16a085 !important; }
			.school-management_page_sms-enrollments .wp-list-table tbody tr:hover { background-color: #e8f8f5; }

			/* Notice */
			.school-management_page_sms-attendance .wrap > h1::before { content: '\\f522'; color: #607d8b; }
			.school-management_page_sms-attendance .wp-list-table { border-top: 4px solid #607d8b; }
			.school-management_page_sms-attendance .wp-list-table thead th { background: linear-gradient(to bottom, #f5f7f8, #eceff1); border-bottom-color: #607d8b !important; color: #455a64; }
			.school-management_page_sms-attendance .page-title-action { background: #607d8b !important; border-color: #455a64 !important; }
			.school-management_page_sms-attendance .page-title-action:hover { background: #455a64 !important; }
			.school-management_page_sms-attendance .wp-list-table tbody tr:hover { background-color: #eceff1; }

			/* Student Attendance */
			.school-management_page_sms-student-attendance .wrap > h1::before { content: '\\f145'; color: #e91e63; }
			.school-management_page_sms-student-attendance .wp-list-table { border-top: 4px solid #e91e63; }
			.school-management_page_sms-student-attendance .wp-list-table thead th { background: linear-gradient(to bottom, #fde9f1, #fce4ec); border-bottom-color: #e91e63 !important; color: #c2185b; }
			.school-management_page_sms-student-attendance .page-title-action { background: #e91e63 !important; border-color: #c2185b !important; }
			.school-management_page_sms-student-attendance .page-title-action:hover { background: #c2185b !important; }
			.school-management_page_sms-student-attendance .wp-list-table tbody tr:hover { background-color: #fce4ec; }

			/* Exams */
			.school-management_page_sms-exams .wrap > h1::before { content: '\\f473'; color: #e74c3c; }
			.school-management_page_sms-exams .wp-list-table { border-top: 4px solid #e74c3c; }
			.school-management_page_sms-exams .wp-list-table thead th { background: linear-gradient(to bottom, #fef4f2, #fdedeb); border-bottom-color: #e74c3c !important; color: #c0392b; }
			.school-management_page_sms-exams .page-title-action { background: #e74c3c !important; border-color: #c0392b !important; }
			.school-management_page_sms-exams .page-title-action:hover { background: #c0392b !important; }
			.school-management_page_sms-exams .wp-list-table tbody tr:hover { background-color: #fdedeb; }

			/* Results */
			.school-management_page_sms-results .wrap > h1::before { content: '\\f158'; color: #f1c40f; }
			.school-management_page_sms-results .wp-list-table { border-top: 4px solid #f1c40f; }
			.school-management_page_sms-results .wp-list-table thead th { background: linear-gradient(to bottom, #fefbf0, #fef9e7); border-bottom-color: #f1c40f !important; color: #f39c12; }
			.school-management_page_sms-results .page-title-action { background: #f1c40f !important; border-color: #f39c12 !important; }
			.school-management_page_sms-results .page-title-action:hover { background: #f39c12 !important; }
			.school-management_page_sms-results .wp-list-table tbody tr:hover { background-color: #fef9e7; }

			/* Dashboard Styles */
			.sms-dashboard-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; }
			.sms-card { background: #ffffff; color: #444; padding: 25px; border-radius: 15px; box-shadow: 0 8px 25px rgba(0,0,0,0.08); display: flex; align-items: center; gap: 20px; transition: transform 0.3s ease, box-shadow 0.3s ease; border-left: 5px solid; position: relative; overflow: hidden; }
			.sms-card:hover { transform: translateY(-5px); box-shadow: 0 12px 30px rgba(0,0,0,0.12); }
			.sms-card .sms-card-icon { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
			.sms-card .sms-card-icon .dashicons { font-size: 32px; color: #fff; }
			.sms-card .sms-card-content h3 { margin: 0 0 5px; font-size: 13px; font-weight: 600; color: #777; text-transform: uppercase; letter-spacing: 0.5px; }
			.sms-card .sms-card-content .sms-card-value { margin: 0; font-size: 36px; font-weight: 700; line-height: 1; color: #333; }
			
			/* Dashboard Card Colors */
			.sms-card.students { border-left-color: #3498db; }
			.sms-card.students .sms-card-icon { background: #3498db; }
			.sms-card.teachers { border-left-color: #e67e22; }
			.sms-card.teachers .sms-card-icon { background: #e67e22; }
			.sms-card.classes { border-left-color: #2ecc71; }
			.sms-card.classes .sms-card-icon { background: #2ecc71; }
			.sms-card.exams { border-left-color: #e74c3c; }
			.sms-card.exams .sms-card-icon { background: #e74c3c; }
			.sms-card.attendance { border-left-color: #607d8b; }
			.sms-card.attendance .sms-card-icon { background: #607d8b; }

			/* Dashboard Widgets General */
			#dashboard-widgets .postbox .inside { padding: 0 !important; margin: 0 !important; }
			#dashboard-widgets .postbox .inside .sms-dashboard-cards, #dashboard-widgets .postbox .inside ul, #dashboard-widgets .postbox .inside p { padding: 20px; }
			#dashboard-widgets .postbox .inside table { border: none; width: 100%; }
			#dashboard-widgets .postbox .inside table th, #dashboard-widgets .postbox .inside table td { padding: 15px 20px; border-top: 1px solid #eee; color: #444; }
			#dashboard-widgets .postbox .inside table th { background: #f7f7f7; border-bottom: 2px solid #eee !important; font-weight: 600; color: #555; }
			#dashboard-widgets .postbox .inside ul { margin: 0; list-style: none; }
			#dashboard-widgets .postbox .inside ul li:last-child { border-bottom: none; }
			#dashboard-widgets .postbox .inside ul li a { text-decoration: none; font-weight: 500; color: #3498db; transition: color 0.2s; }
			#dashboard-widgets .postbox .inside ul li a:hover { color: #2980b9; }
			#dashboard-widgets .postbox .inside .button { margin: 0 20px 20px; }
			#dashboard-widgets .postbox .inside p { color: #666; }
			
			/* Dashboard Notice Files List */
			#dashboard-widgets .postbox .inside ul li {
				background: #fcfcfc; border: 1px solid #f0f0f0; margin-bottom: 8px; border-radius: 6px; padding: 10px 15px; transition: all 0.2s;
			}
			#dashboard-widgets .postbox .inside ul li:hover { background: #fff; border-color: #3498db; box-shadow: 0 2px 5px rgba(0,0,0,0.05); transform: translateX(2px); }
			
			/* Print Styles */
			@media print {
				#adminmenumain, #wpadminbar, #wpfooter, .update-nag, .notice, .page-title-action, .tablenav, .search-box, .sms-panel-header, .sms-dashboard-cards, .nav-tab-wrapper, .sms-filter-card, .sms-stats-grid, .sms-status-distribution, .sms-enrollments-header, .sms-enrollments-search, .bulkactions, .sms-results-search, .sms-filter-section {
					display: none !important;
				}
				#wpcontent, #wpbody-content, #wpbody, .wrap, .sms-panel, .sms-panel-body {
					margin: 0 !important;
					padding: 0 !important;
					height: auto !important;
					width: 100% !important;
					overflow: visible !important;
					background: #fff !important;
					box-shadow: none !important;
					border: none !important;
				}
				.wp-list-table {
					width: 100% !important;
					border: 1px solid #ddd !important;
					box-shadow: none !important;
				}
				.wp-list-table th, .wp-list-table td {
					color: #000 !important;
				}
				.column-actions, th.column-actions, td.column-actions, .check-column, .sms-row-actions {
					display: none !important;
				}
			}
		";
		wp_add_inline_style( 'sms-admin-style', $custom_css );

		// Enqueue Select2.
		wp_enqueue_style( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '4.1.0' );

		// Enqueue admin JavaScript.
		wp_enqueue_script(
			'sms-admin-script',
			SMS_PLUGIN_URL . 'public/js/admin-script.js',
			$script_dependencies,
			SMS_VERSION . '.' . time(), // Force cache bust
			true
		);

		// Enqueue Select2 JS.
		wp_enqueue_script( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array( 'jquery' ), '4.1.0', true );

		// Localize script with AJAX URL and nonce.
		wp_localize_script(
			'sms-admin-script',
			'smsAdmin',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'sms_admin_nonce' ),
				'voucher_nonce' => wp_create_nonce( 'sms_generate_voucher_nonce' ),
			)
		);
	}

	/**
	 * Enqueue frontend scripts and styles.
	 */
	public function enqueue_frontend_scripts() {
		// Enqueue frontend stylesheet.
		wp_enqueue_style(
			'sms-frontend-style',
			SMS_PLUGIN_URL . 'public/css/style.css',
			array(),
			SMS_VERSION
		);

		// Enqueue frontend JavaScript.
		wp_enqueue_script(
			'sms-frontend-script',
			SMS_PLUGIN_URL . 'public/js/script.js',
			array( 'jquery' ),
			SMS_VERSION,
			true
		);

		// Localize script with AJAX URL and nonce.
		wp_localize_script(
			'sms-frontend-script',
			'smsFrontend',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'sms_frontend_nonce' ),
			)
		);
	}
}
