/* School Management System Frontend JavaScript */

jQuery(document).ready(function ($) {
	// Login form submission
	$('.sms-login-form').on('submit', function (e) {
		const $form = $(this);
		const $submitBtn = $form.find('button[type="submit"]');
		const originalText = $submitBtn.text();

		$submitBtn.prop('disabled', true).text('Processing...');

		// Small delay for visual feedback
		setTimeout(function () {
			$submitBtn.prop('disabled', false).text(originalText);
		}, 500);
	});

	// Results search form
	$('.sms-results-search').on('submit', function (e) {
		const $form = $(this);
		const rollNumber = $form.find('input[name="roll_number"]').val();

		if (rollNumber.trim() === '') {
			e.preventDefault();
			alert('Please enter a roll number');
		}
	});

	// Tab switching for portals (if needed)
	$('.sms-tab-button').on('click', function (e) {
		e.preventDefault();

		const tabName = $(this).data('tab');

		$('.sms-tab-content').hide();
		$('#' + tabName).show();

		$('.sms-tab-button').removeClass('active');
		$(this).addClass('active');
	});

	// Confirm action
	$('[data-confirm]').on('click', function (e) {
		const message = $(this).data('confirm');
		if (!confirm(message)) {
			e.preventDefault();
		}
	});

	// Add smooth scroll behavior
	$('a[href^="#"]').on('click', function (e) {
		const href = $(this).attr('href');
		if ($(href).length) {
			e.preventDefault();
			$('html, body').animate(
				{
					scrollTop: $(href).offset().top - 20,
				},
				300
			);
		}
	});

	// Format date inputs
	$('input[type="date"]').on('change', function () {
		const dateValue = $(this).val();
		if (dateValue) {
			const date = new Date(dateValue);
			console.log('Date selected:', date.toLocaleDateString());
		}
	});

	// Table row highlighting on hover
	$('.sms-results-table tbody tr').on('hover', function () {
		$(this).toggleClass('hover');
	});

	// Print functionality for results
	$('.sms-print-results').on('click', function (e) {
		e.preventDefault();
		window.print();
	});
});
