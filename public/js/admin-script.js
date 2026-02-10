/* School Management System Admin JavaScript */

jQuery(document).ready(function ($) {
	// Delete confirmation
	$('a[data-sms-confirm]').on('click', function (e) {
		if (!confirm($(this).data('sms-confirm'))) {
			e.preventDefault();
		}
	});

	// Submit attendance via AJAX
	$('#sms-attendance-form').on('submit', function (e) {
		e.preventDefault();

		const studentId = $(this).find('input[name="student_id"]').val();
		const classId = $(this).find('input[name="class_id"]').val();
		const attendanceDate = $(this).find('input[name="attendance_date"]').val();
		const status = $(this).find('select[name="status"]').val();

		$.ajax({
			url: smsAdmin.ajaxurl,
			type: 'POST',
			data: {
				action: 'sms_submit_attendance',
				nonce: smsAdmin.nonce,
				student_id: studentId,
				class_id: classId,
				attendance_date: attendanceDate,
				status: status,
			},
			success: function (response) {
				if (response.success) {
					alert(response.data);
					location.reload();
				} else {
					alert('Error: ' + response.data);
				}
			},
			error: function () {
				alert('Failed to submit attendance');
			},
		});
	});

	// Enroll student via AJAX
	$('#sms-enroll-student-form').on('submit', function (e) {
		e.preventDefault();

		var $form = $(this);
		var $btn = $form.find('button[type="submit"]');
		var originalText = $btn.html();

		$btn.prop('disabled', true).text('Processing...');

		// Get class ID specifically
		var classId = $('#class_id').val();

		var formData = {
			action: 'sms_enroll_student',
			nonce: smsAdmin.nonce,
			enrollment_id: $('#enrollment_id').val(),
			student_id: $('#student_id').val(),
			first_name: $('#first_name').val(),
			class_id: classId,
			roll_number: $('#roll_number').val(),
			enrollment_date: $('#enrollment_date').val(),
			status: $('#status').val(),
			address: $('#address').val(),
			parent_name: $('#parent_name').val(),
			parent_phone: $('#parent_phone').val(),
			admission_fee: $('#admission_fee').val()
		};

		if (!formData.class_id) {
			$btn.prop('disabled', false).html(originalText);
			alert('Please select a class.');
			return;
		}

		$.ajax({
			url: smsAdmin.ajaxurl,
			type: 'POST',
			data: formData,
			success: function (response) {
				$btn.prop('disabled', false).html(originalText);
				if (response.success) {
					alert(response.data);
					if ($('#enrollment_id').val()) {
						window.location.href = 'admin.php?page=sms-enrollments';
					} else {
						location.reload();
					}
				} else {
					alert('Error: ' + response.data);
				}
			},
			error: function () {
				$btn.prop('disabled', false).html(originalText);
				alert('Failed to enroll student');
			}
		});
	});

	// Search functionality
	$('.sms-search-form').on('submit', function (e) {
		e.preventDefault();

		const searchTerm = $(this).find('input[name="search_term"]').val();
		const type = $(this).find('input[name="type"]').val();

		$.ajax({
			url: smsAdmin.ajaxurl,
			type: 'POST',
			data: {
				action: 'sms_search_data',
				nonce: smsAdmin.nonce,
				search_term: searchTerm,
				type: type,
			},
			success: function (response) {
				if (response.success) {
					displaySearchResults(response.data);
				} else {
					alert('Error: ' + response.data);
				}
			},
			error: function () {
				alert('Search failed');
			},
		});
	});

	function displaySearchResults(results) {
		let html = '<table class="wp-list-table widefat fixed striped"><thead><tr>';
		html += '<th>ID</th><th>Name</th><th>Email</th></tr></thead><tbody>';

		results.forEach(function (result) {
			html += '<tr><td>' + result.id + '</td>';
			html += '<td>' + (result.first_name || result.subject_name || result.class_name || result.exam_name) + '</td>';
			html += '<td>' + (result.email || '-') + '</td></tr>';
		});

		html += '</tbody></table>';
		$('#sms-search-results').html(html);
	}

	// Logo uploader for settings page.
	if ($('#upload_logo_button').length) {
		var image_frame;

		$('#upload_logo_button').on('click', function (e) {
			e.preventDefault();

			if (image_frame) {
				image_frame.open();
				return;
			}

			image_frame = wp.media({
				title: 'Select or Upload Logo',
				multiple: false,
				library: {
					type: 'image',
				},
			});

			image_frame.on('select', function () {
				var media_attachment = image_frame.state().get('selection').first().toJSON();
				$('#school_logo').val(media_attachment.url);
				$('#logo-preview').html('<img src="' + media_attachment.url + '" style="max-height: 100px; border: 1px solid #ddd; padding: 5px;" />');
			});

			image_frame.open();
		});
	}

	// Select All checkbox for bulk actions
	$('#cb-select-all-1').on('click', function() {
		var isChecked = $(this).prop('checked');
		$('input[name="student_ids[]"]').prop('checked', isChecked);
	});

	// Select All checkbox for bulk actions (Classes)
	$('#cb-select-all-classes').on('click', function() {
		var isChecked = $(this).prop('checked');
		$('input[name="class_ids[]"]').prop('checked', isChecked);
	});

	// Select All checkbox for bulk actions (Teachers)
	$('#cb-select-all-teachers').on('click', function() {
		var isChecked = $(this).prop('checked');
		$('input[name="teacher_ids[]"]').prop('checked', isChecked);
	});

	// Select All checkbox for bulk actions (Subjects)
	$('#cb-select-all-subjects').on('click', function() {
		var isChecked = $(this).prop('checked');
		$('input[name="subject_ids[]"]').prop('checked', isChecked);
	});

	// Select All checkbox for bulk actions (Enrollments)
	$('#cb-select-all-enrollments').on('click', function() {
		var isChecked = $(this).prop('checked');
		$('input[name="enrollment_ids[]"]').prop('checked', isChecked);
	});

	// Select All checkbox for bulk actions (Results)
	$('#cb-select-all-results').on('click', function() {
		var isChecked = $(this).prop('checked');
		$('input[name="result_ids[]"]').prop('checked', isChecked);
	});

	// Add Result via AJAX
	$('#sms-add-result-form').on('submit', function (e) {
		e.preventDefault();
		
		var $form = $(this);
		var $btn = $form.find('button[type="submit"]');
		var originalText = $btn.text();
		
		$btn.prop('disabled', true).text('Saving...');
		
		var formData = {
			action: 'sms_add_result',
			nonce: smsAdmin.nonce,
			student_id: $('#student_id').val(),
			exam_id: $('#exam_id').val(),
			subject_id: $('#subject_id').val(),
			obtained_marks: $('#obtained_marks').val()
		};
		
		$.ajax({
			url: smsAdmin.ajaxurl,
			type: 'POST',
			data: formData,
			success: function (response) {
				$btn.prop('disabled', false).text(originalText);
				if (response.success) {
					alert(response.data.message);
					location.reload();
				} else {
					var errorMsg = response.data;
					if (response.data && response.data.message) {
						errorMsg = response.data.message;
					}
					alert('Error: ' + errorMsg);
				}
			},
			error: function () {
				$btn.prop('disabled', false).text(originalText);
				alert('An error occurred. Please try again.');
			}
		});
	});

	// Handle voucher download (Global)
	$(document).on('click', '.sms-voucher-btn', function(e) {
		e.preventDefault();
		
		var $button = $(this);
		var feeId = $button.data('fee-id');
		var originalHtml = $button.html();
		
		// Show loading state
		$button.prop('disabled', true);
		$button.html('<span class="dashicons dashicons-update spin"></span>');
		
		// Prepare AJAX data
		var ajaxData = {
			action: 'sms_generate_voucher',
			fee_id: feeId,
			nonce: smsAdmin.voucher_nonce
		};
		
		$.ajax({
			url: smsAdmin.ajaxurl,
			type: 'POST',
			data: ajaxData,
			success: function(response) {
				if (response && response.success) {
					// Create download link
					var downloadLink = document.createElement('a');
					downloadLink.href = response.data.url;
					downloadLink.download = response.data.filename;
					downloadLink.target = '_blank';
					
					document.body.appendChild(downloadLink);
					downloadLink.click();
					document.body.removeChild(downloadLink);
					
					$button.html('<span class="dashicons dashicons-yes-alt"></span>');
					
					setTimeout(function() {
						$button.html(originalHtml);
						$button.prop('disabled', false);
					}, 2000);
				} else {
					var errorMsg = response && response.data ? response.data.message : 'Failed to generate voucher.';
					alert(errorMsg);
					$button.html('<span class="dashicons dashicons-warning"></span>');
					
					setTimeout(function() {
						$button.html(originalHtml);
						$button.prop('disabled', false);
					}, 2000);
				}
			},
			error: function(xhr, status, error) {
				alert('Network error occurred: ' + error);
				$button.html('<span class="dashicons dashicons-warning"></span>');
				
				setTimeout(function() {
					$button.html(originalHtml);
					$button.prop('disabled', false);
				}, 2000);
			}
		});
	});

	// Add spin animation style if not exists
	if ($('#sms-spin-style').length === 0) {
		$('<style id="sms-spin-style">')
			.prop('type', 'text/css')
			.html('.spin { animation: spin 1s linear infinite; } @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }')
			.appendTo('head');
	}

	// Print functionality for results
	$('.sms-print-results').on('click', function (e) {
		e.preventDefault();
		window.print();
	});

	// Allow Excel files in import
	$('input[name="import_file"]').attr('accept', '.csv, .xlsx, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, text/csv');
});
